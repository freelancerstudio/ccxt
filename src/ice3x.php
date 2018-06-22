<?php

namespace ccxt;

use Exception as Exception; // a common import

class ice3x extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'ice3x',
            'name' => 'ICE3X',
            'countries' => 'ZA', // South Africa
            'rateLimit' => 1000,
            'has' => array (
                'fetchCurrencies' => true,
                'fetchTickers' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchMyTrades' => true,
                'fetchDepositAddress' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/38012176-11616c32-3269-11e8-9f05-e65cf885bb15.jpg',
                'api' => 'https://ice3x.com/api/v1',
                'www' => array (
                    'https://ice3x.com',
                    'https://ice3x.co.za',
                ),
                'doc' => 'https://ice3x.co.za/ice-cubed-bitcoin-exchange-api-documentation-1-june-2017',
                'fees' => array (
                    'https://help.ice3.com/support/solutions/articles/11000033293-trading-fees',
                    'https://help.ice3.com/support/solutions/articles/11000033288-fees-explained',
                    'https://help.ice3.com/support/solutions/articles/11000008131-what-are-your-fiat-deposit-and-withdrawal-fees-',
                    'https://help.ice3.com/support/solutions/articles/11000033289-deposit-fees',
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currency/list',
                        'currency/info',
                        'pair/list',
                        'pair/info',
                        'stats/marketdepthfull',
                        'stats/marketdepthbtcav',
                        'stats/marketdepth',
                        'orderbook/info',
                        'trade/list',
                        'trade/info',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balance/list',
                        'balance/info',
                        'order/new',
                        'order/cancel',
                        'order/list',
                        'order/info',
                        'trade/list',
                        'trade/info',
                        'transaction/list',
                        'transaction/info',
                        'invoice/list',
                        'invoice/info',
                        'invoice/pdf',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.01,
                    'taker' => 0.01,
                ),
            ),
            'precision' => array (
                'amount' => 8,
                'price' => 8,
            ),
        ));
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetCurrencyList ($params);
        $currencies = $response['response']['entities'];
        $precision = $this->precision['amount'];
        $result = array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $id = $currency['currency_id'];
            $code = $this->common_currency_code(strtoupper ($currency['iso']));
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'name' => $currency['name'],
                'active' => true,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => null,
                        'max' => pow (10, $precision),
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision),
                        'max' => pow (10, $precision),
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $currency,
            );
        }
        return $result;
    }

    public function fetch_markets () {
        if (!$this->currencies) {
            $this->currencies = $this->fetch_currencies();
        }
        $this->currencies_by_id = $this->index_by($this->currencies, 'id');
        $response = $this->publicGetPairList ();
        $markets = $response['response']['entities'];
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['pair_id'];
            $baseId = (string) $market['currency_id_from'];
            $quoteId = (string) $market['currency_id_to'];
            $baseCurrency = $this->currencies_by_id[$baseId];
            $quoteCurrency = $this->currencies_by_id[$quoteId];
            $base = $this->common_currency_code($baseCurrency['code']);
            $quote = $this->common_currency_code($quoteCurrency['code']);
            $symbol = $base . '/' . $quote;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'lot' => null,
                'info' => $market,
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = $market['symbol'];
        $last = $this->safe_float($ticker, 'last_price');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'max'),
            'low' => $this->safe_float($ticker, 'min'),
            'bid' => $this->safe_float($ticker, 'max_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'min_ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => $this->safe_float($ticker, 'avg'),
            'baseVolume' => null,
            'quoteVolume' => $this->safe_float($ticker, 'vol'),
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetStatsMarketdepthfull (array_merge (array (
            'pair_id' => $market['id'],
        ), $params));
        return $this->parse_ticker($response['response']['entity'], $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetStatsMarketdepthfull ($params);
        $tickers = $response['response']['entities'];
        $result = array ();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $tickers[$i];
            $market = $this->marketsById[$ticker['pair_id']];
            $symbol = $market['symbol'];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetOrderbookInfo (array_merge (array (
            'pair_id' => $this->market_id($symbol),
        ), $params));
        $orderbook = $response['response']['entities'];
        return $this->parse_order_book($orderbook, null, 'bids', 'asks', 'price', 'amount');
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = intval ($trade['created']) * 1000;
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'volume');
        $symbol = $market['symbol'];
        $cost = floatval ($this->cost_to_precision($symbol, $price * $amount));
        $fee = $this->safe_float($trade, 'fee');
        if ($fee) {
            $fee = array (
                'cost' => $fee,
                'currency' => $market['quote'],
            );
        }
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'trade_id'),
            'order' => null,
            'type' => 'limit',
            'side' => $trade['type'],
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetTradeList (array_merge (array (
            'pair_id' => $market['id'],
        ), $params));
        $trades = $response['response']['entities'];
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostBalanceList ($params);
        $result = array ( 'info' => $response );
        $balances = $response['response']['entities'];
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $id = $balance['currency_id'];
            if (is_array ($this->currencies_by_id) && array_key_exists ($id, $this->currencies_by_id)) {
                $currency = $this->currencies_by_id[$id];
                $code = $currency['code'];
                $result[$code] = array (
                    'free' => 0.0,
                    'used' => 0.0,
                    'total' => floatval ($balance['balance']),
                );
            }
        }
        return $this->parse_balance($result);
    }

    public function parse_order ($order, $market = null) {
        $pairId = $this->safe_integer($order, 'pair_id');
        $symbol = null;
        if ($pairId && !$market && (is_array ($this->marketsById) && array_key_exists ($pairId, $this->marketsById))) {
            $market = $this->marketsById[$pairId];
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer($order, 'created') * 1000;
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'volume');
        $status = $this->safe_integer($order, 'active');
        $remaining = $this->safe_float($order, 'remaining');
        $filled = null;
        if ($status === 1) {
            $status = 'open';
        } else {
            $status = 'closed';
            $remaining = 0;
            $filled = $amount;
        }
        $fee = $this->safe_float($order, 'fee');
        if ($fee) {
            $fee = array ( 'cost' => $fee );
            if ($market)
                $fee['currency'] = $market['quote'];
        }
        return array (
            'id' => $this->safe_string($order, 'order_id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $order['type'],
            'price' => $price,
            'cost' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => $fee,
            'info' => $order,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostOrderNew (array_merge (array (
            'pair_id' => $market['id'],
            'type' => $side,
            'amount' => $amount,
            'price' => $price,
        ), $params));
        $order = $this->parse_order(array (
            'order_id' => $response['response']['entity']['order_id'],
            'created' => $this->seconds (),
            'active' => 1,
            'type' => $side,
            'price' => $price,
            'volume' => $amount,
            'remaining' => $amount,
            'info' => $response,
        ), $market);
        $id = $order['id'];
        $this->orders[$id] = $order;
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $response = $this->privatePostOrderCancel (array_merge (array (
            'order_id' => $id,
        ), $params));
        return $response;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePostOrderInfo (array_merge (array (
            'order _id' => $id,
        ), $params));
        return $this->parse_order($response['response']['entity']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePostOrderList ();
        $orders = $response['response']['entities'];
        return $this->parse_orders($orders, null, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair_id' => $market['id'],
        );
        if ($limit)
            $request['items_per_page'] = $limit;
        if ($since)
            $request['date_from'] = intval ($since / 1000);
        $response = $this->privatePostTradeList (array_merge ($request, $params));
        $trades = $response['response']['entities'];
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privatePostBalanceInfo (array_merge (array (
            'currency_id' => $currency['id'],
        ), $params));
        $balance = $response['response']['entity'];
        $address = $this->safe_string($balance, 'address');
        $status = $address ? 'ok' : 'none';
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'status' => $status,
            'info' => $response,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $path;
        if ($api === 'public') {
            $params = $this->urlencode ($params);
            if (strlen ($params))
                $url .= '?' . $params;
        } else {
            $this->check_required_credentials();
            $body = $this->urlencode (array_merge (array (
                'nonce' => $this->nonce (),
            ), $params));
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Key' => $this->apiKey,
                'Sign' => $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512'),
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        $errors = $this->safe_value($response, 'errors');
        $data = $this->safe_value($response, 'response');
        if ($errors || !$data) {
            $authErrorKeys = array ( 'Key', 'user_id', 'Sign' );
            for ($i = 0; $i < count ($authErrorKeys); $i++) {
                $errorKey = $authErrorKeys[$i];
                $errorMessage = $this->safe_string($errors, $errorKey);
                if (!$errorMessage)
                    continue;
                if ($errorKey === 'user_id' && mb_strpos ($errorMessage, 'authorization') < 0)
                    continue;
                throw new AuthenticationError ($errorMessage);
            }
            throw new ExchangeError ($this->json ($errors));
        }
        return $response;
    }
}
