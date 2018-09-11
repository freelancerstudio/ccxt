<?php

namespace ccxt;

use Exception as Exception; // a common import

class livecoin extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'livecoin',
            'name' => 'LiveCoin',
            'countries' => array ( 'US', 'UK', 'RU' ),
            'rateLimit' => 1000,
            'userAgent' => $this->userAgents['chrome'],
            'has' => array (
                'fetchDepositAddress' => true,
                'CORS' => false,
                'fetchTickers' => true,
                'fetchCurrencies' => true,
                'fetchTradingFees' => true,
                'fetchOrders' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27980768-f22fc424-638a-11e7-89c9-6010a54ff9be.jpg',
                'api' => 'https://api.livecoin.net',
                'www' => 'https://www.livecoin.net',
                'doc' => 'https://www.livecoin.net/api?lang=en',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'exchange/all/order_book',
                        'exchange/last_trades',
                        'exchange/maxbid_minask',
                        'exchange/order_book',
                        'exchange/restrictions',
                        'exchange/ticker', // omit params to get all tickers at once
                        'info/coinInfo',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'exchange/client_orders',
                        'exchange/order',
                        'exchange/trades',
                        'exchange/commission',
                        'exchange/commissionCommonInfo',
                        'payment/balances',
                        'payment/balance',
                        'payment/get/address',
                        'payment/history/size',
                        'payment/history/transactions',
                    ),
                    'post' => array (
                        'exchange/buylimit',
                        'exchange/buymarket',
                        'exchange/cancellimit',
                        'exchange/selllimit',
                        'exchange/sellmarket',
                        'payment/out/capitalist',
                        'payment/out/card',
                        'payment/out/coin',
                        'payment/out/okpay',
                        'payment/out/payeer',
                        'payment/out/perfectmoney',
                        'payment/voucher/amount',
                        'payment/voucher/make',
                        'payment/voucher/redeem',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.18 / 100,
                    'taker' => 0.18 / 100,
                ),
            ),
            'commonCurrencies' => array (
                'BTCH' => 'Bithash',
                'CPC' => 'CapriCoin',
                'EDR' => 'E-Dinar Coin', // conflicts with EDR for Endor Protocol and EDRCoin
                'eETT' => 'EETT',
                'FirstBlood' => '1ST',
                'FORTYTWO' => '42',
                'ORE' => 'Orectic',
                'RUR' => 'RUB',
                'SCT' => 'SpaceCoin',
                'TPI' => 'ThaneCoin',
                'wETT' => 'WETT',
                'XBT' => 'Bricktox',
            ),
            'exceptions' => array (
                '1' => '\\ccxt\\ExchangeError',
                '10' => '\\ccxt\\AuthenticationError',
                '100' => '\\ccxt\\ExchangeError', // invalid parameters
                '101' => '\\ccxt\\AuthenticationError',
                '102' => '\\ccxt\\AuthenticationError',
                '103' => '\\ccxt\\InvalidOrder', // invalid currency
                '104' => '\\ccxt\\InvalidOrder', // invalid amount
                '105' => '\\ccxt\\InvalidOrder', // unable to block funds
                '11' => '\\ccxt\\AuthenticationError',
                '12' => '\\ccxt\\AuthenticationError',
                '2' => '\\ccxt\\AuthenticationError', // "User not found"
                '20' => '\\ccxt\\AuthenticationError',
                '30' => '\\ccxt\\AuthenticationError',
                '31' => '\\ccxt\\NotSupported',
                '32' => '\\ccxt\\ExchangeError',
                '429' => '\\ccxt\\DDoSProtection',
                '503' => '\\ccxt\\ExchangeNotAvailable',
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetExchangeTicker ();
        $restrictions = $this->publicGetExchangeRestrictions ();
        $restrictionsById = $this->index_by($restrictions['restrictions'], 'currencyPair');
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = $market['symbol'];
            list ($baseId, $quoteId) = explode ('/', $id);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $coinRestrictions = $this->safe_value($restrictionsById, $symbol);
            $precision = array (
                'price' => 5,
                'amount' => 8,
                'cost' => 8,
            );
            $limits = array (
                'amount' => array (
                    'min' => pow (10, -$precision['amount']),
                    'max' => pow (10, $precision['amount']),
                ),
            );
            if ($coinRestrictions) {
                $precision['price'] = $this->safe_integer($coinRestrictions, 'priceScale', 5);
                $limits['amount']['min'] = $this->safe_float($coinRestrictions, 'minLimitQuantity', $limits['amount']['min']);
            }
            $limits['price'] = array (
                'min' => pow (10, -$precision['price']),
                'max' => pow (10, $precision['price']),
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetInfoCoinInfo ($params);
        $currencies = $response['info'];
        $result = array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $id = $currency['symbol'];
            // todo => will need to rethink the fees
            // to add support for multiple withdrawal/deposit methods and
            // differentiated fees for each particular method
            $code = $this->common_currency_code($id);
            $precision = 8; // default $precision, todo => fix "magic constants"
            $active = ($currency['walletStatus'] === 'normal');
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => $currency['name'],
                'active' => $active,
                'fee' => $currency['withdrawFee'], // todo => redesign
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $currency['minOrderAmount'],
                        'max' => pow (10, $precision),
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision),
                        'max' => pow (10, $precision),
                    ),
                    'cost' => array (
                        'min' => $currency['minOrderAmount'],
                        'max' => null,
                    ),
                    'withdraw' => array (
                        'min' => $currency['minWithdrawAmount'],
                        'max' => pow (10, $precision),
                    ),
                    'deposit' => array (
                        'min' => $currency['minDepositAmount'],
                        'max' => null,
                    ),
                ),
            );
        }
        $result = $this->append_fiat_currencies ($result);
        return $result;
    }

    public function append_fiat_currencies ($result) {
        $precision = 8;
        $defaults = array (
            'info' => null,
            'active' => true,
            'fee' => null,
            'precision' => $precision,
            'limits' => array (
                'withdraw' => array ( 'min' => null, 'max' => null ),
                'deposit' => array ( 'min' => null, 'max' => null ),
                'amount' => array ( 'min' => null, 'max' => null ),
                'cost' => array ( 'min' => null, 'max' => null ),
                'price' => array (
                    'min' => pow (10, -$precision),
                    'max' => pow (10, $precision),
                ),
            ),
        );
        $currencies = array (
            array ( 'id' => 'USD', 'code' => 'USD', 'name' => 'US Dollar' ),
            array ( 'id' => 'EUR', 'code' => 'EUR', 'name' => 'Euro' ),
            // array ( 'id' => 'RUR', 'code' => 'RUB', 'name' => 'Russian ruble' ),
        );
        $currencies[] = array (
            'id' => 'RUR',
            'code' => $this->common_currency_code('RUR'),
            'name' => 'Russian ruble',
        );
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $code = $currency['code'];
            $result[$code] = array_merge ($defaults, $currency);
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privateGetPaymentBalances ();
        $result = array ( 'info' => $balances );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currency = $balance['currency'];
            $account = null;
            if (is_array ($result) && array_key_exists ($currency, $result))
                $account = $result[$currency];
            else
                $account = $this->account ();
            if ($balance['type'] === 'total')
                $account['total'] = floatval ($balance['value']);
            if ($balance['type'] === 'available')
                $account['free'] = floatval ($balance['value']);
            if ($balance['type'] === 'trade')
                $account['used'] = floatval ($balance['value']);
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_trading_fees ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetExchangeCommissionCommonInfo ($params);
        $commission = $this->safe_float($response, 'commission');
        return array (
            'info' => $response,
            'maker' => $commission,
            'taker' => $commission,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'currencyPair' => $this->market_id($symbol),
            'groupByPrice' => 'false',
        );
        if ($limit !== null)
            $request['depth'] = $limit; // 100
        $orderbook = $this->publicGetExchangeOrderBook (array_merge ($request, $params));
        $timestamp = $orderbook['timestamp'];
        return $this->parse_order_book($orderbook, $timestamp);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
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
            'bid' => $this->safe_float($ticker, 'best_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'best_ask'),
            'askVolume' => null,
            'vwap' => $this->safe_float($ticker, 'vwap'),
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

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetExchangeTicker ($params);
        $tickers = $this->index_by($response, 'symbol');
        $ids = is_array ($tickers) ? array_keys ($tickers) : array ();
        $result = array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $this->markets_by_id[$id];
            $symbol = $market['symbol'];
            $ticker = $tickers[$id];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetExchangeTicker (array_merge (array (
            'currencyPair' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market) {
        $timestamp = $trade['time'] * 1000;
        return array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'id' => (string) $trade['id'],
            'order' => null,
            'type' => null,
            'side' => strtolower ($trade['type']),
            'price' => $trade['price'],
            'amount' => $trade['quantity'],
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetExchangeLastTrades (array_merge (array (
            'currencyPair' => $market['id'],
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderId' => $id,
        );
        $response = $this->privateGetExchangeOrder (array_merge ($request, $params));
        return $this->parse_order($response);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'OPEN' => 'open',
            'PARTIALLY_FILLED' => 'open',
            'EXECUTED' => 'closed',
            'CANCELLED' => 'canceled',
            'PARTIALLY_FILLED_AND_CANCELLED' => 'canceled',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function parse_order ($order, $market = null) {
        $timestamp = null;
        if (is_array ($order) && array_key_exists ('lastModificationTime', $order)) {
            $timestamp = $this->safe_string($order, 'lastModificationTime');
            if ($timestamp !== null) {
                if (mb_strpos ($timestamp, 'T') !== false) {
                    $timestamp = $this->parse8601 ($timestamp);
                } else {
                    $timestamp = $this->safe_integer($order, 'lastModificationTime');
                }
            }
        }
        // TODO currently not supported by livecoin
        // $trades = $this->parse_trades($order['trades'], $market, since, limit);
        $trades = null;
        $status = $this->parse_order_status($this->safe_string_2($order, 'status', 'orderStatus'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'currencyPair');
            $marketId = $this->safe_string($order, 'symbol', $marketId);
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                $market = $this->markets_by_id[$marketId];
        }
        $type = null;
        $side = null;
        if (is_array ($order) && array_key_exists ('type', $order)) {
            $lowercaseType = strtolower ($order['type']);
            $orderType = explode ('_', $lowercaseType);
            $type = $orderType[0];
            $side = $orderType[1];
        }
        $price = $this->safe_float($order, 'price');
        // of the next two lines the latter overrides the former, if present in the $order structure
        $remaining = $this->safe_float($order, 'remainingQuantity');
        $remaining = $this->safe_float($order, 'remaining_quantity', $remaining);
        $amount = $this->safe_float($order, 'quantity', $remaining);
        $filled = null;
        if ($remaining !== null) {
            $filled = $amount - $remaining;
        }
        $cost = null;
        if ($filled !== null && $price !== null) {
            $cost = $filled * $price;
        }
        $feeRate = $this->safe_float($order, 'commission_rate');
        $feeCost = null;
        if ($cost !== null && $feeRate !== null) {
            $feeCost = $cost * $feeRate;
        }
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['quote'];
        }
        return array (
            'info' => $order,
            'id' => $order['id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => $trades,
            'fee' => array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
                'rate' => $feeRate,
            ),
        );
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array ();
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['currencyPair'] = $market['id'];
        }
        if ($since !== null)
            $request['issuedFrom'] = intval ($since);
        if ($limit !== null)
            $request['endRow'] = $limit - 1;
        $response = $this->privateGetExchangeClientOrders (array_merge ($request, $params));
        $result = array ();
        $rawOrders = array ();
        if ($response['data'])
            $rawOrders = $response['data'];
        for ($i = 0; $i < count ($rawOrders); $i++) {
            $order = $rawOrders[$i];
            $result[] = $this->parse_order($order, $market);
        }
        return $result;
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'openClosed' => 'OPEN',
        ), $params));
        return $result;
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'openClosed' => 'CLOSED',
        ), $params));
        return $result;
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $method = 'privatePostExchange' . $this->capitalize ($side) . $type;
        $market = $this->market ($symbol);
        $order = array (
            'quantity' => $this->amount_to_precision($symbol, $amount),
            'currencyPair' => $market['id'],
        );
        if ($type === 'limit')
            $order['price'] = $this->price_to_precision($symbol, $price);
        $response = $this->$method (array_merge ($order, $params));
        $result = array (
            'info' => $response,
            'id' => (string) $response['orderId'],
        );
        $success = $this->safe_value($response, 'success');
        if ($success) {
            $result['status'] = 'open';
        }
        return $result;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' cancelOrder requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        $currencyPair = $market['id'];
        $response = $this->privatePostExchangeCancellimit (array_merge (array (
            'orderId' => $id,
            'currencyPair' => $currencyPair,
        ), $params));
        $message = $this->safe_string($response, 'message', $this->json ($response));
        if (is_array ($response) && array_key_exists ('success', $response)) {
            if (!$response['success']) {
                throw new InvalidOrder ($message);
            } else if (is_array ($response) && array_key_exists ('cancelled', $response)) {
                if ($response['cancelled']) {
                    return array (
                        'status' => 'canceled',
                        'info' => $response,
                    );
                } else {
                    throw new OrderNotFound ($message);
                }
            }
        }
        throw new ExchangeError ($this->id . ' cancelOrder() failed => ' . $this->json ($response));
    }

    public function withdraw ($currency, $amount, $address, $tag = null, $params = array ()) {
        // Sometimes the $response with be array ( key => null ) for all keys.
        // An example is if you attempt to withdraw more than is allowed when $withdrawal fees are considered.
        $this->load_markets();
        $this->check_address($address);
        $wallet = $address;
        if ($tag !== null)
            $wallet .= '::' . $tag;
        $withdrawal = array (
            'amount' => $this->decimal_to_precision($amount, TRUNCATE, $this->currencies[$currency]['precision'], DECIMAL_PLACES),
            'currency' => $this->common_currency_code($currency),
            'wallet' => $wallet,
        );
        $response = $this->privatePostPaymentOutCoin (array_merge ($withdrawal, $params));
        $id = $this->safe_integer($response, 'id');
        if ($id === null)
            throw new InsufficientFunds ($this->id . ' insufficient funds to cover requested $withdrawal $amount post fees ' . $this->json ($response));
        return array (
            'info' => $response,
            'id' => $id,
        );
    }

    public function fetch_deposit_address ($currency, $params = array ()) {
        $request = array (
            'currency' => $currency,
        );
        $response = $this->privateGetPaymentGetAddress (array_merge ($request, $params));
        $address = $this->safe_string($response, 'wallet');
        $tag = null;
        if (mb_strpos ($address, ':') !== false) {
            $parts = explode (':', $address);
            $address = $parts[0];
            $tag = $parts[2];
        }
        $this->check_address($address);
        return array (
            'currency' => $currency,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $path;
        $query = $this->urlencode ($this->keysort ($params));
        if ($method === 'GET') {
            if ($params) {
                $url .= '?' . $query;
            }
        }
        if ($api === 'private') {
            $this->check_required_credentials();
            if ($method === 'POST')
                $body = $query;
            $signature = $this->hmac ($this->encode ($query), $this->encode ($this->secret), 'sha256');
            $headers = array (
                'Api-Key' => $this->apiKey,
                'Sign' => strtoupper ($signature),
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return;
        if ($body[0] === '{') {
            $response = json_decode ($body, $as_associative_array = true);
            if ($code >= 300) {
                $errorCode = $this->safe_string($response, 'errorCode');
                if (is_array ($this->exceptions) && array_key_exists ($errorCode, $this->exceptions)) {
                    $ExceptionClass = $this->exceptions[$errorCode];
                    throw new $ExceptionClass ($this->id . ' ' . $body);
                } else {
                    throw new ExchangeError ($this->id . ' ' . $body);
                }
            }
            // returns status $code 200 even if $success === false
            $success = $this->safe_value($response, 'success', true);
            if (!$success) {
                $message = $this->safe_string($response, 'message');
                if ($message !== null) {
                    if (mb_strpos ($message, 'Cannot find order') !== false) {
                        throw new OrderNotFound ($this->id . ' ' . $body);
                    }
                }
                $exception = $this->safe_string($response, 'exception');
                if ($exception !== null) {
                    if (mb_strpos ($exception, 'Minimal amount is') !== false) {
                        throw new InvalidOrder ($this->id . ' ' . $body);
                    }
                }
                throw new ExchangeError ($this->id . ' ' . $body);
            }
        }
    }
}
