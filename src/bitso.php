<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitso extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitso',
            'name' => 'Bitso',
            'countries' => 'MX', // Mexico
            'rateLimit' => 2000, // 30 requests per minute
            'version' => 'v3',
            'has' => array (
                'CORS' => true,
                'fetchMyTrades' => true,
                'fetchOpenOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766335-715ce7aa-5ed5-11e7-88a8-173a27bb30fe.jpg',
                'api' => 'https://api.bitso.com',
                'www' => 'https://bitso.com',
                'doc' => 'https://bitso.com/api_info',
                'fees' => 'https://bitso.com/fees?l=es',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'available_books',
                        'ticker',
                        'order_book',
                        'trades',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'account_status',
                        'balance',
                        'fees',
                        'fundings',
                        'fundings/{fid}',
                        'funding_destination',
                        'kyc_documents',
                        'ledger',
                        'ledger/trades',
                        'ledger/fees',
                        'ledger/fundings',
                        'ledger/withdrawals',
                        'mx_bank_codes',
                        'open_orders',
                        'order_trades/{oid}',
                        'orders/{oid}',
                        'user_trades',
                        'user_trades/{tid}',
                        'withdrawals/',
                        'withdrawals/{wid}',
                    ),
                    'post' => array (
                        'bitcoin_withdrawal',
                        'debit_card_withdrawal',
                        'ether_withdrawal',
                        'ripple_withdrawal',
                        'bcash_withdrawal',
                        'litecoin_withdrawal',
                        'orders',
                        'phone_number',
                        'phone_verification',
                        'phone_withdrawal',
                        'spei_withdrawal',
                        'ripple_withdrawal',
                        'bcash_withdrawal',
                        'litecoin_withdrawal',
                    ),
                    'delete' => array (
                        'orders/{oid}',
                        'orders/all',
                    ),
                ),
            ),
            'exceptions' => array (
                '0201' => '\\ccxt\\AuthenticationError', // Invalid Nonce or Invalid Credentials
                '104' => '\\ccxt\\InvalidNonce', // Cannot perform request - nonce must be higher than 1520307203724237
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetAvailableBooks ();
        $result = array ();
        for ($i = 0; $i < count ($markets['payload']); $i++) {
            $market = $markets['payload'][$i];
            $id = $market['book'];
            $symbol = str_replace ('_', '/', strtoupper ($id));
            list ($base, $quote) = explode ('/', $symbol);
            $limits = array (
                'amount' => array (
                    'min' => $this->safe_float($market, 'minimum_amount'),
                    'max' => $this->safe_float($market, 'maximum_amount'),
                ),
                'price' => array (
                    'min' => $this->safe_float($market, 'minimum_price'),
                    'max' => $this->safe_float($market, 'maximum_price'),
                ),
                'cost' => array (
                    'min' => $this->safe_float($market, 'minimum_value'),
                    'max' => $this->safe_float($market, 'maximum_value'),
                ),
            );
            $precision = array (
                'amount' => $this->precision_from_string($market['minimum_amount']),
                'price' => $this->precision_from_string($market['minimum_price']),
            );
            $lot = $limits['amount']['min'];
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'info' => $market,
                'lot' => $lot,
                'limits' => $limits,
                'precision' => $precision,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetBalance ();
        $balances = $response['payload']['balances'];
        $result = array ( 'info' => $response );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currency = strtoupper ($balance['currency']);
            $account = array (
                'free' => floatval ($balance['available']),
                'used' => floatval ($balance['locked']),
                'total' => floatval ($balance['total']),
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetOrderBook (array_merge (array (
            'book' => $this->market_id($symbol),
        ), $params));
        $orderbook = $response['payload'];
        $timestamp = $this->parse8601 ($orderbook['updated_at']);
        return $this->parse_order_book($orderbook, $timestamp, 'bids', 'asks', 'price', 'amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetTicker (array_merge (array (
            'book' => $this->market_id($symbol),
        ), $params));
        $ticker = $response['payload'];
        $timestamp = $this->parse8601 ($ticker['created_at']);
        $vwap = $this->safe_float($ticker, 'vwap');
        $baseVolume = $this->safe_float($ticker, 'volume');
        $quoteVolume = $baseVolume * $vwap;
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

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($trade['created_at']);
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($trade, 'book');
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                $market = $this->markets_by_id[$marketId];
        }
        if ($market !== null)
            $symbol = $market['symbol'];
        $side = $this->safe_string($trade, 'side');
        if ($side === null)
            $side = $this->safe_string($trade, 'maker_side');
        $amount = $this->safe_float($trade, 'amount');
        if ($amount === null)
            $amount = $this->safe_float($trade, 'major');
        if ($amount !== null)
            $amount = abs ($amount);
        $fee = null;
        $feeCost = $this->safe_float($trade, 'fees_amount');
        if ($feeCost !== null) {
            $feeCurrency = $this->safe_string($trade, 'fees_currency');
            if ($feeCurrency !== null) {
                if (is_array ($this->currencies_by_id) && array_key_exists ($feeCurrency, $this->currencies_by_id))
                    $feeCurrency = $this->currencies_by_id[$feeCurrency]['code'];
            }
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        $cost = $this->safe_float($trade, 'minor');
        if ($cost !== null)
            $cost = abs ($cost);
        $price = $this->safe_float($trade, 'price');
        $orderId = $this->safe_string($trade, 'oid');
        return array (
            'id' => (string) $trade['tid'],
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetTrades (array_merge (array (
            'book' => $market['id'],
        ), $params));
        return $this->parse_trades($response['payload'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = 25, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        // the don't support fetching trades starting from a date yet
        // use the `marker` extra param for that
        // this is not a typo, the variable name is 'marker' (don't confuse with 'market')
        $markerInParams = (is_array ($params) && array_key_exists ('marker', $params));
        // warn the user with an exception if the user wants to filter
        // starting from $since timestamp, but does not set the trade id with an extra 'marker' param
        if (($since !== null) && !$markerInParams)
            throw ExchangeError ($this->id . ' fetchMyTrades does not support fetching trades starting from a timestamp with the `$since` argument, use the `marker` extra param to filter starting from an integer trade id');
        // convert it to an integer unconditionally
        if ($markerInParams)
            $params = array_merge ($params, array (
                'marker' => intval ($params['marker']),
            ));
        $request = array (
            'book' => $market['id'],
            'limit' => $limit, // default = 25, max = 100
            // 'sort' => 'desc', // default = desc
            // 'marker' => id, // integer id to start from
        );
        $response = $this->privateGetUserTrades (array_merge ($request, $params));
        return $this->parse_trades($response['payload'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'book' => $this->market_id($symbol),
            'side' => $side,
            'type' => $type,
            'major' => $this->amount_to_precision($symbol, $amount),
        );
        if ($type === 'limit')
            $order['price'] = $this->price_to_precision($symbol, $price);
        $response = $this->privatePostOrders (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => $response['payload']['oid'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        return $this->privateDeleteOrdersOid (array ( 'oid' => $id ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'partial-fill' => 'open', // this is a common substitution in ccxt
            'completed' => 'closed',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function parse_order ($order, $market = null) {
        $side = $order['side'];
        $status = $this->parse_order_status($order['status']);
        $symbol = null;
        if ($market === null) {
            $marketId = $order['book'];
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                $market = $this->markets_by_id[$marketId];
        }
        if ($market)
            $symbol = $market['symbol'];
        $orderType = $order['type'];
        $timestamp = $this->parse8601 ($order['created_at']);
        $amount = $this->safe_float($order, 'original_amount');
        $remaining = $this->safe_float($order, 'unfilled_amount');
        $filled = $amount - $remaining;
        $result = array (
            'info' => $order,
            'id' => $order['oid'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $orderType,
            'side' => $side,
            'price' => $this->safe_float($order, 'price'),
            'amount' => $amount,
            'cost' => null,
            'remaining' => $remaining,
            'filled' => $filled,
            'status' => $status,
            'fee' => null,
        );
        return $result;
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = 25, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        // the don't support fetching trades starting from a date yet
        // use the `marker` extra param for that
        // this is not a typo, the variable name is 'marker' (don't confuse with 'market')
        $markerInParams = (is_array ($params) && array_key_exists ('marker', $params));
        // warn the user with an exception if the user wants to filter
        // starting from $since timestamp, but does not set the trade id with an extra 'marker' param
        if (($since !== null) && !$markerInParams)
            throw ExchangeError ($this->id . ' fetchOpenOrders does not support fetching $orders starting from a timestamp with the `$since` argument, use the `marker` extra param to filter starting from an integer trade id');
        // convert it to an integer unconditionally
        if ($markerInParams)
            $params = array_merge ($params, array (
                'marker' => intval ($params['marker']),
            ));
        $request = array (
            'book' => $market['id'],
            'limit' => $limit, // default = 25, max = 100
            // 'sort' => 'desc', // default = desc
            // 'marker' => id, // integer id to start from
        );
        $response = $this->privateGetOpenOrders (array_merge ($request, $params));
        $orders = $this->parse_orders($response['payload'], $market, $since, $limit);
        return $orders;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateGetOrdersOid (array (
            'oid' => $id,
        ));
        $numOrders = is_array ($response['payload']) ? count ($response['payload']) : 0;
        if (!gettype ($response['payload']) === 'array' && count (array_filter (array_keys ($response['payload']), 'is_string')) == 0 || ($numOrders !== 1)) {
            throw new OrderNotFound ($this->id . ' => The order ' . $id . ' not found.');
        }
        return $this->parse_order($response['payload'][0], $market);
    }

    public function fetch_order_trades ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateGetOrderTradesOid (array (
            'oid' => $id,
        ));
        return $this->parse_trades($response['payload'], $market);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'fund_currency' => $currency['id'],
        );
        $response = $this->privateGetFundingDestination (array_merge ($request, $params));
        $address = $this->safe_string($response['payload'], 'account_identifier');
        $tag = null;
        if ($code === 'XRP') {
            $parts = explode ('?dt=', $address);
            $address = $parts[0];
            $tag = $parts[1];
        }
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'status' => 'ok',
            'info' => $response,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $methods = array (
            'BTC' => 'Bitcoin',
            'ETH' => 'Ether',
            'XRP' => 'Ripple',
            'BCH' => 'Bcash',
            'LTC' => 'Litecoin',
        );
        $method = (is_array ($methods) && array_key_exists ($code, $methods)) ? $methods[$code] : null;
        if ($method === null) {
            throw new ExchangeError ($this->id . ' not valid withdraw coin => ' . $code);
        }
        $request = array (
            'amount' => $amount,
            'address' => $address,
            'destination_tag' => $tag,
        );
        $classMethod = 'privatePost' . $method . 'Withdrawal';
        $response = $this->$classMethod (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $this->safe_string($response['payload'], 'wid'),
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $endpoint = '/' . $this->version . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($method === 'GET') {
            if ($query)
                $endpoint .= '?' . $this->urlencode ($query);
        }
        $url = $this->urls['api'] . $endpoint;
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $request = implode ('', array ($nonce, $method, $endpoint));
            if ($method !== 'GET') {
                if ($query) {
                    $body = $this->json ($query);
                    $request .= $body;
                }
            }
            $signature = $this->hmac ($this->encode ($request), $this->encode ($this->secret));
            $auth = $this->apiKey . ':' . $nonce . ':' . $signature;
            $headers = array (
                'Authorization' => 'Bitso ' . $auth,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default $error handler
        if (strlen ($body) < 2)
            return; // fallback to default $error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            if (is_array ($response) && array_key_exists ('success', $response)) {
                //
                //     array ("$success":false,"$error":{"$code":104,"message":"Cannot perform request - nonce must be higher than 1520307203724237")}
                //
                $success = $this->safe_value($response, 'success', false);
                if (gettype ($success) === 'string') {
                    if (($success === 'true') || ($success === '1'))
                        $success = true;
                    else
                        $success = false;
                }
                if (!$success) {
                    $feedback = $this->id . ' ' . $this->json ($response);
                    $error = $this->safe_value($response, 'error');
                    if ($error === null)
                        throw new ExchangeError ($feedback);
                    $code = $this->safe_string($error, 'code');
                    $exceptions = $this->exceptions;
                    if (is_array ($exceptions) && array_key_exists ($code, $exceptions)) {
                        throw new $exceptions[$code] ($feedback);
                    } else {
                        throw new ExchangeError ($feedback);
                    }
                }
            }
        }
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array ($response) && array_key_exists ('success', $response))
            if ($response['success'])
                return $response;
        throw new ExchangeError ($this->id . ' ' . $this->json ($response));
    }
}
