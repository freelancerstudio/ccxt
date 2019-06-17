<?php

namespace ccxt;

use Exception as Exception; // a common import

class _1btcxe extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => '_1btcxe',
            'name' => '1BTCXE',
            'countries' => array ( 'PA' ), // Panama
            'comment' => 'Crypto Capital API',
            'has' => array (
                'CORS' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1d' => '1year',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766049-2b294408-5ecc-11e7-85cc-adaff013dc1a.jpg',
                'api' => 'https://1btcxe.com/api',
                'www' => 'https://1btcxe.com',
                'doc' => 'https://1btcxe.com/api-docs.php',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'stats',
                        'historical-prices',
                        'order-book',
                        'transactions',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balances-and-info',
                        'open-orders',
                        'user-transactions',
                        'btc-deposit-address/get',
                        'btc-deposit-address/new',
                        'deposits/get',
                        'withdrawals/get',
                        'orders/new',
                        'orders/edit',
                        'orders/cancel',
                        'orders/status',
                        'withdrawals/new',
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        return array (
            array( 'id' => 'USD', 'symbol' => 'BTC/USD', 'base' => 'BTC', 'quote' => 'USD', 'baseId' => 'BTC', 'quoteId' => 'USD' ),
            array( 'id' => 'EUR', 'symbol' => 'BTC/EUR', 'base' => 'BTC', 'quote' => 'EUR', 'baseId' => 'BTC', 'quoteId' => 'EUR' ),
            array( 'id' => 'CNY', 'symbol' => 'BTC/CNY', 'base' => 'BTC', 'quote' => 'CNY', 'baseId' => 'BTC', 'quoteId' => 'CNY' ),
            array( 'id' => 'RUB', 'symbol' => 'BTC/RUB', 'base' => 'BTC', 'quote' => 'RUB', 'baseId' => 'BTC', 'quoteId' => 'RUB' ),
            array( 'id' => 'CHF', 'symbol' => 'BTC/CHF', 'base' => 'BTC', 'quote' => 'CHF', 'baseId' => 'BTC', 'quoteId' => 'CHF' ),
            array( 'id' => 'JPY', 'symbol' => 'BTC/JPY', 'base' => 'BTC', 'quote' => 'JPY', 'baseId' => 'BTC', 'quoteId' => 'JPY' ),
            array( 'id' => 'GBP', 'symbol' => 'BTC/GBP', 'base' => 'BTC', 'quote' => 'GBP', 'baseId' => 'BTC', 'quoteId' => 'GBP' ),
            array( 'id' => 'CAD', 'symbol' => 'BTC/CAD', 'base' => 'BTC', 'quote' => 'CAD', 'baseId' => 'BTC', 'quoteId' => 'CAD' ),
            array( 'id' => 'AUD', 'symbol' => 'BTC/AUD', 'base' => 'BTC', 'quote' => 'AUD', 'baseId' => 'BTC', 'quoteId' => 'AUD' ),
            array( 'id' => 'AED', 'symbol' => 'BTC/AED', 'base' => 'BTC', 'quote' => 'AED', 'baseId' => 'BTC', 'quoteId' => 'AED' ),
            array( 'id' => 'BGN', 'symbol' => 'BTC/BGN', 'base' => 'BTC', 'quote' => 'BGN', 'baseId' => 'BTC', 'quoteId' => 'BGN' ),
            array( 'id' => 'CZK', 'symbol' => 'BTC/CZK', 'base' => 'BTC', 'quote' => 'CZK', 'baseId' => 'BTC', 'quoteId' => 'CZK' ),
            array( 'id' => 'DKK', 'symbol' => 'BTC/DKK', 'base' => 'BTC', 'quote' => 'DKK', 'baseId' => 'BTC', 'quoteId' => 'DKK' ),
            array( 'id' => 'HKD', 'symbol' => 'BTC/HKD', 'base' => 'BTC', 'quote' => 'HKD', 'baseId' => 'BTC', 'quoteId' => 'HKD' ),
            array( 'id' => 'HRK', 'symbol' => 'BTC/HRK', 'base' => 'BTC', 'quote' => 'HRK', 'baseId' => 'BTC', 'quoteId' => 'HRK' ),
            array( 'id' => 'HUF', 'symbol' => 'BTC/HUF', 'base' => 'BTC', 'quote' => 'HUF', 'baseId' => 'BTC', 'quoteId' => 'HUF' ),
            array( 'id' => 'ILS', 'symbol' => 'BTC/ILS', 'base' => 'BTC', 'quote' => 'ILS', 'baseId' => 'BTC', 'quoteId' => 'ILS' ),
            array( 'id' => 'INR', 'symbol' => 'BTC/INR', 'base' => 'BTC', 'quote' => 'INR', 'baseId' => 'BTC', 'quoteId' => 'INR' ),
            array( 'id' => 'MUR', 'symbol' => 'BTC/MUR', 'base' => 'BTC', 'quote' => 'MUR', 'baseId' => 'BTC', 'quoteId' => 'MUR' ),
            array( 'id' => 'MXN', 'symbol' => 'BTC/MXN', 'base' => 'BTC', 'quote' => 'MXN', 'baseId' => 'BTC', 'quoteId' => 'MXN' ),
            array( 'id' => 'NOK', 'symbol' => 'BTC/NOK', 'base' => 'BTC', 'quote' => 'NOK', 'baseId' => 'BTC', 'quoteId' => 'NOK' ),
            array( 'id' => 'NZD', 'symbol' => 'BTC/NZD', 'base' => 'BTC', 'quote' => 'NZD', 'baseId' => 'BTC', 'quoteId' => 'NZD' ),
            array( 'id' => 'PLN', 'symbol' => 'BTC/PLN', 'base' => 'BTC', 'quote' => 'PLN', 'baseId' => 'BTC', 'quoteId' => 'PLN' ),
            array( 'id' => 'RON', 'symbol' => 'BTC/RON', 'base' => 'BTC', 'quote' => 'RON', 'baseId' => 'BTC', 'quoteId' => 'RON' ),
            array( 'id' => 'SEK', 'symbol' => 'BTC/SEK', 'base' => 'BTC', 'quote' => 'SEK', 'baseId' => 'BTC', 'quoteId' => 'SEK' ),
            array( 'id' => 'SGD', 'symbol' => 'BTC/SGD', 'base' => 'BTC', 'quote' => 'SGD', 'baseId' => 'BTC', 'quoteId' => 'SGD' ),
            array( 'id' => 'THB', 'symbol' => 'BTC/THB', 'base' => 'BTC', 'quote' => 'THB', 'baseId' => 'BTC', 'quoteId' => 'THB' ),
            array( 'id' => 'TRY', 'symbol' => 'BTC/TRY', 'base' => 'BTC', 'quote' => 'TRY', 'baseId' => 'BTC', 'quoteId' => 'TRY' ),
            array( 'id' => 'ZAR', 'symbol' => 'BTC/ZAR', 'base' => 'BTC', 'quote' => 'ZAR', 'baseId' => 'BTC', 'quoteId' => 'ZAR' ),
        );
    }

    public function fetch_balance ($params = array ()) {
        $response = $this->privatePostBalancesAndInfo ($params);
        $balance = $response['balances-and-info'];
        $result = array( 'info' => $balance );
        $codes = is_array($this->currencies) ? array_keys($this->currencies) : array();
        for ($i = 0; $i < count ($codes); $i++) {
            $code = $codes[$i];
            $currency = $this->currency ($code);
            $currencyId = $currency['id'];
            $account = $this->account ();
            $account['free'] = $this->safe_float($balance['available'], $currencyId, 0.0);
            $account['used'] = $this->safe_float($balance['on_hold'], $currencyId, 0.0);
            $account['total'] = $this->sum ($account['free'], $account['used']);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $request = array (
            'currency' => $this->market_id($symbol),
        );
        $response = $this->publicGetOrderBook (array_merge ($request, $params));
        return $this->parse_order_book($response['order-book'], null, 'bid', 'ask', 'price', 'order_amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $request = array (
            'currency' => $this->market_id($symbol),
        );
        $response = $this->publicGetStats (array_merge ($request, $params));
        $ticker = $this->safe_value($response, 'stats', array());
        $last = $this->safe_float($ticker, 'last_price');
        return array (
            'symbol' => $symbol,
            'timestamp' => null,
            'datetime' => null,
            'high' => $this->safe_float($ticker, 'max'),
            'low' => $this->safe_float($ticker, 'min'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $this->safe_float($ticker, 'daily_change'),
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => $this->safe_float($ticker, 'total_btc_traded'),
            'info' => $ticker,
        );
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1d', $since = null, $limit = null) {
        return [
            $this->parse8601 ($ohlcv['date'] . ' 00:00:00'),
            null,
            null,
            null,
            $this->safe_float($ohlcv, 'price'),
            null,
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1d', $since = null, $limit = null, $params = array ()) {
        $market = $this->market ($symbol);
        $response = $this->publicGetHistoricalPrices (array_merge (array (
            'currency' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
        ), $params));
        $ohlcvs = $this->to_array($this->omit ($response['historical-prices'], 'request_currency'));
        return $this->parse_ohlcvs($ohlcvs, $market, $timeframe, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_integer($trade, 'timestamp');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $id = $this->safe_string($trade, 'id');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $type = null;
        $side = $this->safe_string($trade, 'maker_type');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => null,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $market = $this->market ($symbol);
        $request = array (
            'currency' => $market['id'],
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetTransactions (array_merge ($request, $params));
        $trades = $this->to_array($this->omit ($response['transactions'], 'request_currency'));
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $request = array (
            'side' => $side,
            'type' => $type,
            'currency' => $this->market_id($symbol),
            'amount' => $amount,
        );
        if ($type === 'limit') {
            $request['limit_price'] = $price;
        }
        $result = $this->privatePostOrdersNew (array_merge ($request, $params));
        return array (
            'info' => $result,
            'id' => $result,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $request = array (
            'id' => $id,
        );
        return $this->privatePostOrdersCancel (array_merge ($request, $params));
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
            'amount' => floatval ($amount),
            'address' => $address,
        );
        $response = $this->privatePostWithdrawalsNew (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['result']['uuid'],
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        if ($this->id === 'cryptocapital') {
            throw new ExchangeError($this->id . ' is an abstract base API for _1btcxe');
        }
        $url = $this->urls['api'] . '/' . $path;
        if ($api === 'public') {
            if ($params) {
                $url .= '?' . $this->urlencode ($params);
            }
        } else {
            $this->check_required_credentials();
            $query = array_merge (array (
                'api_key' => $this->apiKey,
                'nonce' => $this->nonce (),
            ), $params);
            $request = $this->json ($query);
            $query['signature'] = $this->hmac ($this->encode ($request), $this->encode ($this->secret));
            $body = $this->json ($query);
            $headers = array( 'Content-Type' => 'application/json' );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (gettype ($response) === 'string') {
            if (mb_strpos($response, 'Maintenance') !== false) {
                throw new ExchangeNotAvailable($this->id . ' on maintenance');
            }
        }
        if (is_array($response) && array_key_exists('errors', $response)) {
            $errors = array();
            for ($e = 0; $e < count ($response['errors']); $e++) {
                $error = $response['errors'][$e];
                $errors[] = $error['code'] . ' => ' . $error['message'];
            }
            $errors = implode(' ', $errors);
            throw new ExchangeError($this->id . ' ' . $errors);
        }
        return $response;
    }
}
