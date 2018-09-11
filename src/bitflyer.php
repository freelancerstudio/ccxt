<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitflyer extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitflyer',
            'name' => 'bitFlyer',
            'countries' => array ( 'JP' ),
            'version' => 'v1',
            'rateLimit' => 1000, // their nonce-timestamp is in seconds...
            'has' => array (
                'CORS' => false,
                'withdraw' => true,
                'fetchMyTrades' => true,
                'fetchOrders' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => 'emulated',
                'fetchClosedOrders' => 'emulated',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/28051642-56154182-660e-11e7-9b0d-6042d1e6edd8.jpg',
                'api' => 'https://api.bitflyer.jp',
                'www' => 'https://bitflyer.jp',
                'doc' => 'https://bitflyer.jp/API',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'getmarkets/usa', // new (wip)
                        'getmarkets/eu',  // new (wip)
                        'getmarkets',     // or 'markets'
                        'getboard',       // ...
                        'getticker',
                        'getexecutions',
                        'gethealth',
                        'getboardstate',
                        'getchats',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'getpermissions',
                        'getbalance',
                        'getcollateral',
                        'getcollateralaccounts',
                        'getaddresses',
                        'getcoinins',
                        'getcoinouts',
                        'getbankaccounts',
                        'getdeposits',
                        'getwithdrawals',
                        'getchildorders',
                        'getparentorders',
                        'getparentorder',
                        'getexecutions',
                        'getpositions',
                        'gettradingcommission',
                    ),
                    'post' => array (
                        'sendcoin',
                        'withdraw',
                        'sendchildorder',
                        'cancelchildorder',
                        'sendparentorder',
                        'cancelparentorder',
                        'cancelallchildorders',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.25 / 100,
                    'taker' => 0.25 / 100,
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $jp_markets = $this->publicGetGetmarkets ();
        $us_markets = $this->publicGetGetmarketsUsa ();
        $eu_markets = $this->publicGetGetmarketsEu ();
        $markets = $this->array_concat($jp_markets, $us_markets);
        $markets = $this->array_concat($markets, $eu_markets);
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = $market['product_code'];
            $spot = true;
            $future = false;
            $type = 'spot';
            if (is_array ($market) && array_key_exists ('alias', $market)) {
                $type = 'future';
                $future = true;
                $spot = false;
            }
            $currencies = explode ('_', $id);
            $baseId = null;
            $quoteId = null;
            $base = null;
            $quote = null;
            $numCurrencies = is_array ($currencies) ? count ($currencies) : 0;
            if ($numCurrencies === 1) {
                $baseId = mb_substr ($id, 0, 3);
                $quoteId = mb_substr ($id, 3, 6);
            } else if ($numCurrencies === 2) {
                $baseId = $currencies[0];
                $quoteId = $currencies[1];
            } else {
                $baseId = $currencies[1];
                $quoteId = $currencies[2];
            }
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = ($numCurrencies === 2) ? ($base . '/' . $quote) : $id;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'type' => $type,
                'spot' => $spot,
                'future' => $future,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetGetbalance ();
        $balances = array ();
        for ($b = 0; $b < count ($response); $b++) {
            $account = $response[$b];
            $currency = $account['currency_code'];
            $balances[$currency] = $account;
        }
        $result = array ( 'info' => $response );
        $currencies = is_array ($this->currencies) ? array_keys ($this->currencies) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $account = $this->account ();
            if (is_array ($balances) && array_key_exists ($currency, $balances)) {
                $account['total'] = $balances[$currency]['amount'];
                $account['free'] = $balances[$currency]['available'];
                $account['used'] = $account['total'] - $account['free'];
            }
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetGetboard (array_merge (array (
            'product_code' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook, null, 'bids', 'asks', 'price', 'size');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $ticker = $this->publicGetGetticker (array_merge (array (
            'product_code' => $this->market_id($symbol),
        ), $params));
        $timestamp = $this->parse8601 ($ticker['timestamp']);
        $last = $this->safe_float($ticker, 'ltp');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'best_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'best_ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume_by_product'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market = null) {
        $side = null;
        $order = null;
        if (is_array ($trade) && array_key_exists ('side', $trade))
            if ($trade['side']) {
                $side = strtolower ($trade['side']);
                $id = $side . '_child_order_acceptance_id';
                if (is_array ($trade) && array_key_exists ($id, $trade))
                    $order = $trade[$id];
            }
        if ($order === null)
            $order = $this->safe_string($trade, 'child_order_acceptance_id');
        $timestamp = $this->parse8601 ($trade['exec_date']);
        return array (
            'id' => (string) $trade['id'],
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'order' => $order,
            'type' => null,
            'side' => $side,
            'price' => $trade['price'],
            'amount' => $trade['size'],
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetGetexecutions (array_merge (array (
            'product_code' => $market['id'],
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'product_code' => $this->market_id($symbol),
            'child_order_type' => strtoupper ($type),
            'side' => strtoupper ($side),
            'price' => $price,
            'size' => $amount,
        );
        $result = $this->privatePostSendchildorder (array_merge ($order, $params));
        // array ( "status" => - 200, "error_message" => "Insufficient funds", "data" => null )
        return array (
            'info' => $result,
            'id' => $result['child_order_acceptance_id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' cancelOrder() requires a $symbol argument');
        $this->load_markets();
        return $this->privatePostCancelchildorder (array_merge (array (
            'product_code' => $this->market_id($symbol),
            'child_order_acceptance_id' => $id,
        ), $params));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'ACTIVE' => 'open',
            'COMPLETED' => 'closed',
            'CANCELED' => 'canceled',
            'EXPIRED' => 'canceled',
            'REJECTED' => 'canceled',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function parse_order ($order, $market = null) {
        $timestamp = $this->parse8601 ($order['child_order_date']);
        $amount = $this->safe_float($order, 'size');
        $remaining = $this->safe_float($order, 'outstanding_size');
        $filled = $this->safe_float($order, 'executed_size');
        $price = $this->safe_float($order, 'price');
        $cost = $price * $filled;
        $status = $this->parse_order_status($this->safe_string($order, 'child_order_state'));
        $type = strtolower ($order['child_order_type']);
        $side = strtolower ($order['side']);
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'product_code');
            if ($marketId !== null) {
                if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                    $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market !== null)
            $symbol = $market['symbol'];
        $fee = null;
        $feeCost = $this->safe_float($order, 'total_commission');
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => null,
                'rate' => null,
            );
        }
        return array (
            'id' => $order['child_order_acceptance_id'],
            'info' => $order,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'fee' => $fee,
        );
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = 100, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchOrders() requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_code' => $market['id'],
            'count' => $limit,
        );
        $response = $this->privateGetGetchildorders (array_merge ($request, $params));
        $orders = $this->parse_orders($response, $market, $since, $limit);
        if ($symbol !== null)
            $orders = $this->filter_by($orders, 'symbol', $symbol);
        return $orders;
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = 100, $params = array ()) {
        $request = array (
            'child_order_state' => 'ACTIVE',
        );
        return $this->fetch_orders($symbol, $since, $limit, array_merge ($request, $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = 100, $params = array ()) {
        $request = array (
            'child_order_state' => 'COMPLETED',
        );
        return $this->fetch_orders($symbol, $since, $limit, array_merge ($request, $params));
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchOrder() requires a $symbol argument');
        $orders = $this->fetch_orders($symbol);
        $ordersById = $this->index_by($orders, 'id');
        if (is_array ($ordersById) && array_key_exists ($id, $ordersById))
            return $ordersById[$id];
        throw new OrderNotFound ($this->id . ' No order found with $id ' . $id);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchMyTrades requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_code' => $market['id'],
        );
        if ($limit !== null)
            $request['count'] = $limit;
        $response = $this->privateGetGetexecutions (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        if ($code !== 'JPY' && $code !== 'USD' && $code !== 'EUR')
            throw new ExchangeError ($this->id . ' allows withdrawing JPY, USD, EUR only, ' . $code . ' is not supported');
        $currency = $this->currency ($code);
        $response = $this->privatePostWithdraw (array_merge (array (
            'currency_code' => $currency['id'],
            'amount' => $amount,
            // 'bank_account_id' => 1234,
        ), $params));
        return array (
            'info' => $response,
            'id' => $response['message_id'],
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->version . '/';
        if ($api === 'private')
            $request .= 'me/';
        $request .= $path;
        if ($method === 'GET') {
            if ($params)
                $request .= '?' . $this->urlencode ($params);
        }
        $url = $this->urls['api'] . $request;
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = implode ('', array ($nonce, $method, $request));
            if ($params) {
                if ($method !== 'GET') {
                    $body = $this->json ($params);
                    $auth .= $body;
                }
            }
            $headers = array (
                'ACCESS-KEY' => $this->apiKey,
                'ACCESS-TIMESTAMP' => $nonce,
                'ACCESS-SIGN' => $this->hmac ($this->encode ($auth), $this->encode ($this->secret)),
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
