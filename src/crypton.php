<?php

namespace ccxt;

use Exception as Exception; // a common import

class crypton extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'crypton',
            'name' => 'Crypton',
            'countries' => array ( 'EU' ),
            'rateLimit' => 500,
            'version' => '1',
            'has' => array (
                'fetchDepositAddress' => true,
                'fetchMyTrades' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchTicker' => false,
                'fetchTickers' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/41334251-905b5a78-6eed-11e8-91b9-f3aa435078a1.jpg',
                'api' => 'https://api.cryptonbtc.com',
                'www' => 'https://cryptonbtc.com',
                'doc' => 'https://cryptonbtc.docs.apiary.io/',
                'fees' => 'https://help.cryptonbtc.com/hc/en-us/articles/360004089872-Fees',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currencies',
                        'markets',
                        'markets/{id}',
                        'markets/{id}/orderbook',
                        'markets/{id}/trades',
                        'tickers',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'balances',
                        'orders',
                        'orders/{id}',
                        'fills',
                        'deposit_address/{currency}',
                        'deposits',
                    ),
                    'post' => array (
                        'orders',
                    ),
                    'delete' => array (
                        'orders/{id}',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.0020,
                    'taker' => 0.0020,
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetMarkets ();
        $markets = $response['result'];
        $result = array ();
        $keys = is_array ($markets) ? array_keys ($markets) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $id = $keys[$i];
            $market = $markets[$id];
            $baseId = $market['base'];
            $quoteId = $market['quote'];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => 8,
                'price' => $this->precision_from_string($this->safe_string($market, 'priceStep')),
            );
            $active = $market['enabled'];
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => $active,
                'info' => $market,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($market, 'minSize'),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => $this->safe_float($market, 'priceStep'),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privateGetBalances ($params);
        $result = array ( 'info' => $balances );
        $keys = is_array ($balances) ? array_keys ($balances) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $id = $keys[$i];
            $currency = $this->common_currency_code($id);
            $account = $this->account ();
            $balance = $balances[$id];
            $total = floatval ($balance['total']);
            $free = floatval ($balance['free']);
            $used = floatval ($balance['locked']);
            $account['total'] = $total;
            $account['free'] = $free;
            $account['used'] = $used;
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetMarketsIdOrderbook (array_merge (array (
            'id' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function parse_ticker ($ticker, $market = null) {
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $last = $this->safe_float($ticker, 'last');
        $relativeChange = $this->safe_float($ticker, 'change24h', 0.0);
        return array (
            'symbol' => $symbol,
            'timestamp' => null,
            'datetime' => null,
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => $relativeChange * 100,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume24h'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetTickers ($params);
        $keys = is_array ($tickers) ? array_keys ($tickers) : array ();
        $result = array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $id = $keys[$i];
            $ticker = $tickers[$id];
            $market = null;
            $symbol = $id;
            if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            } else {
                $symbol = $this->parse_symbol ($id);
            }
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($trade['time']);
        $symbol = null;
        if (is_array ($trade) && array_key_exists ('market', $trade)) {
            $marketId = $trade['market'];
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                $symbol = $this->parse_symbol ($marketId);
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $fee = null;
        if (is_array ($trade) && array_key_exists ('fee', $trade)) {
            $fee = array (
                'cost' => $this->safe_float($trade, 'fee'),
                'currency' => $this->common_currency_code($trade['feeCurrency']),
            );
        }
        return array (
            'id' => (string) $trade['id'],
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $trade['side'],
            'price' => $this->safe_float($trade, 'price'),
            'amount' => $this->safe_float($trade, 'size'),
            'order' => $this->safe_string($trade, 'orderId'),
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'id' => $market['id'],
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        $response = $this->publicGetMarketsIdTrades (array_merge ($request, $params));
        return $this->parse_trades($response['result'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array ();
        if ($limit !== null)
            $request['limit'] = $limit;
        $response = $this->privateGetFills (array_merge ($request, $params));
        $trades = $this->parse_trades($response['result'], $market, $since, $limit);
        return $this->filter_by_symbol($trades, $symbol);
    }

    public function parse_order ($order, $market = null) {
        $id = (string) $order['id'];
        $status = $order['status'];
        $side = $order['side'];
        $type = $order['type'];
        $symbol = null;
        $marketId = $order['market'];
        if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
            $market = $this->markets_by_id[$marketId];
            $symbol = $market['symbol'];
        } else {
            $symbol = $this->parse_symbol ($marketId);
        }
        $timestamp = $this->parse8601 ($order['createdAt']);
        $fee = null;
        if (is_array ($order) && array_key_exists ('fee', $order)) {
            $fee = array (
                'cost' => floatval ($order['fee']),
                'currency' => $this->common_currency_code($order['feeCurrency']),
            );
        }
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'size');
        $filled = $this->safe_float($order, 'filledSize');
        $remaining = $amount - $filled;
        $cost = $filled * $price;
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'average' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
        return $result;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => $id,
        );
        $response = $this->privateGetOrdersId (array_merge ($request, $params));
        return $this->parse_order($response['result']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        $market = null;
        if ($symbol !== null) {
            $request['market'] = $this->market_id($symbol);
        }
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response['result'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'market' => $this->market_id($symbol),
            'side' => $side,
            'type' => $type,
            'size' => $this->amount_to_precision($symbol, $amount),
            'price' => $this->price_to_precision($symbol, $price),
        );
        $response = $this->privatePostOrders (array_merge ($order, $params));
        return $this->parse_order($response['result']);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => $id,
        );
        $response = $this->privateDeleteOrdersId (array_merge ($request, $params));
        return $this->parse_order($response['result']);
    }

    public function parse_symbol ($id) {
        list ($base, $quote) = explode ('-', $id);
        $base = $this->common_currency_code($base);
        $quote = $this->common_currency_code($quote);
        return $base . '/' . $quote;
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privateGetDepositAddressCurrency (array_merge (array (
            'currency' => $currency['id'],
        ), $params));
        $result = $response['result'];
        $address = $this->safe_string($result, 'address');
        $tag = $this->safe_string($result, 'tag');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($method === 'GET') {
            if ($query)
                $request .= '?' . $this->urlencode ($query);
        }
        $url = $this->urls['api'] . $request;
        if ($api === 'private') {
            $this->check_required_credentials();
            $timestamp = (string) $this->milliseconds ();
            $payload = '';
            if ($method !== 'GET') {
                if ($query) {
                    $body = $this->json ($query);
                    $payload = $body;
                }
            }
            $what = $timestamp . $method . $request . $payload;
            $signature = $this->hmac ($this->encode ($what), $this->encode ($this->secret), 'sha256');
            $headers = array (
                'CRYPTON-APIKEY' => $this->apiKey,
                'CRYPTON-SIGNATURE' => $signature,
                'CRYPTON-TIMESTAMP' => $timestamp,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if ($body[0] === '{') {
            $response = json_decode ($body, $as_associative_array = true);
            $success = $this->safe_value($response, 'success');
            if (!$success) {
                throw new ExchangeError ($this->id . ' ' . $body);
            }
        }
    }
}