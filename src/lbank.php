<?php

namespace ccxt;

use Exception as Exception; // a common import

class lbank extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'lbank',
            'name' => 'LBank',
            'countries' => array ( 'CN' ),
            'version' => 'v1',
            'has' => array (
                'fetchTickers' => true,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => false, // status 0 API doesn't work
                'fetchClosedOrders' => true,
            ),
            'timeframes' => array (
                '1m' => 'minute1',
                '5m' => 'minute5',
                '15m' => 'minute15',
                '30m' => 'minute30',
                '1h' => 'hour1',
                '2h' => 'hour2',
                '4h' => 'hour4',
                '6h' => 'hour6',
                '8h' => 'hour8',
                '12h' => 'hour12',
                '1d' => 'day1',
                '1w' => 'week1',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/38063602-9605e28a-3302-11e8-81be-64b1e53c4cfb.jpg',
                'api' => 'https://api.lbank.info',
                'www' => 'https://www.lbank.info',
                'doc' => 'https://github.com/LBank-exchange/lbank-official-api-docs',
                'fees' => 'https://lbankinfo.zendesk.com/hc/zh-cn/articles/115002295114--%E8%B4%B9%E7%8E%87%E8%AF%B4%E6%98%8E',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currencyPairs',
                        'ticker',
                        'depth',
                        'trades',
                        'kline',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'user_info',
                        'create_order',
                        'cancel_order',
                        'orders_info',
                        'orders_info_history',
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
                        'BTC' => null,
                        'ZEC' => 0.01,
                        'ETH' => 0.01,
                        'ETC' => 0.01,
                        // 'QTUM' => amount => max (0.01, amount * (0.1 / 100)),
                        'VEN' => 10.0,
                        'BCH' => 0.0002,
                        'SC' => 50.0,
                        'BTM' => 20.0,
                        'NAS' => 1.0,
                        'EOS' => 1.0,
                        'XWC' => 5.0,
                        'BTS' => 1.0,
                        'INK' => 10.0,
                        'BOT' => 3.0,
                        'YOYOW' => 15.0,
                        'TGC' => 10.0,
                        'NEO' => 0.0,
                        'CMT' => 20.0,
                        'SEER' => 2000.0,
                        'FIL' => null,
                        'BTG' => null,
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetCurrencyPairs ();
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $id = $markets[$i];
            list ($baseId, $quoteId) = explode ('_', $id);
            $base = $this->common_currency_code(strtoupper ($baseId));
            $quote = $this->common_currency_code(strtoupper ($quoteId));
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => 8,
                'price' => 8,
            );
            $lot = pow (10, -$precision['amount']);
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'lot' => $lot,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $lot,
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision['price']),
                        'max' => pow (10, $precision['price']),
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $id,
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = $this->safe_integer($ticker, 'timestamp');
        $info = $ticker;
        $ticker = $info['ticker'];
        $last = $this->safe_float($ticker, 'latest');
        $percentage = $this->safe_float($ticker, 'change');
        $relativeChange = $percentage / 100;
        $open = $last / $this->sum (1, $relativeChange);
        $change = $last - $open;
        $average = $this->sum ($last, $open) / 2;
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => null,
            'bidVolume' => null,
            'ask' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $average,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => $this->safe_float($ticker, 'turnover'),
            'info' => $info,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetTicker (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($response, $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetTicker (array_merge (array (
            'symbol' => 'all',
        ), $params));
        $result = array ();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $tickers[$i];
            $id = $ticker['symbol'];
            if (is_array ($this->marketsById) && array_key_exists ($id, $this->marketsById)) {
                $market = $this->marketsById[$id];
                $symbol = $market['symbol'];
                $result[$symbol] = $this->parse_ticker($ticker, $market);
            }
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = 60, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetDepth (array_merge (array (
            'symbol' => $this->market_id($symbol),
            'size' => min ($limit, 60),
        ), $params));
        return $this->parse_order_book($response);
    }

    public function parse_trade ($trade, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = intval ($trade['date_ms']);
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = $this->cost_to_precision($symbol, $price * $amount);
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'tid'),
            'order' => null,
            'type' => null,
            'side' => $trade['type'],
            'price' => $price,
            'amount' => $amount,
            'cost' => floatval ($cost),
            'fee' => null,
            'info' => $this->safe_value($trade, 'info', $trade),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'size' => 100,
        );
        if ($since)
            $request['time'] = intval ($since / 1000);
        if ($limit)
            $request['size'] = $limit;
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

    public function fetch_ohlcv ($symbol, $timeframe = '5m', $since = null, $limit = 1000, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($since === null)
            throw new ExchangeError ($this->id . ' fetchOHLCV requires a $since argument');
        if ($limit === null)
            throw new ExchangeError ($this->id . ' fetchOHLCV requires a $limit argument');
        $request = array (
            'symbol' => $market['id'],
            'type' => $this->timeframes[$timeframe],
            'size' => $limit,
            'time' => intval ($since / 1000),
        );
        $response = $this->publicGetKline (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostUserInfo ($params);
        $result = array ( 'info' => $response );
        $ids = is_array (array_merge ($response['info']['free'], $response['info']['freeze'])) ? array_keys (array_merge ($response['info']['free'], $response['info']['freeze'])) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $code = $id;
            if (is_array ($this->currencies_by_id) && array_key_exists ($id, $this->currencies_by_id))
                $code = $this->currencies_by_id[$id]['code'];
            $free = $this->safe_float($response['info']['free'], $id, 0.0);
            $used = $this->safe_float($response['info']['freeze'], $id, 0.0);
            $account = array (
                'free' => $free,
                'used' => $used,
                'total' => 0.0,
            );
            $account['total'] = $this->sum ($account['free'], $account['used']);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '-1' => 'cancelled', // cancelled
            '0' => 'open', // not traded
            '1' => 'open', // partial deal
            '2' => 'closed', // complete deal
            '4' => 'closed', // disposal processing
        );
        return $this->safe_string($statuses, $status);
    }

    public function parse_order ($order, $market = null) {
        $symbol = null;
        $responseMarket = $this->safe_value($this->marketsById, $order['symbol']);
        if ($responseMarket !== null) {
            $symbol = $responseMarket['symbol'];
        } else if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer($order, 'create_time');
        // Limit Order Request Returns => Order Price
        // Market Order Returns => cny $amount of $market $order
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'amount', 0.0);
        $filled = $this->safe_float($order, 'deal_amount', 0.0);
        $av_price = $this->safe_float($order, 'avg_price');
        $cost = null;
        if ($av_price !== null) {
            $cost = $filled * $av_price;
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        return array (
            'id' => $this->safe_string($order, 'order_id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $this->safe_string($order, 'order_type'),
            'side' => $order['type'],
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $amount - $filled,
            'trades' => null,
            'fee' => null,
            'info' => $this->safe_value($order, 'info', $order),
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $order = array (
            'symbol' => $market['id'],
            'type' => $side,
            'amount' => $amount,
        );
        if ($type === 'market') {
            $order['type'] .= '_market';
        } else {
            $order['price'] = $price;
        }
        $response = $this->privatePostCreateOrder (array_merge ($order, $params));
        $order = $this->omit ($order, 'type');
        $order['order_id'] = $response['order_id'];
        $order['type'] = $side;
        $order['order_type'] = $type;
        $order['create_time'] = $this->milliseconds ();
        $order['info'] = $response;
        $order = $this->parse_order($order, $market);
        $id = $order['id'];
        $this->orders[$id] = $order;
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostCancelOrder (array_merge (array (
            'symbol' => $market['id'],
            'order_id' => $id,
        ), $params));
        return $response;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        // Id can be a list of ids delimited by a comma
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostOrdersInfo (array_merge (array (
            'symbol' => $market['id'],
            'order_id' => $id,
        ), $params));
        $orders = $this->parse_orders($response['orders'], $market);
        if (strlen ($orders) === 1) {
            return $orders[0];
        } else {
            return $orders;
        }
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($limit === null) {
            $limit = 100;
        }
        $market = $this->market ($symbol);
        $response = $this->privatePostOrdersInfoHistory (array_merge (array (
            'symbol' => $market['id'],
            'current_page' => 1,
            'page_length' => $limit,
        ), $params));
        return $this->parse_orders($response['orders'], null, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, $params);
        $closed = $this->filter_by($orders, 'status', 'closed');
        $cancelled = $this->filter_by($orders, 'status', 'cancelled'); // $cancelled $orders may be partially filled
        return $closed . $cancelled;
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'] . '/' . $this->version . '/' . $this->implode_params($path, $params);
        // Every endpoint ends with ".do"
        $url .= '.do';
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $query = $this->keysort (array_merge (array (
                'api_key' => $this->apiKey,
            ), $params));
            $queryString = $this->rawencode ($query) . '&secret_key=' . $this->secret;
            $query['sign'] = strtoupper ($this->hash ($this->encode ($queryString)));
            $body = $this->urlencode ($query);
            $headers = array ( 'Content-Type' => 'application/x-www-form-urlencoded' );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        $success = $this->safe_string($response, 'result');
        if ($success === 'false') {
            $errorCode = $this->safe_string($response, 'error_code');
            $message = $this->safe_string(array (
                '10000' => 'Internal error',
                '10001' => 'The required parameters can not be empty',
                '10002' => 'verification failed',
                '10003' => 'Illegal parameters',
                '10004' => 'User requests are too frequent',
                '10005' => 'Key does not exist',
                '10006' => 'user does not exist',
                '10007' => 'Invalid signature',
                '10008' => 'This currency pair is not supported',
                '10009' => 'Limit orders can not be missing orders and the number of orders',
                '10010' => 'Order price or order quantity must be greater than 0',
                '10011' => 'Market orders can not be missing the amount of the order',
                '10012' => 'market sell orders can not be missing orders',
                '10013' => 'is less than the minimum trading position 0.001',
                '10014' => 'Account number is not enough',
                '10015' => 'The order type is wrong',
                '10016' => 'Account balance is not enough',
                '10017' => 'Abnormal server',
                '10018' => 'order inquiry can not be more than 50 less than one',
                '10019' => 'withdrawal orders can not be more than 3 less than one',
                '10020' => 'less than the minimum amount of the transaction limit of 0.001',
            ), $errorCode, $this->json ($response));
            $ErrorClass = $this->safe_value(array (
                '10002' => '\\ccxt\\AuthenticationError',
                '10004' => '\\ccxt\\DDoSProtection',
                '10005' => '\\ccxt\\AuthenticationError',
                '10006' => '\\ccxt\\AuthenticationError',
                '10007' => '\\ccxt\\AuthenticationError',
                '10009' => '\\ccxt\\InvalidOrder',
                '10010' => '\\ccxt\\InvalidOrder',
                '10011' => '\\ccxt\\InvalidOrder',
                '10012' => '\\ccxt\\InvalidOrder',
                '10013' => '\\ccxt\\InvalidOrder',
                '10014' => '\\ccxt\\InvalidOrder',
                '10015' => '\\ccxt\\InvalidOrder',
                '10016' => '\\ccxt\\InvalidOrder',
            ), $errorCode, '\\ccxt\\ExchangeError');
            throw new $ErrorClass ($message);
        }
        return $response;
    }
}
