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
                'fetchOrder' => 'emulated',
                'fetchOpenOrders' => 'emulated',
                'fetchClosedOrders' => 'emulated',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/28051642-56154182-660e-11e7-9b0d-6042d1e6edd8.jpg',
                'api' => 'https://api.bitflyer.jp',
                'www' => 'https://bitflyer.jp',
                'doc' => 'https://lightning.bitflyer.com/docs?lang=en',
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
                        'getbalancehistory',
                        'getcollateral',
                        'getcollateralhistory',
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

    public function fetch_markets ($params = array ()) {
        $jp_markets = $this->publicGetGetmarkets ($params);
        $us_markets = $this->publicGetGetmarketsUsa ($params);
        $eu_markets = $this->publicGetGetmarketsEu ($params);
        $markets = $this->array_concat($jp_markets, $us_markets);
        $markets = $this->array_concat($markets, $eu_markets);
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'product_code');
            $spot = true;
            $future = false;
            $type = 'spot';
            if (is_array($market) && array_key_exists('alias', $market)) {
                $type = 'future';
                $future = true;
                $spot = false;
            }
            $currencies = explode('_', $id);
            $baseId = null;
            $quoteId = null;
            $base = null;
            $quote = null;
            $numCurrencies = is_array ($currencies) ? count ($currencies) : 0;
            if ($numCurrencies === 1) {
                $baseId = mb_substr($id, 0, 3 - 0);
                $quoteId = mb_substr($id, 3, 6 - 3);
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
        $response = $this->privateGetGetbalance ($params);
        //
        //     array (
        //         array (
        //             "currency_code" => "JPY",
        //             "amount" => 1024078,
        //             "available" => 508000
        //         ),
        //         array (
        //             "currency_code" => "BTC",
        //             "amount" => 10.24,
        //             "available" => 4.12
        //         ),
        //         {
        //             "currency_code" => "ETH",
        //             "amount" => 20.48,
        //             "available" => 16.38
        //         }
        //     )
        //
        $result = array( 'info' => $response );
        $balances = array();
        for ($i = 0; $i < count ($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'currency_code');
            $code = $this->common_currency_code($currencyId);
            $account = $this->account ();
            $account['total'] = $this->safe_float($balance, 'amount');
            $account['free'] = $this->safe_float($balance, 'available');
            $account['used'] = $account['total'] - $account['free'];
            $balances[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'product_code' => $this->market_id($symbol),
        );
        $orderbook = $this->publicGetGetboard (array_merge ($request, $params));
        return $this->parse_order_book($orderbook, null, 'bids', 'asks', 'price', 'size');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $request = array (
            'product_code' => $this->market_id($symbol),
        );
        $ticker = $this->publicGetGetticker (array_merge ($request, $params));
        $timestamp = $this->parse8601 ($this->safe_string($ticker, 'timestamp'));
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
        $side = $this->safe_string($trade, 'side');
        $order = null;
        if ($side !== null) {
            $side = strtolower($trade['side']);
            $id = $side . '_child_order_acceptance_id';
            if (is_array($trade) && array_key_exists($id, $trade)) {
                $order = $trade[$id];
            }
        }
        if ($order === null) {
            $order = $this->safe_string($trade, 'child_order_acceptance_id');
        }
        $timestamp = $this->parse8601 ($this->safe_string($trade, 'exec_date'));
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'size');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $price * $amount;
            }
        }
        $id = $this->safe_string($trade, 'id');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $order,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_code' => $market['id'],
        );
        $response = $this->publicGetGetexecutions (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'product_code' => $this->market_id($symbol),
            'child_order_type' => strtoupper($type),
            'side' => strtoupper($side),
            'price' => $price,
            'size' => $amount,
        );
        $result = $this->privatePostSendchildorder (array_merge ($request, $params));
        // array( "status" => - 200, "error_message" => "Insufficient funds", "data" => null )
        $id = $this->safe_string($result, 'child_order_acceptance_id');
        return array (
            'info' => $result,
            'id' => $id,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder() requires a `$symbol` argument');
        }
        $this->load_markets();
        $request = array (
            'product_code' => $this->market_id($symbol),
            'child_order_acceptance_id' => $id,
        );
        return $this->privatePostCancelchildorder (array_merge ($request, $params));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'ACTIVE' => 'open',
            'COMPLETED' => 'closed',
            'CANCELED' => 'canceled',
            'EXPIRED' => 'canceled',
            'REJECTED' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        $timestamp = $this->parse8601 ($this->safe_string($order, 'child_order_date'));
        $amount = $this->safe_float($order, 'size');
        $remaining = $this->safe_float($order, 'outstanding_size');
        $filled = $this->safe_float($order, 'executed_size');
        $price = $this->safe_float($order, 'price');
        $cost = $price * $filled;
        $status = $this->parse_order_status($this->safe_string($order, 'child_order_state'));
        $type = $this->safe_string($order, 'child_order_type');
        if ($type !== null) {
            $type = strtolower($type);
        }
        $side = $this->safe_string($order, 'side');
        if ($side !== null) {
            $side = strtolower($side);
        }
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'product_code');
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $fee = null;
        $feeCost = $this->safe_float($order, 'total_commission');
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => null,
                'rate' => null,
            );
        }
        $id = $this->safe_string($order, 'child_order_acceptance_id');
        return array (
            'id' => $id,
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
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrders() requires a `$symbol` argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_code' => $market['id'],
            'count' => $limit,
        );
        $response = $this->privateGetGetchildorders (array_merge ($request, $params));
        $orders = $this->parse_orders($response, $market, $since, $limit);
        if ($symbol !== null) {
            $orders = $this->filter_by($orders, 'symbol', $symbol);
        }
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
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrder() requires a `$symbol` argument');
        }
        $orders = $this->fetch_orders($symbol);
        $ordersById = $this->index_by($orders, 'id');
        if (is_array($ordersById) && array_key_exists($id, $ordersById)) {
            return $ordersById[$id];
        }
        throw new OrderNotFound($this->id . ' No order found with $id ' . $id);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchMyTrades requires a `$symbol` argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_code' => $market['id'],
        );
        if ($limit !== null) {
            $request['count'] = $limit;
        }
        $response = $this->privateGetGetexecutions (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        if ($code !== 'JPY' && $code !== 'USD' && $code !== 'EUR') {
            throw new ExchangeError($this->id . ' allows withdrawing JPY, USD, EUR only, ' . $code . ' is not supported');
        }
        $currency = $this->currency ($code);
        $request = array (
            'currency_code' => $currency['id'],
            'amount' => $amount,
            // 'bank_account_id' => 1234,
        );
        $response = $this->privatePostWithdraw (array_merge ($request, $params));
        $id = $this->safe_string($response, 'message_id');
        return array (
            'info' => $response,
            'id' => $id,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->version . '/';
        if ($api === 'private') {
            $request .= 'me/';
        }
        $request .= $path;
        if ($method === 'GET') {
            if ($params) {
                $request .= '?' . $this->urlencode ($params);
            }
        }
        $url = $this->urls['api'] . $request;
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = implode('', array($nonce, $method, $request));
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
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
