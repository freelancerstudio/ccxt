<?php

namespace ccxt;

use Exception as Exception; // a common import

class gdax extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'gdax',
            'name' => 'GDAX',
            'countries' => array ( 'US' ),
            'rateLimit' => 1000,
            'userAgent' => $this->userAgents['chrome'],
            'has' => array (
                'CORS' => true,
                'fetchOHLCV' => true,
                'deposit' => true,
                'withdraw' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchDepositAddress' => true,
                'fetchMyTrades' => true,
                'fetchTransactions' => true,
            ),
            'timeframes' => array (
                '1m' => 60,
                '5m' => 300,
                '15m' => 900,
                '1h' => 3600,
                '6h' => 21600,
                '1d' => 86400,
            ),
            'urls' => array (
                'test' => 'https://api-public.sandbox.gdax.com',
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766527-b1be41c6-5edb-11e7-95f6-5b496c469e2c.jpg',
                'api' => 'https://api.gdax.com',
                'www' => 'https://www.gdax.com',
                'doc' => 'https://docs.gdax.com',
                'fees' => array (
                    'https://www.gdax.com/fees',
                    'https://support.gdax.com/customer/en/portal/topics/939402-depositing-and-withdrawing-funds/articles',
                ),
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
                'password' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currencies',
                        'products',
                        'products/{id}/book',
                        'products/{id}/candles',
                        'products/{id}/stats',
                        'products/{id}/ticker',
                        'products/{id}/trades',
                        'time',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{id}',
                        'accounts/{id}/holds',
                        'accounts/{id}/ledger',
                        'accounts/{id}/transfers',
                        'coinbase-accounts',
                        'fills',
                        'funding',
                        'orders',
                        'orders/{id}',
                        'payment-methods',
                        'position',
                        'reports/{id}',
                        'users/self/trailing-volume',
                    ),
                    'post' => array (
                        'conversions',
                        'deposits/coinbase-account',
                        'deposits/payment-method',
                        'coinbase-accounts/{id}/addresses',
                        'funding/repay',
                        'orders',
                        'position/close',
                        'profiles/margin-transfer',
                        'reports',
                        'withdrawals/coinbase',
                        'withdrawals/crypto',
                        'withdrawals/payment-method',
                    ),
                    'delete' => array (
                        'orders',
                        'orders/{id}',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => true, // complicated tier system per coin
                    'percentage' => true,
                    'maker' => 0.0,
                    'taker' => 0.3 / 100, // tiered fee starts at 0.3%
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array (
                        'BCH' => 0,
                        'BTC' => 0,
                        'LTC' => 0,
                        'ETH' => 0,
                        'EUR' => 0.15,
                        'USD' => 25,
                    ),
                    'deposit' => array (
                        'BCH' => 0,
                        'BTC' => 0,
                        'LTC' => 0,
                        'ETH' => 0,
                        'EUR' => 0.15,
                        'USD' => 10,
                    ),
                ),
            ),
            'exceptions' => array (
                'exact' => array (
                    'Insufficient funds' => '\\ccxt\\InsufficientFunds',
                    'NotFound' => '\\ccxt\\OrderNotFound',
                    'Invalid API Key' => '\\ccxt\\AuthenticationError',
                    'invalid signature' => '\\ccxt\\AuthenticationError',
                    'Invalid Passphrase' => '\\ccxt\\AuthenticationError',
                ),
                'broad' => array (
                    'Order already done' => '\\ccxt\\OrderNotFound',
                    'order not found' => '\\ccxt\\OrderNotFound',
                    'price too small' => '\\ccxt\\InvalidOrder',
                    'price too precise' => '\\ccxt\\InvalidOrder',
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->publicGetProducts ();
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = $market['id'];
            $base = $market['base_currency'];
            $quote = $market['quote_currency'];
            $symbol = $base . '/' . $quote;
            $priceLimits = array (
                'min' => $this->safe_float($market, 'quote_increment'),
                'max' => null,
            );
            $precision = array (
                'amount' => 8,
                'price' => $this->precision_from_string($this->safe_string($market, 'quote_increment')),
            );
            $taker = $this->fees['trading']['taker'];  // does not seem right
            if (($base === 'ETH') || ($base === 'LTC')) {
                $taker = 0.003;
            }
            $accessible = true;
            if (is_array ($market) && array_key_exists ('accessible', $market)) {
                $accessible = $this->safe_value($market, 'accessible');
            }
            $active = ($market['status'] === 'online') && $accessible;
            $result[] = array_merge ($this->fees['trading'], array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($market, 'base_min_size'),
                        'max' => $this->safe_float($market, 'base_max_size'),
                    ),
                    'price' => $priceLimits,
                    'cost' => array (
                        'min' => $this->safe_float($market, 'min_market_funds'),
                        'max' => $this->safe_float($market, 'max_market_funds'),
                    ),
                ),
                'taker' => $taker,
                'active' => $active,
                'info' => $market,
            ));
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privateGetAccounts ();
        $result = array ( 'info' => $balances );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currency = $balance['currency'];
            $account = array (
                'free' => $this->safe_float($balance, 'available'),
                'used' => $this->safe_float($balance, 'hold'),
                'total' => $this->safe_float($balance, 'balance'),
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetProductsIdBook (array_merge (array (
            'id' => $this->market_id($symbol),
            'level' => 2, // 1 best bidask, 2 aggregated, 3 full
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array_merge (array (
            'id' => $market['id'],
        ), $params);
        $ticker = $this->publicGetProductsIdTicker ($request);
        $timestamp = $this->parse8601 ($this->safe_value($ticker, 'time'));
        $bid = null;
        $ask = null;
        if (is_array ($ticker) && array_key_exists ('bid', $ticker))
            $bid = $this->safe_float($ticker, 'bid');
        if (is_array ($ticker) && array_key_exists ('ask', $ticker))
            $ask = $this->safe_float($ticker, 'ask');
        $last = $this->safe_float($ticker, 'price');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $bid,
            'bidVolume' => null,
            'ask' => $ask,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($this->safe_string_2($trade, 'time', 'created_at'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($trade, 'product_id');
            $market = $this->safe_value($this->markets_by_id, $marketId);
        }
        if ($market)
            $symbol = $market['symbol'];
        $feeRate = null;
        $feeCurrency = null;
        $takerOrMaker = null;
        if ($market !== null) {
            $feeCurrency = $market['quote'];
            if (is_array ($trade) && array_key_exists ('liquidity', $trade)) {
                $takerOrMaker = ($trade['liquidity'] === 'T') ? 'taker' : 'maker';
                $feeRate = $market[$takerOrMaker];
            }
        }
        $feeCost = $this->safe_float($trade, 'fill_fees');
        if ($feeCost === null)
            $feeCost = $this->safe_float($trade, 'fee');
        $fee = array (
            'cost' => $feeCost,
            'currency' => $feeCurrency,
            'rate' => $feeRate,
        );
        $type = null;
        $id = $this->safe_string($trade, 'trade_id');
        $side = ($trade['side'] === 'buy') ? 'sell' : 'buy';
        $orderId = $this->safe_string($trade, 'order_id');
        // GDAX returns inverted $side to fetchMyTrades vs fetchTrades
        if ($orderId !== null)
            $side = ($trade['side'] === 'buy') ? 'buy' : 'sell';
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'size');
        return array (
            'id' => $id,
            'order' => $orderId,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'takerOrMaker' => $takerOrMaker,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'fee' => $fee,
            'cost' => $price * $amount,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // as of 2018-08-23
        if ($symbol === null) {
            throw new ArgumentsRequired ($this->id . ' fetchMyTrades requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'product_id' => $market['id'],
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        $response = $this->privateGetFills (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetProductsIdTrades (array_merge (array (
            'id' => $market['id'], // fixes issue #2
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv[0] * 1000,
            $ohlcv[3],
            $ohlcv[2],
            $ohlcv[1],
            $ohlcv[4],
            $ohlcv[5],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $granularity = $this->timeframes[$timeframe];
        $request = array (
            'id' => $market['id'],
            'granularity' => $granularity,
        );
        if ($since !== null) {
            $request['start'] = $this->ymdhms ($since);
            if ($limit === null) {
                // https://docs.gdax.com/#get-historic-rates
                $limit = 300; // max = 300
            }
            $request['end'] = $this->ymdhms ($this->sum ($limit * $granularity * 1000, $since));
        }
        $response = $this->publicGetProductsIdCandles (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function fetch_time () {
        $response = $this->publicGetTime ();
        return $this->parse8601 ($response['iso']);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'pending' => 'open',
            'active' => 'open',
            'open' => 'open',
            'done' => 'closed',
            'canceled' => 'canceled',
            'canceling' => 'open',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        $timestamp = $this->parse8601 ($order['created_at']);
        $symbol = null;
        if ($market === null) {
            if (is_array ($this->markets_by_id) && array_key_exists ($order['product_id'], $this->markets_by_id))
                $market = $this->markets_by_id[$order['product_id']];
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'size');
        if ($amount === null)
            $amount = $this->safe_float($order, 'funds');
        if ($amount === null)
            $amount = $this->safe_float($order, 'specified_funds');
        $filled = $this->safe_float($order, 'filled_size');
        $remaining = null;
        if ($amount !== null)
            if ($filled !== null)
                $remaining = $amount - $filled;
        $cost = $this->safe_float($order, 'executed_value');
        $fee = array (
            'cost' => $this->safe_float($order, 'fill_fees'),
            'currency' => null,
            'rate' => null,
        );
        if ($market)
            $symbol = $market['symbol'];
        return array (
            'id' => $order['id'],
            'info' => $order,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $order['type'],
            'side' => $order['side'],
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'fee' => $fee,
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateGetOrdersId (array_merge (array (
            'id' => $id,
        ), $params));
        return $this->parse_order($response);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'status' => 'all',
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['product_id'] = $market['id'];
        }
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['product_id'] = $market['id'];
        }
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'status' => 'done',
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['product_id'] = $market['id'];
        }
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        // $oid = (string) $this->nonce ();
        $order = array (
            'product_id' => $this->market_id($symbol),
            'side' => $side,
            'size' => $amount,
            'type' => $type,
        );
        if ($type === 'limit')
            $order['price'] = $price;
        $response = $this->privatePostOrders (array_merge ($order, $params));
        return $this->parse_order($response);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        return $this->privateDeleteOrdersId (array ( 'id' => $id ));
    }

    public function calculate_fee ($symbol, $type, $side, $amount, $price, $takerOrMaker = 'taker', $params = array ()) {
        $market = $this->markets[$symbol];
        $rate = $market[$takerOrMaker];
        $cost = $amount * $price;
        $currency = $market['quote'];
        return array (
            'type' => $takerOrMaker,
            'currency' => $currency,
            'rate' => $rate,
            'cost' => floatval ($this->currency_to_precision($currency, $rate * $cost)),
        );
    }

    public function get_payment_methods () {
        $response = $this->privateGetPaymentMethods ();
        return $response;
    }

    public function deposit ($code, $amount, $address, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
            'amount' => $amount,
        );
        $method = 'privatePostDeposits';
        if (is_array ($params) && array_key_exists ('payment_method_id', $params)) {
            // deposit from a payment_method, like a bank account
            $method .= 'PaymentMethod';
        } else if (is_array ($params) && array_key_exists ('coinbase_account_id', $params)) {
            // deposit into GDAX account from a Coinbase account
            $method .= 'CoinbaseAccount';
        } else {
            // deposit methodotherwise we did not receive a supported deposit location
            // relevant docs link for the Googlers
            // https://docs.gdax.com/#deposits
            throw new NotSupported ($this->id . ' deposit() requires one of `coinbase_account_id` or `payment_method_id` extra params');
        }
        $response = $this->$method (array_merge ($request, $params));
        if (!$response)
            throw new ExchangeError ($this->id . ' deposit() error => ' . $this->json ($response));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $currency = $this->currency ($code);
        $this->load_markets();
        $request = array (
            'currency' => $currency['id'],
            'amount' => $amount,
        );
        $method = 'privatePostWithdrawals';
        if (is_array ($params) && array_key_exists ('payment_method_id', $params)) {
            $method .= 'PaymentMethod';
        } else if (is_array ($params) && array_key_exists ('coinbase_account_id', $params)) {
            $method .= 'CoinbaseAccount';
        } else {
            $method .= 'Crypto';
            $request['crypto_address'] = $address;
        }
        $response = $this->$method (array_merge ($request, $params));
        if (!$response)
            throw new ExchangeError ($this->id . ' withdraw() error => ' . $this->json ($response));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function fetch_transactions ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($code === null) {
            throw new ArgumentsRequired ($this->id . ' fetchTransactions() requires a $currency $code argument');
        }
        $currency = $this->currency ($code);
        $accountId = null;
        $accounts = $this->privateGetAccounts ();
        for ($i = 0; $i < count ($accounts); $i++) {
            $account = $accounts[$i];
            // todo => use unified common currencies below
            if ($account['currency'] === $currency['id']) {
                $accountId = $account['id'];
                break;
            }
        }
        if ($accountId === null) {
            throw new ExchangeError ($this->id . ' fetchTransactions() could not find $account id for ' . $code);
        }
        $request = array (
            'id' => $accountId,
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privateGetAccountsIdTransfers (array_merge ($request, $params));
        for ($i = 0; $i < count ($response); $i++) {
            $response[$i]['currency'] = $code;
        }
        return $this->parseTransactions ($response, $currency, $since, $limit);
    }

    public function parse_transaction_status ($transaction) {
        if (is_array ($transaction && $transaction['canceled_at']) && array_key_exists ('canceled_at', $transaction && $transaction['canceled_at'])) {
            return 'canceled';
        } else if (is_array ($transaction && $transaction['completed_at']) && array_key_exists ('completed_at', $transaction && $transaction['completed_at'])) {
            return 'ok';
        } else if (((is_array ($transaction) && array_key_exists ('canceled_at', $transaction)) && !$transaction['canceled_at']) && ((is_array ($transaction) && array_key_exists ('completed_at', $transaction)) && !$transaction['completed_at']) && ((is_array ($transaction) && array_key_exists ('processed_at', $transaction)) && !$transaction['processed_at'])) {
            return 'pending';
        } else if (is_array ($transaction && $transaction['processed_at']) && array_key_exists ('processed_at', $transaction && $transaction['processed_at'])) {
            return 'pending';
        } else {
            return 'failed';
        }
    }

    public function parse_transaction ($transaction, $currency = null) {
        $details = $this->safe_value($transaction, 'details', array ());
        $id = $this->safe_string($transaction, 'id');
        $txid = $this->safe_string($details, 'crypto_transaction_hash');
        $timestamp = $this->parse8601 ($this->safe_string($transaction, 'created_at'));
        $updated = $this->parse8601 ($this->safe_string($transaction, 'processed_at'));
        $code = null;
        $currencyId = $this->safe_string($transaction, 'currency');
        if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
            $currency = $this->currencies_by_id[$currencyId];
            $code = $currency['code'];
        } else {
            $code = $this->common_currency_code($currencyId);
        }
        $fee = null;
        $status = $this->parse_transaction_status ($transaction);
        $amount = $this->safe_float($transaction, 'amount');
        $type = $this->safe_string($transaction, 'type');
        $address = $this->safe_string($details, 'crypto_address');
        $address = $this->safe_string($transaction, 'crypto_address', $address);
        if ($type === 'withdraw') {
            $type = 'withdrawal';
            $address = $this->safe_string($details, 'sent_to_address', $address);
        }
        return array (
            'info' => $transaction,
            'id' => $id,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'address' => $address,
            'tag' => null,
            'type' => $type,
            'amount' => $amount,
            'currency' => $code,
            'status' => $status,
            'updated' => $updated,
            'fee' => $fee,
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
            $nonce = (string) $this->nonce ();
            $payload = '';
            if ($method !== 'GET') {
                if ($query) {
                    $body = $this->json ($query);
                    $payload = $body;
                }
            }
            // $payload = ($body) ? $body : '';
            $what = $nonce . $method . $request . $payload;
            $secret = base64_decode ($this->secret);
            $signature = $this->hmac ($this->encode ($what), $secret, 'sha256', 'base64');
            $headers = array (
                'CB-ACCESS-KEY' => $this->apiKey,
                'CB-ACCESS-SIGN' => $this->decode ($signature),
                'CB-ACCESS-TIMESTAMP' => $nonce,
                'CB-ACCESS-PASSPHRASE' => $this->password,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $accounts = $this->safe_value($this->options, 'coinbaseAccounts');
        if ($accounts === null) {
            $accounts = $this->privateGetCoinbaseAccounts ();
            $this->options['coinbaseAccounts'] = $accounts; // cache it
            $this->options['coinbaseAccountsByCurrencyId'] = $this->index_by($accounts, 'currency');
        }
        $currencyId = $currency['id'];
        $account = $this->safe_value($this->options['coinbaseAccountsByCurrencyId'], $currencyId);
        if ($account === null) {
            // eslint-disable-next-line quotes
            throw new InvalidAddress ($this->id . " fetchDepositAddress() could not find $currency $code " . $code . " with id = " . $currencyId . " in $this->options['coinbaseAccountsByCurrencyId']");
        }
        $response = $this->privatePostCoinbaseAccountsIdAddresses (array_merge (array (
            'id' => $account['id'],
        ), $params));
        $address = $this->safe_string($response, 'address');
        // todo => figure this out
        // $tag = $this->safe_string($response, 'addressTag');
        $tag = null;
        return array (
            'currency' => $code,
            'address' => $this->check_address($address),
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response) {
        if (($code === 400) || ($code === 404)) {
            if ($body[0] === '{') {
                $message = $response['message'];
                $feedback = $this->id . ' ' . $message;
                $exact = $this->exceptions['exact'];
                if (is_array ($exact) && array_key_exists ($message, $exact)) {
                    throw new $exact[$message] ($feedback);
                }
                $broad = $this->exceptions['broad'];
                $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
                if ($broadKey !== null) {
                    throw new $broad[$broadKey] ($feedback);
                }
                throw new ExchangeError ($feedback); // unknown $message
            }
            throw new ExchangeError ($this->id . ' ' . $body);
        }
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array ($response) && array_key_exists ('message', $response)) {
            throw new ExchangeError ($this->id . ' ' . $this->json ($response));
        }
        return $response;
    }
}
