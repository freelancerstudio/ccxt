<?php

namespace ccxt;

class gemini extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'gemini',
            'name' => 'Gemini',
            'countries' => 'US',
            'rateLimit' => 1500, // 200 for private API
            'version' => 'v1',
            'has' => array (
                'fetchDepositAddress' => false,
                'CORS' => false,
                'fetchBidsAsks' => false,
                'fetchTickers' => false,
                'fetchOHLCV' => false,
                'fetchMyTrades' => true,
                'fetchOrder' => false,
                'fetchOrders' => false,
                'fetchOpenOrders' => false,
                'fetchClosedOrders' => false,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27816857-ce7be644-6096-11e7-82d6-3c257263229c.jpg',
                'api' => 'https://api.gemini.com',
                'www' => 'https://gemini.com',
                'doc' => array (
                    'https://docs.gemini.com/rest-api',
                    'https://docs.sandbox.gemini.com',
                ),
                'test' => 'https://api.sandbox.gemini.com',
                'fees' => array (
                    'https://gemini.com/fee-schedule/',
                    'https://gemini.com/transfer-fees/',
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'symbols',
                        'pubticker/{symbol}',
                        'book/{symbol}',
                        'trades/{symbol}',
                        'auction/{symbol}',
                        'auction/{symbol}/history',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'order/new',
                        'order/cancel',
                        'order/cancel/session',
                        'order/cancel/all',
                        'order/status',
                        'orders',
                        'mytrades',
                        'tradevolume',
                        'balances',
                        'deposit/{currency}/newAddress',
                        'withdraw/{currency}',
                        'heartbeat',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'taker' => 0.0025,
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetSymbols ();
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $id = $markets[$p];
            $market = $id;
            $uppercase = strtoupper ($market);
            $base = mb_substr ($uppercase, 0, 3);
            $quote = mb_substr ($uppercase, 3, 6);
            $symbol = $base . '/' . $quote;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetBookSymbol (array_merge (array (
            'symbol' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook, null, 'bids', 'asks', 'price', 'amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetPubtickerSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        $timestamp = $ticker['volume']['timestamp'];
        $baseVolume = $market['base'];
        $quoteVolume = $market['quote'];
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => floatval ($ticker['bid']),
            'ask' => floatval ($ticker['ask']),
            'vwap' => null,
            'open' => null,
            'close' => null,
            'first' => null,
            'last' => floatval ($ticker['last']),
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => floatval ($ticker['volume'][$baseVolume]),
            'quoteVolume' => floatval ($ticker['volume'][$quoteVolume]),
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market) {
        $timestamp = $trade['timestampms'];
        $order = null;
        if (is_array ($trade) && array_key_exists ('orderId', $trade))
            $order = (string) $trade['orderId'];
        $fee = $this->safe_float($trade, 'fee_amount');
        if ($fee !== null) {
            $currency = $this->safe_string($trade, 'fee_currency');
            if ($currency !== null) {
                if (is_array ($this->currencies_by_id) && array_key_exists ($currency, $this->currencies_by_id))
                    $currency = $this->currencies_by_id[$currency]['code'];
                $currency = $this->common_currency_code($currency);
            }
            $fee = array (
                'cost' => floatval ($trade['fee_amount']),
                'currency' => $currency,
            );
        }
        $price = floatval ($trade['price']);
        $amount = floatval ($trade['amount']);
        return array (
            'id' => (string) $trade['tid'],
            'order' => $order,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => $trade['type'],
            'price' => $price,
            'cost' => $price * $amount,
            'amount' => $amount,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetTradesSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privatePostBalances ();
        $result = array ( 'info' => $balances );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currency = $balance['currency'];
            $account = array (
                'free' => floatval ($balance['available']),
                'used' => 0.0,
                'total' => floatval ($balance['amount']),
            );
            $account['used'] = $account['total'] - $account['free'];
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        if ($type === 'market')
            throw new ExchangeError ($this->id . ' allows limit orders only');
        $nonce = $this->nonce ();
        $order = array (
            'client_order_id' => (string) $nonce,
            'symbol' => $this->market_id($symbol),
            'amount' => (string) $amount,
            'price' => (string) $price,
            'side' => $side,
            'type' => 'exchange limit', // gemini allows limit orders only
        );
        $response = $this->privatePostOrderNew (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => $response['order_id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        return $this->privatePostCancelOrder (array ( 'order_id' => $id ));
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchMyTrades requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        $response = $this->privatePostMytrades (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privatePostWithdrawCurrency (array_merge (array (
            'currency' => $currency['id'],
            'amount' => $amount,
            'address' => $address,
        ), $params));
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'txHash'),
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = '/' . $this->version . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $request = array_merge (array (
                'request' => $url,
                'nonce' => $nonce,
            ), $query);
            $payload = $this->json ($request);
            $payload = base64_encode ($this->encode ($payload));
            $signature = $this->hmac ($payload, $this->encode ($this->secret), 'sha384');
            $headers = array (
                'Content-Type' => 'text/plain',
                'X-GEMINI-APIKEY' => $this->apiKey,
                'X-GEMINI-PAYLOAD' => $this->decode ($payload),
                'X-GEMINI-SIGNATURE' => $signature,
            );
        }
        $url = $this->urls['api'] . $url;
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array ($response) && array_key_exists ('result', $response))
            if ($response['result'] === 'error')
                throw new ExchangeError ($this->id . ' ' . $this->json ($response));
        return $response;
    }
}
