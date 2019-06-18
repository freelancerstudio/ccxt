<?php

namespace ccxt;

use Exception as Exception; // a common import

class tidebit extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'tidebit',
            'name' => 'TideBit',
            'countries' => array ( 'HK' ),
            'rateLimit' => 1000,
            'version' => 'v2',
            'has' => array (
                'fetchDepositAddress' => true,
                'CORS' => true,
                'fetchTickers' => true,
                'fetchOHLCV' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1',
                '5m' => '5',
                '15m' => '15',
                '30m' => '30',
                '1h' => '60',
                '2h' => '120',
                '4h' => '240',
                '12h' => '720',
                '1d' => '1440',
                '3d' => '4320',
                '1w' => '10080',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/39034921-e3acf016-4480-11e8-9945-a6086a1082fe.jpg',
                'api' => 'https://www.tidebit.com',
                'www' => 'https://www.tidebit.com',
                'doc' => array (
                    'https://www.tidebit.com/documents/api/guide',
                    'https://www.tidebit.com/swagger/#/default',
                ),
                'referral' => 'http://bit.ly/2IX0LrM',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'markets',
                        'tickers',
                        'tickers/{market}',
                        'timestamp',
                        'trades',
                        'trades/{market}',
                        'order_book',
                        'order',
                        'k_with_pending_trades',
                        'k',
                        'depth',
                    ),
                    'post' => array(),
                ),
                'private' => array (
                    'get' => array (
                        'addresses/{address}',
                        'deposits/history',
                        'deposits/get_deposit',
                        'deposits/deposit_address',
                        'historys/orders',
                        'historys/vouchers',
                        'historys/accounts',
                        'historys/snapshots',
                        'linkage/get_status',
                        'members/me',
                        'order',
                        'orders',
                        'partners/orders/{id}/trades',
                        'referral_commissions/get_undeposited',
                        'referral_commissions/get_graph_data',
                        'trades/my',
                        'withdraws/bind_account_list',
                        'withdraws/get_withdraw_account',
                        'withdraws/fetch_bind_info',
                    ),
                    'post' => array (
                        'deposits/deposit_cash',
                        'favorite_markets/update',
                        'order/delete',
                        'orders',
                        'orders/multi',
                        'orders/clear',
                        'referral_commissions/deposit',
                        'withdraws/apply',
                        'withdraws/bind_bank',
                        'withdraws/bind_address',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.2 / 100,
                    'taker' => 0.2 / 100,
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'withdraw' => array(), // There is only 1% fee on withdrawals to your bank account.
                ),
            ),
            'exceptions' => array (
                '2002' => '\\ccxt\\InsufficientFunds',
                '2003' => '\\ccxt\\OrderNotFound',
            ),
        ));
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privateGetDepositAddress (array_merge ($request, $params));
        if (is_array($response) && array_key_exists('success', $response)) {
            if ($response['success']) {
                $address = $this->safe_string($response, 'address');
                $tag = $this->safe_string($response, 'addressTag');
                return array (
                    'currency' => $code,
                    'address' => $this->check_address($address),
                    'tag' => $tag,
                    'info' => $response,
                );
            }
        }
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetMarkets ($params);
        $result = array();
        for ($i = 0; $i < count ($response); $i++) {
            $market = $response[$i];
            $id = $this->safe_string($market, 'id');
            $symbol = $this->safe_string($market, 'name');
            list($baseId, $quoteId) = explode('/', $symbol);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetMembersMe ($params);
        $balances = $this->safe_value($response, 'accounts');
        $result = array( 'info' => $balances );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $this->safe_string($balance, 'currency');
            $code = $currencyId;
            if (is_array($this->currencies_by_id) && array_key_exists($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            } else {
                $code = $this->common_currency_code(strtoupper($currencyId));
            }
            $account = $this->account ();
            $account['free'] = $this->safe_float($balance, 'balance');
            $account['used'] = $this->safe_float($balance, 'locked');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        if ($limit === null) {
            $request['limit'] = $limit; // default = 300
        }
        $request['market'] = $market['id'];
        $response = $this->publicGetDepth (array_merge ($request, $params));
        $timestamp = $this->safe_integer($response, 'timestamp');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->safe_integer($ticker, 'at');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $ticker = $this->safe_value($ticker, 'ticker', array());
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'ask' => $this->safe_float($ticker, 'sell'),
            'bidVolume' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'change' => null,
            'percentage' => null,
            'previousClose' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetTickers ($params);
        $ids = is_array($tickers) ? array_keys($tickers) : array();
        $result = array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = null;
            $symbol = $id;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            } else {
                $baseId = mb_substr($id, 0, 3 - 0);
                $quoteId = mb_substr($id, 3, 6 - 3);
                $base = strtoupper($baseId);
                $quote = strtoupper($quoteId);
                $base = $this->common_currency_code($base);
                $quote = $this->common_currency_code($quote);
                $symbol = $base . '/' . $quote;
            }
            $ticker = $tickers[$id];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        $response = $this->publicGetTickersMarket (array_merge ($request, $params));
        return $this->parse_ticker($response, $market);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($this->safe_string($trade, 'created_at'));
        $id = $this->safe_string($trade, 'id');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'volume');
        $cost = $this->safe_float($trade, 'funds');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        $response = $this->publicGetTrades (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv[0] * 1000,
            $ohlcv[1],
            $ohlcv[2],
            $ohlcv[3],
            $ohlcv[4],
            $ohlcv[5],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null) {
            $limit = 30; // default is 30
        }
        $request = array (
            'market' => $market['id'],
            'period' => $this->timeframes[$timeframe],
            'limit' => $limit,
        );
        if ($since !== null) {
            $request['timestamp'] = $since;
        } else {
            $request['timestamp'] = 1800000;
        }
        $response = $this->publicGetK (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'done' => 'closed',
            'wait' => 'open',
            'cancel' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        } else {
            $marketId = $order['market'];
            $symbol = $this->markets_by_id[$marketId]['symbol'];
        }
        $timestamp = $this->parse8601 ($this->safe_string($order, 'created_at'));
        $status = $this->parse_order_status($this->safe_string($order, 'state'));
        $id = $this->safe_string($order, 'id');
        $type = $this->safe_string($order, 'ord_type');
        $side = $this->safe_string($order, 'side');
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'volume');
        $filled = $this->safe_float($order, 'executed_volume');
        $remaining = $this->safe_float($order, 'remaining_volume');
        $cost = null;
        if ($price !== null) {
            if ($filled !== null) {
                $cost = $price * $filled;
            }
        }
        return array (
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'cost' => $cost,
            'trades' => null,
            'fee' => null,
            'info' => $order,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'market' => $this->market_id($symbol),
            'side' => $side,
            'volume' => (string) $amount,
            'ord_type' => $type,
        );
        if ($type === 'limit') {
            $request['price'] = (string) $price;
        }
        $response = $this->privatePostOrders (array_merge ($request, $params));
        $market = $this->markets_by_id[$response['market']];
        return $this->parse_order($response, $market);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => $id,
        );
        $result = $this->privatePostOrderDelete (array_merge ($request, $params));
        $order = $this->parse_order($result);
        $status = $this->safe_string($order, 'status');
        if ($status === 'closed' || $status === 'canceled') {
            throw new OrderNotFound($this->id . ' ' . $this->json ($order));
        }
        return $order;
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $id = $this->safe_string($params, 'id');
        if ($id === null) {
            throw new ExchangeError($this->id . ' withdraw() requires an extra `$id` param (withdraw account $id according to withdraws/bind_account_list endpoint');
        }
        $request = array (
            'id' => $id,
            'currency_type' => 'coin', // or 'cash'
            'currency' => strtolower($currency),
            'body' => $amount,
            // 'address' => $address, // they don't allow withdrawing to direct addresses?
        );
        if ($tag !== null) {
            $request['memo'] = $tag;
        }
        $result = $this->privatePostWithdrawsApply (array_merge ($request, $params));
        return array (
            'info' => $result,
            'id' => null,
        );
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function encode_params ($params) {
        return $this->urlencode ($this->keysort ($params));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . 'api/' . $this->version . '/' . $this->implode_params($path, $params) . '.json';
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'] . $request;
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $sortedByKey = $this->keysort (array_merge (array (
                'access_key' => $this->apiKey,
                'tonce' => $nonce,
            ), $params));
            $query = $this->urlencode ($sortedByKey);
            $payload = $method . '|' . $request . '|' . $query;
            $signature = $this->hmac ($this->encode ($payload), $this->encode ($this->secret));
            $suffix = $query . '&$signature=' . $signature;
            if ($method === 'GET') {
                $url .= '?' . $suffix;
            } else {
                $body = $suffix;
                $headers = array( 'Content-Type' => 'application/x-www-form-urlencoded' );
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response) {
        if ($code === 400) {
            $error = $this->safe_value($response, 'error');
            $errorCode = $this->safe_string($error, 'code');
            $feedback = $this->id . ' ' . $this->json ($response);
            $exceptions = $this->exceptions;
            if (is_array($exceptions) && array_key_exists($errorCode, $exceptions)) {
                throw new $exceptions[$errorCode]($feedback);
            }
            // fallback to default $error handler
        }
    }
}
