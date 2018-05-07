<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinnest extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinnest',
            'name' => 'coinnest',
            'countries' => 'KR',
            'rateLimit' => 1000,
            'has' => array (
                'fetchOpenOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/38065728-7289ff5c-330d-11e8-9cc1-cf0cbcb606bc.jpg',
                'api' => array (
                    'public' => 'https://api.coinnest.co.kr/api',
                    'private' => 'https://api.coinnest.co.kr/api',
                    'web' => 'https://www.coinnest.co.kr',
                ),
                'www' => 'https://www.coinnest.co.kr',
                'doc' => 'https://www.coinnest.co.kr/doc/intro.html',
                'fees' => array (
                    'https://coinnesthelp.zendesk.com/hc/ko/articles/115002110252-%EA%B1%B0%EB%9E%98-%EC%88%98%EC%88%98%EB%A3%8C%EB%8A%94-%EC%96%BC%EB%A7%88%EC%9D%B8%EA%B0%80%EC%9A%94-',
                    'https://coinnesthelp.zendesk.com/hc/ko/articles/115002110272-%EB%B9%84%ED%8A%B8%EC%BD%94%EC%9D%B8-%EC%88%98%EC%88%98%EB%A3%8C%EB%A5%BC-%EC%84%A0%ED%83%9D%ED%95%98%EB%8A%94-%EC%9D%B4%EC%9C%A0%EA%B0%80-%EB%AC%B4%EC%97%87%EC%9D%B8%EA%B0%80%EC%9A%94-',
                ),
            ),
            'api' => array (
                'web' => array (
                    'get' => array (
                        'coin/allcoin',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'pub/ticker',
                        'pub/depth',
                        'pub/trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'account/balance',
                        'trade/add',
                        'trade/cancel',
                        'trade/fetchtrust',
                        'trade/trust',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.1 / 100,
                    'taker' => 0.1 / 100,
                ),
                'funding' => array (
                    'withdraw' => array (
                        'BTC' => '0.002',
                    ),
                ),
            ),
            'precision' => array (
                'amount' => 8,
                'price' => 8,
            ),
        ));
    }

    public function fetch_markets () {
        $quote = 'KRW';
        $quoteId = strtolower ($quote);
        // todo => rewrite this for web endpoint
        $coins = array (
            'btc',
            'bch',
            'btg',
            'bcd',
            'ubtc',
            'btn',
            'kst',
            'ltc',
            'act',
            'eth',
            'etc',
            'ada',
            'qtum',
            'xlm',
            'neo',
            'gas',
            'rpx',
            'hsr',
            'knc',
            'tsl',
            'tron',
            'omg',
            'wtc',
            'mco',
            'storm',
            'gto',
            'pxs',
            'chat',
            'ink',
            'oc',
            'hlc',
            'ent',
            'qbt',
            'spc',
            'put',
        );
        $result = array ();
        for ($i = 0; $i < count ($coins); $i++) {
            $baseId = $coins[$i];
            $id = $baseId . '/' . $quoteId;
            $base = $this->common_currency_code(strtoupper ($baseId));
            $symbol = $base . '/' . $quote;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'info' => null,
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $ticker['time'] * 1000;
        $symbol = $market['symbol'];
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'sell'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetPubTicker (array_merge (array (
            'coin' => $market['baseId'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $orderbook = $this->publicGetPubDepth (array_merge (array (
            'coin' => $market['baseId'],
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = intval ($trade['date']) * 1000;
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $symbol = $market['symbol'];
        $cost = $this->price_to_precision($symbol, $amount * $price);
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'tid'),
            'order' => null,
            'type' => 'limit',
            'side' => $trade['type'],
            'price' => $price,
            'amount' => $amount,
            'cost' => floatval ($cost),
            'fee' => null,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $trades = $this->publicGetPubTrades (array_merge (array (
            'coin' => $market['baseId'],
        ), $params));
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostAccountBalance ($params);
        $result = array ( 'info' => $response );
        $balancKeys = is_array ($response) ? array_keys ($response) : array ();
        for ($i = 0; $i < count ($balancKeys); $i++) {
            $key = $balancKeys[$i];
            $parts = explode ('_', $key);
            if (strlen ($parts) !== 2)
                continue;
            $type = $parts[1];
            if ($type !== 'reserved' && $type !== 'balance')
                continue;
            $currency = strtoupper ($parts[0]);
            $currency = $this->common_currency_code($currency);
            if (!(is_array ($result) && array_key_exists ($currency, $result))) {
                $result[$currency] = array (
                    'free' => 0.0,
                    'used' => 0.0,
                    'total' => 0.0,
                );
            }
            $type = ($type === 'reserved' ? 'used' : 'free');
            $result[$currency][$type] = floatval ($response[$key]);
            $otherType = ($type === 'used' ? 'free' : 'used');
            if (is_array ($result[$currency]) && array_key_exists ($otherType, $result[$currency]))
                $result[$currency]['total'] = $this->sum ($result[$currency]['free'], $result[$currency]['used']);
        }
        return $this->parse_balance($result);
    }

    public function parse_order ($order, $market) {
        $symbol = $market['symbol'];
        $timestamp = intval ($order['time']) * 1000;
        $status = intval ($order['status']);
        // 1 => newly created, 2 => ready for dealing, 3 => canceled, 4 => completed.
        if ($status === 4) {
            $status = 'closed';
        } else if ($status === 3) {
            $status = 'canceled';
        } else {
            $status = 'open';
        }
        $amount = $this->safe_float($order, 'amount_total');
        $remaining = $this->safe_float($order, 'amount_over');
        $filled = $this->safe_value($order, 'deals');
        if ($filled) {
            $filled = $this->safe_float($filled, 'sum_amount');
        } else {
            $filled = $amount - $remaining;
        }
        return array (
            'id' => $this->safe_string($order, 'id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $order['type'],
            'price' => $this->safe_float($order, 'price'),
            'cost' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => null,
            'info' => $this->safe_value($order, 'info', $order),
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostTradeAdd (array_merge (array (
            'coin' => $market['baseId'],
            'type' => $side,
            'number' => $amount,
            'price' => $price,
        ), $params));
        $order = array (
            'id' => $response['id'],
            'time' => $this->seconds (),
            'type' => $side,
            'price' => $price,
            'amount_total' => $amount,
            'amount_over' => $amount,
            'info' => $response,
        );
        $id = $order['id'];
        $this->orders[$id] = $this->parse_order($order, $market);
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostTradeCancel (array_merge (array (
            'id' => $id,
            'coin' => $market['baseId'],
        ), $params));
        return $response;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $order = $this->privatePostTradeFetchtrust (array_merge (array (
            'id' => $id,
            'coin' => $market['baseId'],
        ), $params));
        return $this->parse_order($order, $market);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'coin' => $market['baseId'],
        );
        if ($since)
            $request['since'] = intval ($since / 1000);
        if ($limit)
            $request['limit'] = $limit;
        $response = $this->privatePostTradeTrust (array_merge ($request, $params));
        return $this->parse_orders($response, $market);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'type' => '1',
        ), $params));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/' . $path;
        $query = null;
        if ($api === 'public') {
            $query = $this->urlencode ($params);
            if (strlen ($query))
                $url .= '?' . $query;
        } else {
            $this->check_required_credentials();
            $body = $this->urlencode (array_merge ($params, array (
                'key' => $this->apiKey,
                'nonce' => $this->nonce (),
            )));
            $secret = $this->hash ($this->secret);
            $body .= '&signature=' . $this->hmac ($this->encode ($body), $this->encode ($secret));
            $headers = array ( 'Content-type' => 'application/x-www-form-urlencoded' );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        $status = $this->safe_string($response, 'status');
        if (!$response || $response === 'nil' || $status) {
            $ErrorClass = $this->safe_value(array (
                '100' => '\\ccxt\\DDoSProtection',
                '101' => '\\ccxt\\DDoSProtection',
                '104' => '\\ccxt\\AuthenticationError',
                '105' => '\\ccxt\\AuthenticationError',
                '106' => '\\ccxt\\DDoSProtection',
            ), $status, '\\ccxt\\ExchangeError');
            $message = $this->safe_string($response, 'msg', $this->json ($response));
            throw new $ErrorClass ($message);
        }
        return $response;
    }
}
