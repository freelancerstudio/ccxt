<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitbank extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitbank',
            'name' => 'bitbank',
            'countries' => array ( 'JP' ),
            'version' => 'v1',
            'has' => array (
                'fetchOHLCV' => true,
                'fetchOpenOrders' => true,
                'fetchMyTrades' => true,
                'fetchDepositAddress' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '4h' => '4hour',
                '8h' => '8hour',
                '12h' => '12hour',
                '1d' => '1day',
                '1w' => '1week',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/37808081-b87f2d9c-2e59-11e8-894d-c1900b7584fe.jpg',
                'api' => array (
                    'public' => 'https://public.bitbank.cc',
                    'private' => 'https://api.bitbank.cc',
                ),
                'www' => 'https://bitbank.cc/',
                'doc' => 'https://docs.bitbank.cc/',
                'fees' => 'https://bitbank.cc/docs/fees/',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        '{pair}/ticker',
                        '{pair}/depth',
                        '{pair}/transactions',
                        '{pair}/transactions/{yyyymmdd}',
                        '{pair}/candlestick/{candletype}/{yyyymmdd}',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'user/assets',
                        'user/spot/order',
                        'user/spot/active_orders',
                        'user/spot/trade_history',
                        'user/withdrawal_account',
                    ),
                    'post' => array (
                        'user/spot/order',
                        'user/spot/cancel_order',
                        'user/spot/cancel_orders',
                        'user/spot/orders_info',
                        'user/request_withdrawal',
                    ),
                ),
            ),
            'markets' => array (
                'BCH/BTC' => array ( 'id' => 'bcc_btc', 'symbol' => 'BCH/BTC', 'base' => 'BCH', 'quote' => 'BTC', 'baseId' => 'bcc', 'quoteId' => 'btc' ),
                'BCH/JPY' => array ( 'id' => 'bcc_jpy', 'symbol' => 'BCH/JPY', 'base' => 'BCH', 'quote' => 'JPY', 'baseId' => 'bcc', 'quoteId' => 'jpy' ),
                'MONA/BTC' => array ( 'id' => 'mona_btc', 'symbol' => 'MONA/BTC', 'base' => 'MONA', 'quote' => 'BTC', 'baseId' => 'mona', 'quoteId' => 'btc' ),
                'MONA/JPY' => array ( 'id' => 'mona_jpy', 'symbol' => 'MONA/JPY', 'base' => 'MONA', 'quote' => 'JPY', 'baseId' => 'mona', 'quoteId' => 'jpy' ),
                'ETH/BTC' => array ( 'id' => 'eth_btc', 'symbol' => 'ETH/BTC', 'base' => 'ETH', 'quote' => 'BTC', 'baseId' => 'eth', 'quoteId' => 'btc' ),
                'LTC/BTC' => array ( 'id' => 'ltc_btc', 'symbol' => 'LTC/BTC', 'base' => 'LTC', 'quote' => 'BTC', 'baseId' => 'ltc', 'quoteId' => 'btc' ),
                'XRP/JPY' => array ( 'id' => 'xrp_jpy', 'symbol' => 'XRP/JPY', 'base' => 'XRP', 'quote' => 'JPY', 'baseId' => 'xrp', 'quoteId' => 'jpy' ),
                'BTC/JPY' => array ( 'id' => 'btc_jpy', 'symbol' => 'BTC/JPY', 'base' => 'BTC', 'quote' => 'JPY', 'baseId' => 'btc', 'quoteId' => 'jpy' ),
            ),
            'fees' => array (
                'trading' => array (
                    // only temporarily
                    'maker' => 0.0,
                    'taker' => 0.0,
                ),
                'funding' => array (
                    'withdraw' => array (
                        // 'JPY' => amount => amount > 30000 ? 756 : 540,
                        'BTC' => 0.001,
                        'LTC' => 0.001,
                        'XRP' => 0.15,
                        'ETH' => 0.0005,
                        'MONA' => 0.001,
                        'BCC' => 0.001,
                    ),
                ),
            ),
            'precision' => array (
                'price' => 8,
                'amount' => 8,
            ),
            'exceptions' => array (
                '20001' => '\\ccxt\\AuthenticationError',
                '20002' => '\\ccxt\\AuthenticationError',
                '20003' => '\\ccxt\\AuthenticationError',
                '20005' => '\\ccxt\\AuthenticationError',
                '20004' => '\\ccxt\\InvalidNonce',
                '40020' => '\\ccxt\\InvalidOrder',
                '40021' => '\\ccxt\\InvalidOrder',
                '40025' => '\\ccxt\\ExchangeError',
                '40013' => '\\ccxt\\OrderNotFound',
                '40014' => '\\ccxt\\OrderNotFound',
                '50008' => '\\ccxt\\PermissionDenied',
                '50009' => '\\ccxt\\OrderNotFound',
                '50010' => '\\ccxt\\OrderNotFound',
                '60001' => '\\ccxt\\InsufficientFunds',
                '60005' => '\\ccxt\\InvalidOrder',
            ),
        ));
    }

    public function parse_ticker ($ticker, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = $ticker['timestamp'];
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
        $response = $this->publicGetPairTicker (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        return $this->parse_ticker($response['data'], $market);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetPairDepth (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        $orderbook = $response['data'];
        return $this->parse_order_book($orderbook, $orderbook['timestamp']);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $trade['executed_at'];
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $symbol = $market['symbol'];
        $cost = $this->cost_to_precision($symbol, $price * $amount);
        $id = $this->safe_string($trade, 'transaction_id');
        if (!$id) {
            $id = $this->safe_string($trade, 'trade_id');
        }
        $fee = null;
        if (is_array ($trade) && array_key_exists ('fee_amount_quote', $trade)) {
            $fee = array (
                'currency' => $market['quote'],
                'cost' => $this->safe_float($trade, 'fee_amount_quote'),
            );
        }
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $id,
            'order' => $this->safe_string($trade, 'order_id'),
            'type' => $this->safe_string($trade, 'type'),
            'side' => $trade['side'],
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
        $trades = $this->publicGetPairTransactions (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        return $this->parse_trades($trades['data']['transactions'], $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '5m', $since = null, $limit = null) {
        return [
            $ohlcv[5],
            floatval ($ohlcv[0]),
            floatval ($ohlcv[1]),
            floatval ($ohlcv[2]),
            floatval ($ohlcv[3]),
            floatval ($ohlcv[4]),
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '5m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $date = $this->milliseconds ();
        $date = $this->ymd ($date);
        $date = explode ('-', $date);
        $response = $this->publicGetPairCandlestickCandletypeYyyymmdd (array_merge (array (
            'pair' => $market['id'],
            'candletype' => $this->timeframes[$timeframe],
            'yyyymmdd' => implode ('', $date),
        ), $params));
        $ohlcv = $response['data']['candlestick'][0]['ohlcv'];
        return $this->parse_ohlcvs($ohlcv, $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetUserAssets ($params);
        $result = array ( 'info' => $response );
        $balances = $response['data']['assets'];
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $id = $balance['asset'];
            $code = $id;
            if (is_array ($this->currencies_by_id) && array_key_exists ($id, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$id]['code'];
            }
            $account = array (
                'free' => floatval ($balance['free_amount']),
                'used' => floatval ($balance['locked_amount']),
                'total' => floatval ($balance['onhand_amount']),
            );
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_order ($order, $market = null) {
        $marketId = $this->safe_string($order, 'pair');
        $symbol = null;
        if ($marketId && !$market && (is_array ($this->marketsById) && array_key_exists ($marketId, $this->marketsById))) {
            $market = $this->marketsById[$marketId];
        }
        if ($market)
            $symbol = $market['symbol'];
        $timestamp = $this->safe_integer($order, 'ordered_at');
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'start_amount');
        $filled = $this->safe_float($order, 'executed_amount');
        $remaining = $this->safe_float($order, 'remaining_amount');
        $cost = $filled * $this->safe_float($order, 'average_price');
        $status = $this->safe_string($order, 'status');
        // UNFILLED
        // PARTIALLY_FILLED
        // FULLY_FILLED
        // CANCELED_UNFILLED
        // CANCELED_PARTIALLY_FILLED
        if ($status === 'FULLY_FILLED') {
            $status = 'closed';
        } else if ($status === 'CANCELED_UNFILLED' || $status === 'CANCELED_PARTIALLY_FILLED') {
            $status = 'canceled';
        } else {
            $status = 'open';
        }
        $type = $this->safe_string($order, 'type');
        if ($type !== null)
            $type = strtolower ($type);
        $side = $this->safe_string($order, 'side');
        if ($side !== null)
            $side = strtolower ($side);
        return array (
            'id' => $this->safe_string($order, 'order_id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
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
            'trades' => null,
            'fee' => null,
            'info' => $order,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($price === null)
            throw new InvalidOrder ($this->id . ' createOrder requires a $price argument for both $market and limit orders');
        $request = array (
            'pair' => $market['id'],
            'amount' => $this->amount_to_string($symbol, $amount),
            'price' => $this->price_to_precision($symbol, $price),
            'side' => $side,
            'type' => $type,
        );
        $response = $this->privatePostUserSpotOrder (array_merge ($request, $params));
        $id = $response['data']['order_id'];
        $order = $this->parse_order($response['data'], $market);
        $this->orders[$id] = $order;
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostUserSpotCancelOrder (array_merge (array (
            'order_id' => $id,
            'pair' => $market['id'],
        ), $params));
        return $response['data'];
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateGetUserSpotOrder (array_merge (array (
            'order_id' => $id,
            'pair' => $market['id'],
        ), $params));
        return $this->parse_order($response['data']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair' => $market['id'],
        );
        if ($limit)
            $request['count'] = $limit;
        if ($since)
            $request['since'] = intval ($since / 1000);
        $orders = $this->privateGetUserSpotActiveOrders (array_merge ($request, $params));
        return $this->parse_orders($orders['data']['orders'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $market = null;
        if ($symbol !== null) {
            $this->load_markets();
            $market = $this->market ($symbol);
        }
        $request = array ();
        if ($market !== null)
            $request['pair'] = $market['id'];
        if ($limit !== null)
            $request['count'] = $limit;
        if ($since !== null)
            $request['since'] = intval ($since / 1000);
        $trades = $this->privateGetUserSpotTradeHistory (array_merge ($request, $params));
        return $this->parse_trades($trades['data']['trades'], $market, $since, $limit);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privateGetUserWithdrawalAccount (array_merge (array (
            'asset' => $currency['id'],
        ), $params));
        // Not sure about this if there could be more than one account...
        $accounts = $response['data']['accounts'];
        $address = $this->safe_string($accounts[0], 'address');
        return array (
            'currency' => $currency,
            'address' => $address,
            'tag' => null,
            'info' => $response,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        if (!(is_array ($params) && array_key_exists ('uuid', $params))) {
            throw new ExchangeError ($this->id . ' uuid is required for withdrawal');
        }
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privatePostUserRequestWithdrawal (array_merge (array (
            'asset' => $currency['id'],
            'amount' => $amount,
        ), $params));
        return array (
            'info' => $response,
            'id' => $response['data']['txid'],
        );
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'][$api] . '/';
        if ($api === 'public') {
            $url .= $this->implode_params($path, $params);
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = $nonce;
            $url .= $this->version . '/' . $this->implode_params($path, $params);
            if ($method === 'POST') {
                $body = $this->json ($query);
                $auth .= $body;
            } else {
                $auth .= '/' . $this->version . '/' . $path;
                if ($query) {
                    $query = $this->urlencode ($query);
                    $url .= '?' . $query;
                    $auth .= '?' . $query;
                }
            }
            $headers = array (
                'Content-Type' => 'application/json',
                'ACCESS-KEY' => $this->apiKey,
                'ACCESS-NONCE' => $nonce,
                'ACCESS-SIGNATURE' => $this->hmac ($this->encode ($auth), $this->encode ($this->secret)),
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        $success = $this->safe_integer($response, 'success');
        $data = $this->safe_value($response, 'data');
        if (!$success || !$data) {
            $errorMessages = array (
                '10000' => 'URL does not exist',
                '10001' => 'A system error occurred. Please contact support',
                '10002' => 'Invalid JSON format. Please check the contents of transmission',
                '10003' => 'A system error occurred. Please contact support',
                '10005' => 'A timeout error occurred. Please wait for a while and try again',
                '20001' => 'API authentication failed',
                '20002' => 'Illegal API key',
                '20003' => 'API key does not exist',
                '20004' => 'API Nonce does not exist',
                '20005' => 'API signature does not exist',
                '20011' => 'Two-step verification failed',
                '20014' => 'SMS authentication failed',
                '30001' => 'Please specify the order quantity',
                '30006' => 'Please specify the order ID',
                '30007' => 'Please specify the order ID array',
                '30009' => 'Please specify the stock',
                '30012' => 'Please specify the order price',
                '30013' => 'Trade Please specify either',
                '30015' => 'Please specify the order type',
                '30016' => 'Please specify asset name',
                '30019' => 'Please specify uuid',
                '30039' => 'Please specify the amount to be withdrawn',
                '40001' => 'The order quantity is invalid',
                '40006' => 'Count value is invalid',
                '40007' => 'End time is invalid',
                '40008' => 'end_id Value is invalid',
                '40009' => 'The from_id value is invalid',
                '40013' => 'The order ID is invalid',
                '40014' => 'The order ID array is invalid',
                '40015' => 'Too many specified orders',
                '40017' => 'Incorrect issue name',
                '40020' => 'The order price is invalid',
                '40021' => 'The trading classification is invalid',
                '40022' => 'Start date is invalid',
                '40024' => 'The order type is invalid',
                '40025' => 'Incorrect asset name',
                '40028' => 'uuid is invalid',
                '40048' => 'The amount of withdrawal is illegal',
                '50003' => 'Currently, this account is in a state where you can not perform the operation you specified. Please contact support',
                '50004' => 'Currently, this account is temporarily registered. Please try again after registering your account',
                '50005' => 'Currently, this account is locked. Please contact support',
                '50006' => 'Currently, this account is locked. Please contact support',
                '50008' => 'User identification has not been completed',
                '50009' => 'Your order does not exist',
                '50010' => 'Can not cancel specified order',
                '50011' => 'API not found',
                '60001' => 'The number of possessions is insufficient',
                '60002' => 'It exceeds the quantity upper limit of the tender buying order',
                '60003' => 'The specified quantity exceeds the limit',
                '60004' => 'The specified quantity is below the threshold',
                '60005' => 'The specified price is above the limit',
                '60006' => 'The specified price is below the lower limit',
                '70001' => 'A system error occurred. Please contact support',
                '70002' => 'A system error occurred. Please contact support',
                '70003' => 'A system error occurred. Please contact support',
                '70004' => 'We are unable to accept orders as the transaction is currently suspended',
                '70005' => 'Order can not be accepted because purchase order is currently suspended',
                '70006' => 'We can not accept orders because we are currently unsubscribed ',
            );
            $errorClasses = $this->exceptions;
            $code = $this->safe_string($data, 'code');
            $message = $this->safe_string($errorMessages, $code, 'Error');
            $ErrorClass = $this->safe_value($errorClasses, $code);
            if ($ErrorClass !== null) {
                throw new $ErrorClass ($message);
            } else {
                throw new ExchangeError ($this->id . ' ' . $this->json ($response));
            }
        }
        return $response;
    }
}
