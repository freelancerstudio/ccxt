<?php

namespace ccxt;

use Exception as Exception; // a common import

class liquid extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'liquid',
            'name' => 'Liquid',
            'countries' => array ( 'JP', 'CN', 'TW' ),
            'version' => '2',
            'rateLimit' => 1000,
            'has' => array (
                'CORS' => false,
                'fetchTickers' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchMyTrades' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/45798859-1a872600-bcb4-11e8-8746-69291ce87b04.jpg',
                'api' => 'https://api.liquid.com',
                'www' => 'https://www.liquid.com',
                'doc' => array (
                    'https://developers.quoine.com',
                    'https://developers.quoine.com/v2',
                ),
                'fees' => 'https://help.liquid.com/getting-started-with-liquid/the-platform/fee-structure',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'products',
                        'products/{id}',
                        'products/{id}/price_levels',
                        'executions',
                        'ir_ladders/{currency}',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'accounts/balance',
                        'accounts/main_asset',
                        'crypto_accounts',
                        'executions/me',
                        'fiat_accounts',
                        'loan_bids',
                        'loans',
                        'orders',
                        'orders/{id}',
                        'orders/{id}/trades',
                        'orders/{id}/executions',
                        'trades',
                        'trades/{id}/loans',
                        'trading_accounts',
                        'trading_accounts/{id}',
                    ),
                    'post' => array (
                        'fiat_accounts',
                        'loan_bids',
                        'orders',
                    ),
                    'put' => array (
                        'loan_bids/{id}/close',
                        'loans/{id}',
                        'orders/{id}',
                        'orders/{id}/cancel',
                        'trades/{id}',
                        'trades/{id}/close',
                        'trades/close_all',
                        'trading_accounts/{id}',
                    ),
                ),
            ),
            'skipJsonOnStatusCodes' => [401],
            'exceptions' => array (
                'messages' => array (
                    'API Authentication failed' => '\\ccxt\\AuthenticationError',
                    'Nonce is too small' => '\\ccxt\\InvalidNonce',
                    'Order not found' => '\\ccxt\\OrderNotFound',
                    'user' => array (
                        'not_enough_free_balance' => '\\ccxt\\InsufficientFunds',
                    ),
                    'price' => array (
                        'must_be_positive' => '\\ccxt\\InvalidOrder',
                    ),
                    'quantity' => array (
                        'less_than_order_size' => '\\ccxt\\InvalidOrder',
                    ),
                ),
            ),
            'commonCurrencies' => array (
                'WIN' => 'WCOIN',
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetProducts ();
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = (string) $market['id'];
            $baseId = $market['base_currency'];
            $quoteId = $market['quoted_currency'];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $maker = $this->safe_float($market, 'maker_fee');
            $taker = $this->safe_float($market, 'taker_fee');
            $active = !$market['disabled'];
            $minAmount = null;
            $minPrice = null;
            if ($base === 'BTC') {
                $minAmount = 0.001;
            } else if ($base === 'ETH') {
                $minAmount = 0.01;
            }
            if ($quote === 'BTC') {
                $minPrice = 0.00000001;
            } else if ($quote === 'ETH' || $quote === 'USD' || $quote === 'JPY') {
                $minPrice = 0.00001;
            }
            $limits = array (
                'amount' => array ( 'min' => $minAmount ),
                'price' => array ( 'min' => $minPrice ),
                'cost' => array ( 'min' => null ),
            );
            if ($minPrice !== null)
                if ($minAmount !== null)
                    $limits['cost']['min'] = $minPrice * $minAmount;
            $precision = array (
                'amount' => null,
                'price' => null,
            );
            if ($minAmount !== null)
                $precision['amount'] = -log10 ($minAmount);
            if ($minPrice !== null)
                $precision['price'] = -log10 ($minPrice);
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'maker' => $maker,
                'taker' => $taker,
                'limits' => $limits,
                'precision' => $precision,
                'active' => $active,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privateGetAccountsBalance ($params);
        $result = array ( 'info' => $balances );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currencyId = $balance['currency'];
            $code = $currencyId;
            if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            }
            $total = floatval ($balance['balance']);
            $account = array (
                'free' => $total,
                'used' => 0.0,
                'total' => $total,
            );
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetProductsIdPriceLevels (array_merge (array (
            'id' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook, null, 'buy_price_levels', 'sell_price_levels');
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $last = null;
        if (is_array ($ticker) && array_key_exists ('last_traded_price', $ticker)) {
            if ($ticker['last_traded_price']) {
                $length = is_array ($ticker['last_traded_price']) ? count ($ticker['last_traded_price']) : 0;
                if ($length > 0)
                    $last = $this->safe_float($ticker, 'last_traded_price');
            }
        }
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($ticker, 'id');
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                $baseId = $this->safe_string($ticker, 'base_currency');
                $quoteId = $this->safe_string($ticker, 'quoted_currency');
                if (is_array ($this->markets) && array_key_exists ($symbol, $this->markets)) {
                    $market = $this->markets[$symbol];
                } else {
                    $symbol = $this->common_currency_code($baseId) . '/' . $this->common_currency_code($quoteId);
                }
            }
        }
        if ($market !== null)
            $symbol = $market['symbol'];
        $change = null;
        $percentage = null;
        $average = null;
        $open = $this->safe_float($ticker, 'last_price_24h');
        if ($open !== null && $last !== null) {
            $change = $last - $open;
            $average = $this->sum ($last, $open) / 2;
            if ($open > 0) {
                $percentage = $change / $open * 100;
            }
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high_market_ask'),
            'low' => $this->safe_float($ticker, 'low_market_bid'),
            'bid' => $this->safe_float($ticker, 'market_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'market_ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $average,
            'baseVolume' => $this->safe_float($ticker, 'volume_24h'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetProducts ($params);
        $result = array ();
        for ($t = 0; $t < count ($tickers); $t++) {
            $ticker = $this->parse_ticker($tickers[$t]);
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetProductsId (array_merge (array (
            'id' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market) {
        // {             id =>  12345,
        //         quantity => "6.789",
        //            price => "98765.4321",
        //       taker_side => "sell",
        //       created_at =>  1512345678,
        //          my_side => "buy"           }
        $timestamp = $trade['created_at'] * 1000;
        // 'taker_side' gets filled for both fetchTrades and fetchMyTrades
        $takerSide = $this->safe_string($trade, 'taker_side');
        // 'my_side' gets filled for fetchMyTrades only and may differ from 'taker_side'
        $mySide = $this->safe_string($trade, 'my_side');
        $side = ($mySide !== null) ? $mySide : $takerSide;
        $takerOrMaker = null;
        if ($mySide !== null)
            $takerOrMaker = ($takerSide === $mySide) ? 'taker' : 'maker';
        return array (
            'info' => $trade,
            'id' => (string) $trade['id'],
            'order' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => $side,
            'takerOrMaker' => $takerOrMaker,
            'price' => $this->safe_float($trade, 'price'),
            'amount' => $this->safe_float($trade, 'quantity'),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_id' => $market['id'],
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        if ($since !== null) {
            // timestamp should be in seconds, whereas we use milliseconds in $since and everywhere
            $request['timestamp'] = intval ($since / 1000);
        }
        $response = $this->publicGetExecutions (array_merge ($request, $params));
        $result = ($since !== null) ? $response : $response['models'];
        return $this->parse_trades($result, $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_id' => $market['id'],
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        $response = $this->privateGetExecutionsMe (array_merge ($request, $params));
        return $this->parse_trades($response['models'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'order_type' => $type,
            'product_id' => $this->market_id($symbol),
            'side' => $side,
            'quantity' => $amount,
        );
        if ($type === 'limit')
            $order['price'] = $price;
        $response = $this->privatePostOrders (array_merge ($order, $params));
        return $this->parse_order($response);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $result = $this->privatePutOrdersIdCancel (array_merge (array (
            'id' => $id,
        ), $params));
        $order = $this->parse_order($result);
        if ($order['status'] === 'closed')
            throw new OrderNotFound ($this->id . ' ' . $this->json ($order));
        return $order;
    }

    public function parse_order ($order, $market = null) {
        $timestamp = $order['created_at'] * 1000;
        $marketId = $this->safe_string($order, 'product_id');
        if ($marketId !== null) {
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                $market = $this->markets_by_id[$marketId];
        }
        $status = null;
        if (is_array ($order) && array_key_exists ('status', $order)) {
            if ($order['status'] === 'live') {
                $status = 'open';
            } else if ($order['status'] === 'filled') {
                $status = 'closed';
            } else if ($order['status'] === 'cancelled') { // 'll' intended
                $status = 'canceled';
            }
        }
        $amount = $this->safe_float($order, 'quantity');
        $filled = $this->safe_float($order, 'filled_quantity');
        $price = $this->safe_float($order, 'price');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'id' => (string) $order['id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'type' => $order['order_type'],
            'status' => $status,
            'symbol' => $symbol,
            'side' => $order['side'],
            'price' => $price,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $amount - $filled,
            'trades' => null,
            'fee' => array (
                'currency' => null,
                'cost' => $this->safe_float($order, 'order_fee'),
            ),
            'info' => $order,
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $order = $this->privateGetOrdersId (array_merge (array (
            'id' => $id,
        ), $params));
        return $this->parse_order($order);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array ();
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['product_id'] = $market['id'];
        }
        $status = $this->safe_value($params, 'status');
        if ($status) {
            $params = $this->omit ($params, 'status');
            if ($status === 'open') {
                $request['status'] = 'live';
            } else if ($status === 'closed') {
                $request['status'] = 'filled';
            } else if ($status === 'canceled') {
                $request['status'] = 'cancelled';
            }
        }
        if ($limit !== null)
            $request['limit'] = $limit;
        $result = $this->privateGetOrders (array_merge ($request, $params));
        $orders = $result['models'];
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array ( 'status' => 'open' ), $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array ( 'status' => 'closed' ), $params));
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        $headers = array (
            'X-Quoine-API-Version' => $this->version,
            'Content-Type' => 'application/json',
        );
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            if ($method === 'GET') {
                if ($query)
                    $url .= '?' . $this->urlencode ($query);
            } else if ($query) {
                $body = $this->json ($query);
            }
            $nonce = $this->nonce ();
            $request = array (
                'path' => $url,
                'nonce' => $nonce,
                'token_id' => $this->apiKey,
                'iat' => (int) floor ($nonce / 1000), // issued at
            );
            $headers['X-Quoine-Auth'] = $this->jwt ($request, $this->secret);
        }
        $url = $this->urls['api'] . $url;
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response = null) {
        if ($code >= 200 && $code <= 299)
            return;
        $messages = $this->exceptions['messages'];
        if ($code === 401) {
            // expected non-json $response
            if (is_array ($messages) && array_key_exists ($body, $messages))
                throw new $messages[$body] ($this->id . ' ' . $body);
            else
                return;
        }
        if ($response === null)
            if (($body[0] === '{') || ($body[0] === '['))
                $response = json_decode ($body, $as_associative_array = true);
            else
                return;
        $feedback = $this->id . ' ' . $this->json ($response);
        if ($code === 404) {
            // array ( "$message" => "Order not found" )
            $message = $this->safe_string($response, 'message');
            if (is_array ($messages) && array_key_exists ($message, $messages))
                throw new $messages[$message] ($feedback);
        } else if ($code === 422) {
            // array of error $messages is returned in 'user' or 'quantity' property of 'errors' object, e.g.:
            // array ( "$errors" => { "user" => ["not_enough_free_balance"] )}
            // array ( "$errors" => { "quantity" => ["less_than_order_size"] )}
            if (is_array ($response) && array_key_exists ('errors', $response)) {
                $errors = $response['errors'];
                $errorTypes = ['user', 'quantity', 'price'];
                for ($i = 0; $i < count ($errorTypes); $i++) {
                    $errorType = $errorTypes[$i];
                    if (is_array ($errors) && array_key_exists ($errorType, $errors)) {
                        $errorMessages = $errors[$errorType];
                        for ($j = 0; $j < count ($errorMessages); $j++) {
                            $message = $errorMessages[$j];
                            if (is_array ($messages[$errorType]) && array_key_exists ($message, $messages[$errorType]))
                                throw new $messages[$errorType][$message] ($feedback);
                        }
                    }
                }
            }
        }
    }
}
