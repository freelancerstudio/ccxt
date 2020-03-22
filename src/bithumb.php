<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;

class bithumb extends Exchange {

    public function describe() {
        return array_replace_recursive(parent::describe (), array(
            'id' => 'bithumb',
            'name' => 'Bithumb',
            'countries' => array( 'KR' ), // South Korea
            'rateLimit' => 500,
            'has' => array(
                'CORS' => true,
                'fetchTickers' => true,
                'withdraw' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/30597177-ea800172-9d5e-11e7-804c-b9d4fa9b56b0.jpg',
                'api' => array(
                    'public' => 'https://api.bithumb.com/public',
                    'private' => 'https://api.bithumb.com',
                ),
                'www' => 'https://www.bithumb.com',
                'doc' => 'https://apidocs.bithumb.com',
                'fees' => 'https://en.bithumb.com/customer_support/info_fee',
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'ticker/{currency}',
                        'ticker/all',
                        'orderbook/{currency}',
                        'orderbook/all',
                        'transaction_history/{currency}',
                        'transaction_history/all',
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'info/account',
                        'info/balance',
                        'info/wallet_address',
                        'info/ticker',
                        'info/orders',
                        'info/user_transactions',
                        'trade/place',
                        'info/order_detail',
                        'trade/cancel',
                        'trade/btc_withdrawal',
                        'trade/krw_deposit',
                        'trade/krw_withdrawal',
                        'trade/market_buy',
                        'trade/market_sell',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'maker' => 0.25 / 100,
                    'taker' => 0.25 / 100,
                ),
            ),
            'exceptions' => array(
                'Bad Request(SSL)' => '\\ccxt\\BadRequest',
                'Bad Request(Bad Method)' => '\\ccxt\\BadRequest',
                'Bad Request.(Auth Data)' => '\\ccxt\\AuthenticationError', // array( "status" => "5100", "message" => "Bad Request.(Auth Data)" )
                'Not Member' => '\\ccxt\\AuthenticationError',
                'Invalid Apikey' => '\\ccxt\\AuthenticationError', // array("status":"5300","message":"Invalid Apikey")
                'Method Not Allowed.(Access IP)' => '\\ccxt\\PermissionDenied',
                'Method Not Allowed.(BTC Adress)' => '\\ccxt\\InvalidAddress',
                'Method Not Allowed.(Access)' => '\\ccxt\\PermissionDenied',
                'Database Fail' => '\\ccxt\\ExchangeNotAvailable',
                'Invalid Parameter' => '\\ccxt\\BadRequest',
                '5600' => '\\ccxt\\ExchangeError',
                'Unknown Error' => '\\ccxt\\ExchangeError',
                'After May 23th, recent_transactions is no longer, hence users will not be able to connect to recent_transactions' => '\\ccxt\\ExchangeError', // array("status":"5100","message":"After May 23th, recent_transactions is no longer, hence users will not be able to connect to recent_transactions")
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        $response = $this->publicGetTickerAll ($params);
        $data = $this->safe_value($response, 'data');
        $currencyIds = is_array($data) ? array_keys($data) : array();
        $result = array();
        for ($i = 0; $i < count($currencyIds); $i++) {
            $currencyId = $currencyIds[$i];
            if ($currencyId === 'date') {
                continue;
            }
            $market = $data[$currencyId];
            $base = $currencyId;
            $quote = 'KRW';
            $symbol = $currencyId . '/' . $quote;
            $active = true;
            if (gettype($market) === 'array' && count(array_filter(array_keys($market), 'is_string')) == 0) {
                $numElements = is_array($market) ? count($market) : 0;
                if ($numElements === 0) {
                    $active = false;
                }
            }
            $result[] = array(
                'id' => $currencyId,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'info' => $market,
                'active' => $active,
                'precision' => array(
                    'amount' => null,
                    'price' => null,
                ),
                'limits' => array(
                    'amount' => array(
                        'min' => null,
                        'max' => null,
                    ),
                    'price' => array(
                        'min' => null,
                        'max' => null,
                    ),
                    'cost' => array(
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $request = array(
            'currency' => 'ALL',
        );
        $response = $this->privatePostInfoBalance (array_merge($request, $params));
        $result = array( 'info' => $response );
        $balances = $this->safe_value($response, 'data');
        $codes = is_array($this->currencies) ? array_keys($this->currencies) : array();
        for ($i = 0; $i < count($codes); $i++) {
            $code = $codes[$i];
            $account = $this->account();
            $currency = $this->currency($code);
            $lowerCurrencyId = $this->safe_string_lower($currency, 'id');
            $account['total'] = $this->safe_float($balances, 'total_' . $lowerCurrencyId);
            $account['used'] = $this->safe_float($balances, 'in_use_' . $lowerCurrencyId);
            $account['free'] = $this->safe_float($balances, 'available_' . $lowerCurrencyId);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currency' => $market['base'],
        );
        if ($limit !== null) {
            $request['count'] = $limit; // max = 50
        }
        $response = $this->publicGetOrderbookCurrency (array_merge($request, $params));
        $orderbook = $this->safe_value($response, 'data');
        $timestamp = $this->safe_integer($orderbook, 'timestamp');
        return $this->parse_order_book($orderbook, $timestamp, 'bids', 'asks', 'price', 'quantity');
    }

    public function parse_ticker($ticker, $market = null) {
        $timestamp = $this->safe_integer($ticker, 'date');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $open = $this->safe_float($ticker, 'opening_price');
        $close = $this->safe_float($ticker, 'closing_price');
        $change = null;
        $percentage = null;
        $average = null;
        if (($close !== null) && ($open !== null)) {
            $change = $close - $open;
            if ($open > 0) {
                $percentage = $change / $open * 100;
            }
            $average = $this->sum($open, $close) / 2;
        }
        $baseVolume = $this->safe_float($ticker, 'units_traded_24H');
        $quoteVolume = $this->safe_float($ticker, 'acc_trade_value_24H');
        $vwap = null;
        if ($quoteVolume !== null && $baseVolume !== null) {
            $vwap = $quoteVolume / $baseVolume;
        }
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_float($ticker, 'max_price'),
            'low' => $this->safe_float($ticker, 'min_price'),
            'bid' => $this->safe_float($ticker, 'buy_price'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'sell_price'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => $open,
            'close' => $close,
            'last' => $close,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $average,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function fetch_tickers($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetTickerAll ($params);
        $result = array();
        $timestamp = $this->safe_integer($response['data'], 'date');
        $tickers = $this->omit($response['data'], 'date');
        $ids = is_array($tickers) ? array_keys($tickers) : array();
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            $symbol = $id;
            $market = null;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            }
            $ticker = $tickers[$id];
            $isArray = gettype($ticker) === 'array' && count(array_filter(array_keys($ticker), 'is_string')) == 0;
            if (!$isArray) {
                $ticker['date'] = $timestamp;
                $result[$symbol] = $this->parse_ticker($ticker, $market);
            }
        }
        return $result;
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currency' => $market['base'],
        );
        $response = $this->publicGetTickerCurrency (array_merge($request, $params));
        return $this->parse_ticker($response['data'], $market);
    }

    public function parse_trade($trade, $market = null) {
        // a workaround for their bug in date format, hours are not 0-padded
        $parts = explode(' ', $trade['transaction_date']);
        $transaction_date = $parts[0];
        $transaction_time = $parts[1];
        if (strlen($transaction_time) < 8) {
            $transaction_time = '0' . $transaction_time;
        }
        $timestamp = $this->parse8601($transaction_date . ' ' . $transaction_time);
        $timestamp -= 9 * 3600000; // they report UTC . 9 hours (is_array(Korean timezone) && array_key_exists(server, Korean timezone))
        $type = null;
        $side = $this->safe_string($trade, 'type');
        $side = ($side === 'ask') ? 'sell' : 'buy';
        $id = $this->safe_string($trade, 'cont_no');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'units_traded');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $price * $amount;
            }
        }
        return array(
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'order' => null,
            'type' => $type,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currency' => $market['base'],
        );
        if ($limit === null) {
            $request['count'] = $limit; // default 20, max 100
        }
        $response = $this->publicGetTransactionHistoryCurrency (array_merge($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = null;
        $method = 'privatePostTrade';
        if ($type === 'limit') {
            $request = array(
                'order_currency' => $market['id'],
                'Payment_currency' => $market['quote'],
                'units' => $amount,
                'price' => $price,
                'type' => ($side === 'buy') ? 'bid' : 'ask',
            );
            $method .= 'Place';
        } else if ($type === 'market') {
            $request = array(
                'currency' => $market['id'],
                'units' => $amount,
            );
            $method .= 'Market' . $this->capitalize($side);
        }
        $response = $this->$method (array_merge($request, $params));
        $id = $this->safe_string($response, 'order_id');
        return array(
            'info' => $response,
            'id' => $id,
        );
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $side_in_params = (is_array($params) && array_key_exists('side', $params));
        if (!$side_in_params) {
            throw new ExchangeError($this->id . ' cancelOrder requires a `$side` parameter (sell or buy) and a `$currency` parameter');
        }
        $currency = $this->safe_string($params, 'currency');
        if ($currency === null) {
            throw new ExchangeError($this->id . ' cancelOrder requires a `$currency` parameter (a $currency $id)');
        }
        $side = ($params['side'] === 'buy') ? 'bid' : 'ask';
        $params = $this->omit($params, array( 'side', 'currency' ));
        $request = array(
            'order_id' => $id,
            'type' => $side,
            'currency' => $currency,
        );
        return $this->privatePostTradeCancel (array_merge($request, $params));
    }

    public function withdraw($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency($code);
        $request = array(
            'units' => $amount,
            'address' => $address,
            'currency' => $currency['id'],
        );
        if ($currency === 'XRP' || $currency === 'XMR') {
            $destination = $this->safe_string($params, 'destination');
            if (($tag === null) && ($destination === null)) {
                throw new ExchangeError($this->id . ' ' . $code . ' withdraw() requires a $tag argument or an extra $destination param');
            } else if ($tag !== null) {
                $request['destination'] = $tag;
            }
        }
        $response = $this->privatePostTradeBtcWithdrawal (array_merge($request, $params));
        return array(
            'info' => $response,
            'id' => null,
        );
    }

    public function nonce() {
        return $this->milliseconds();
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $endpoint = '/' . $this->implode_params($path, $params);
        $url = $this->urls['api'][$api] . $endpoint;
        $query = $this->omit($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        } else {
            $this->check_required_credentials();
            $body = $this->urlencode(array_merge(array(
                'endpoint' => $endpoint,
            ), $query));
            $nonce = (string) $this->nonce();
            $auth = $endpoint . "\0" . $body . "\0" . $nonce; // eslint-disable-line quotes
            $signature = $this->hmac($this->encode($auth), $this->encode($this->secret), 'sha512');
            $signature64 = $this->decode(base64_encode($this->encode($signature)));
            $headers = array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Api-Key' => $this->apiKey,
                'Api-Sign' => (string) $signature64,
                'Api-Nonce' => $nonce,
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($httpCode, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return; // fallback to default error handler
        }
        if (is_array($response) && array_key_exists('status', $response)) {
            //
            //     array("$status":"5100","$message":"After May 23th, recent_transactions is no longer, hence users will not be able to connect to recent_transactions")
            //
            $status = $this->safe_string($response, 'status');
            $message = $this->safe_string($response, 'message');
            if ($status !== null) {
                if ($status === '0000') {
                    return; // no error
                }
                $feedback = $this->id . ' ' . $body;
                $this->throw_exactly_matched_exception($this->exceptions, $status, $feedback);
                $this->throw_exactly_matched_exception($this->exceptions, $message, $feedback);
                throw new ExchangeError($feedback);
            }
        }
    }

    public function request($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2($path, $api, $method, $params, $headers, $body);
        if (is_array($response) && array_key_exists('status', $response)) {
            if ($response['status'] === '0000') {
                return $response;
            }
            throw new ExchangeError($this->id . ' ' . $this->json($response));
        }
        return $response;
    }
}
