<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinfloor extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinfloor',
            'name' => 'coinfloor',
            'rateLimit' => 1000,
            'countries' => array ( 'UK' ),
            'has' => array (
                'CORS' => false,
                'fetchOpenOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/28246081-623fc164-6a1c-11e7-913f-bac0d5576c90.jpg',
                'api' => 'https://webapi.coinfloor.co.uk:8090/bist',
                'www' => 'https://www.coinfloor.co.uk',
                'doc' => array (
                    'https://github.com/coinfloor/api',
                    'https://www.coinfloor.co.uk/api',
                ),
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => false,
                'password' => true,
                'uid' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        '{id}/ticker/',
                        '{id}/order_book/',
                        '{id}/transactions/',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        '{id}/balance/',
                        '{id}/user_transactions/',
                        '{id}/open_orders/',
                        '{id}/cancel_order/',
                        '{id}/buy/',
                        '{id}/sell/',
                        '{id}/buy_market/',
                        '{id}/sell_market/',
                        '{id}/estimate_sell_market/',
                        '{id}/estimate_buy_market/',
                    ),
                ),
            ),
            'markets' => array (
                'BTC/GBP' => array ( 'id' => 'XBT/GBP', 'symbol' => 'BTC/GBP', 'base' => 'BTC', 'quote' => 'GBP' ),
                'BTC/EUR' => array ( 'id' => 'XBT/EUR', 'symbol' => 'BTC/EUR', 'base' => 'BTC', 'quote' => 'EUR' ),
                'BTC/USD' => array ( 'id' => 'XBT/USD', 'symbol' => 'BTC/USD', 'base' => 'BTC', 'quote' => 'USD' ),
                'BTC/PLN' => array ( 'id' => 'XBT/PLN', 'symbol' => 'BTC/PLN', 'base' => 'BTC', 'quote' => 'PLN' ),
                'BCH/GBP' => array ( 'id' => 'BCH/GBP', 'symbol' => 'BCH/GBP', 'base' => 'BCH', 'quote' => 'GBP' ),
            ),
        ));
    }

    public function fetch_balance ($params = array ()) {
        $market = null;
        if (is_array ($params) && array_key_exists ('symbol', $params))
            $market = $this->find_market($params['symbol']);
        if (is_array ($params) && array_key_exists ('id', $params))
            $market = $this->find_market($params['id']);
        if (!$market)
            throw new NotSupported ($this->id . ' fetchBalance requires a symbol param');
        $response = $this->privatePostIdBalance (array (
            'id' => $market['id'],
        ));
        $result = array (
            'info' => $response,
        );
        // base/quote used for $keys e.g. "xbt_reserved"
        $keys = strtolower (explode ('/', $market['id']));
        $result[$market['base']] = array (
            'free' => floatval ($response[$keys[0] . '_available']),
            'used' => floatval ($response[$keys[0] . '_reserved']),
            'total' => floatval ($response[$keys[0] . '_balance']),
        );
        $result[$market['quote']] = array (
            'free' => floatval ($response[$keys[1] . '_available']),
            'used' => floatval ($response[$keys[1] . '_reserved']),
            'total' => floatval ($response[$keys[1] . '_balance']),
        );
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $orderbook = $this->publicGetIdOrderBook (array_merge (array (
            'id' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function parse_ticker ($ticker, $market = null) {
        // rewrite to get the $timestamp from HTTP headers
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $vwap = $this->safe_float($ticker, 'vwap');
        $baseVolume = $this->safe_float($ticker, 'volume');
        $quoteVolume = null;
        if ($vwap !== null) {
            $quoteVolume = $baseVolume * $vwap;
        }
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $market = $this->market ($symbol);
        $ticker = $this->publicGetIdTicker (array_merge (array (
            'id' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market) {
        $timestamp = $trade['date'] * 1000;
        return array (
            'info' => $trade,
            'id' => (string) $trade['tid'],
            'order' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => null,
            'price' => $this->safe_float($trade, 'price'),
            'amount' => $this->safe_float($trade, 'amount'),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $market = $this->market ($symbol);
        $response = $this->publicGetIdTransactions (array_merge (array (
            'id' => $market['id'],
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $order = array ( 'id' => $this->market_id($symbol) );
        $method = 'privatePostId' . $this->capitalize ($side);
        if ($type === 'market') {
            $order['quantity'] = $amount;
            $method .= 'Market';
        } else {
            $order['price'] = $price;
            $order['amount'] = $amount;
        }
        return $this->$method (array_merge ($order, $params));
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostIdCancelOrder (array ( 'id' => $id ));
    }

    public function parse_order ($order, $market = null) {
        $timestamp = $this->parse_date($order['datetime']);
        $datetime = $this->iso8601 ($timestamp);
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'amount');
        $cost = $price * $amount;
        $side = null;
        $status = $this->safe_string($order, 'status');
        if ($order['type'] === 0)
            $side = 'buy';
        else if ($order['type'] === 1)
            $side = 'sell';
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        $id = (string) $order['id'];
        return array (
            'info' => $order,
            'id' => $id,
            'datetime' => $datetime,
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (!$symbol)
            throw new NotSupported ($this->id . ' fetchOpenOrders requires a $symbol param');
        $this->load_markets();
        $market = $this->market ($symbol);
        $orders = $this->privatePostIdOpenOrders (array (
            'id' => $market['id'],
        ));
        for ($i = 0; $i < count ($orders); $i++) {
            // Coinfloor open $orders would always be $limit $orders
            $orders[$i] = array_merge ($orders[$i], array ( 'status' => 'open' ));
        }
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        // curl -k -u '[User ID]/[API key]:[Passphrase]' https://webapi.coinfloor.co.uk:8090/bist/XBT/GBP/balance/
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $body = $this->urlencode (array_merge (array ( 'nonce' => $nonce ), $query));
            $auth = $this->uid . '/' . $this->apiKey . ':' . $this->password;
            $signature = $this->decode (base64_encode ($this->encode ($auth)));
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . $signature,
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
