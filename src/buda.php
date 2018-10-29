<?php

namespace ccxt;

use Exception as Exception; // a common import

class buda extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'buda',
            'name' => 'Buda',
            'countries' => array ( 'AR', 'CL', 'CO', 'PE' ),
            'rateLimit' => 1000,
            'version' => 'v2',
            'has' => array (
                'CORS' => false,
                'createDepositAddress' => true,
                'fetchClosedOrders' => true,
                'fetchCurrencies' => true,
                'fetchDepositAddress' => true,
                'fetchDeposits' => true,
                'fetchFundingFees' => true,
                'fetchMyTrades' => false,
                'fetchOHLCV' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchTrades' => true,
                'fetchWithdrawals' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/47380619-8a029200-d706-11e8-91e0-8a391fe48de3.jpg',
                'api' => 'https://www.buda.com/api',
                'www' => 'https://www.buda.com',
                'doc' => 'https://api.buda.com',
                'fees' => 'https://www.buda.com/comisiones',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'pairs',
                        'markets',
                        'currencies',
                        'markets/{market}',
                        'markets/{market}/ticker',
                        'markets/{market}/volume',
                        'markets/{market}/order_book',
                        'markets/{market}/trades',
                        'currencies/{currency}/fees/deposit',
                        'currencies/{currency}/fees/withdrawal',
                        'tv/history',
                    ),
                    'post' => array (
                        'markets/{market}/quotations',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'balances',
                        'balances/{currency}',
                        'currencies/{currency}/balances',
                        'orders',
                        'orders/{id}',
                        'markets/{market}/orders',
                        'deposits',
                        'currencies/{currency}/deposits',
                        'withdrawals',
                        'currencies/{currency}/withdrawals',
                        'currencies/{currency}/receive_addresses',
                        'currencies/{currency}/receive_addresses/{id}',
                    ),
                    'post' => array (
                        'markets/{market}/orders',
                        'currencies/{currency}/deposits',
                        'currencies/{currency}/withdrawals',
                        'currencies/{currency}/simulated_withdrawals',
                        'currencies/{currency}/receive_addresses',
                    ),
                    'put' => array (
                        'orders/{id}',
                    ),
                ),
            ),
            'timeframes' => array (
                '1m' => '1',
                '5m' => '5',
                '30m' => '30',
                '1h' => '60',
                '2h' => '120',
                '1d' => 'D',
                '1w' => 'W',
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => true,
                    'percentage' => true,
                    'taker' => 0.008,  // 0.8%
                    'maker' => 0.004,  // 0.4%
                    'tiers' => array (
                        'taker' => [
                            [0, 0.008],  // 0.8%
                            [2000, 0.007],  // 0.7%
                            [20000, 0.006],  // 0.6%
                            [100000, 0.005],  // 0.5%
                            [500000, 0.004],  // 0.4%
                            [2500000, 0.003],  // 0.3%
                            [12500000, 0.002],  // 0.2%
                        ],
                        'maker' => [
                            [0, 0.004],  // 0.4%
                            [2000, 0.0035],  // 0.35%
                            [20000, 0.003],  // 0.3%
                            [100000, 0.0025],  // 0.25%
                            [500000, 0.002],  // 0.2%
                            [2500000, 0.0015],  // 0.15%
                            [12500000, 0.001],  // 0.1%
                        ],
                    ),
                ),
            ),
            'exceptions' => array (
                'not_authorized' => '\\ccxt\\AuthenticationError',  // array ( message => 'Invalid credentials', code => 'not_authorized' )
                'forbidden' => '\\ccxt\\PermissionDenied',  // array ( message => 'You dont have access to this resource', code => 'forbidden' )
                'invalid_record' => '\\ccxt\\ExchangeError',  // array ( message => 'Validation Failed', code => 'invalid_record', errors => array () )
                'not_found' => '\\ccxt\\ExchangeError',  // array ( message => 'Not found', code => 'not_found' )
                'parameter_missing' => '\\ccxt\\ExchangeError',  // array ( message => 'Parameter missing', code => 'parameter_missing' )
                'bad_parameter' => '\\ccxt\\ExchangeError',  // array ( message => 'Bad Parameter format', code => 'bad_parameter' )
            ),
        ));
    }

    public function fetch_currency_info ($currency, $currencies = null) {
        if (!$currencies) {
            $response = $this->publicGetCurrencies ();
            $currencies = $response['currencies'];
        }
        for ($i = 0; $i < count ($currencies); $i++) {
            $currencyInfo = $currencies[$i];
            if ($currencyInfo['id'] === $currency) {
                return $currencyInfo;
            }
        }
        return null;
    }

    public function fetch_markets () {
        $marketsResponse = $this->publicGetMarkets ();
        $markets = $marketsResponse['markets'];
        $currenciesResponse = $this->publicGetCurrencies ();
        $currencies = $currenciesResponse['currencies'];
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['id'];
            $baseId = $market['base_currency'];
            $quoteId = $market['quote_currency'];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $baseInfo = $this->fetch_currency_info ($baseId, $currencies);
            $quoteInfo = $this->fetch_currency_info ($quoteId, $currencies);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $baseInfo['input_decimals'],
                'price' => $quoteInfo['input_decimals'],
            );
            $limits = array (
                'amount' => array (
                    'min' => floatval ($market['minimum_order_amount'][0]),
                    'max' => null,
                ),
                'price' => array (
                    'min' => pow (10, -$precision['price']),
                    'max' => null,
                ),
            );
            $limits['cost'] = array (
                'min' => $limits['amount']['min'] * $limits['price']['min'],
                'max' => null,
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
        $response = $this->publicGetCurrencies ();
        $currencies = $response['currencies'];
        $result = array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            if (!$currency['managed'])
                continue;
            $id = $currency['id'];
            $code = $this->common_currency_code($id);
            $precision = $currency['input_decimals'];
            $minimum = pow (10, -$precision);
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => null,
                'active' => true,
                'fee' => null,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $minimum,
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => $minimum,
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'deposit' => array (
                        'min' => floatval ($currency['deposit_minimum'][0]),
                        'max' => null,
                    ),
                    'withdraw' => array (
                        'min' => floatval ($currency['withdrawal_minimum'][0]),
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_funding_fees ($codes = null, $params = array ()) {
        //  by default it will try load withdrawal fees of all currencies (with separate requests)
        //  however if you define $codes = array ( 'ETH', 'BTC' ) in args it will only load those
        $this->load_markets();
        $withdrawFees = array ();
        $depositFees = array ();
        $info = array ();
        if ($codes === null)
            $codes = is_array ($this->currencies) ? array_keys ($this->currencies) : array ();
        for ($i = 0; $i < count ($codes); $i++) {
            $code = $codes[$i];
            $currency = $this->currency ($code);
            $request = array ( 'currency' => $currency['id'] );
            $withdrawResponse = $this->publicGetCurrenciesCurrencyFeesWithdrawal ($request);
            $depositResponse = $this->publicGetCurrenciesCurrencyFeesDeposit ($request);
            $withdrawFees[$code] = $this->parse_funding_fee ($withdrawResponse['fee']);
            $depositFees[$code] = $this->parse_funding_fee ($depositResponse['fee']);
            $info[$code] = array (
                'withdraw' => $withdrawResponse,
                'deposit' => $depositResponse,
            );
        }
        return array (
            'withdraw' => $withdrawFees,
            'deposit' => $depositFees,
            'info' => $info,
        );
    }

    public function parse_funding_fee ($fee, $type = null) {
        if ($type === null)
            $type = $fee['name'];
        if ($type === 'withdrawal')
            $type = 'withdraw';
        return array (
            'type' => $type,
            'currency' => $fee['base'][1],
            'rate' => $fee['percent'],
            'cost' => floatval ($fee['base'][0]),
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketsMarketTicker (array_merge (array (
            'market' => $market['id'],
        ), $params));
        $ticker = $response['ticker'];
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        $last = floatval ($ticker['last_price'][0]);
        $percentage = floatval ($ticker['price_variation_24h']);
        $open = floatval ($this->price_to_precision($symbol, $last / ($percentage . 1)));
        $change = $last - $open;
        $average = ($last . $open) / 2;
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => floatval ($ticker['max_bid'][0]),
            'bidVolume' => null,
            'ask' => floatval ($ticker['min_ask'][0]),
            'askVolume' => null,
            'vwap' => null,
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => $open,
            'change' => $change,
            'percentage' => $percentage * 100,
            'average' => $average,
            'baseVolume' => floatval ($ticker['volume'][0]),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        // the $since argument works backwards – returns trades up to the specified timestamp
        // therefore not implemented here
        // the method is still available for users to be able to traverse backwards in time
        // by using the timestamp from the first received trade upon each iteration
        if ($limit !== null) {
            $request['limit'] = $limit; // 50 max
        }
        $response = $this->publicGetMarketsMarketTrades (array_merge ($request, $params));
        //
        //     { trades => {      market_id =>   "ETH-BTC",
        //                      timestamp =>    null,
        //                 last_timestamp =>   "1536901277302",
        //                        entries => array ( array ( "1540077456791", "0.0063767", "0.03", "sell", 479842 ),
        //                                   array ( "1539916642772", "0.01888263", "0.03019563", "sell", 479438 ),
        //                                   array ( "1539834081787", "0.023718648", "0.031001", "sell", 479069 ),
        //                                   ... )
        //
        return $this->parse_trades($response['trades']['entries'], $market, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //  array ( "1540077456791", "0.0063767", "0.03", "sell", 479842 )
        //
        $timestamp = null;
        $side = null;
        $type = null;
        $price = null;
        $amount = null;
        $id = null;
        $order = null;
        $fee = null;
        $symbol = null;
        $cost = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        if (gettype ($trade) === 'array' && count (array_filter (array_keys ($trade), 'is_string')) == 0) {
            $timestamp = intval ($trade[0]);
            $price = floatval ($trade[1]);
            $amount = floatval ($trade[2]);
            $cost = $price * $amount;
            $side = $trade[3];
            $id = (string) $trade[4];
        }
        return array (
            'id' => $id,
            'order' => $order,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketsMarketOrderBook (array_merge (array (
            'market' => $market['id'],
        ), $params));
        $orderBook = $response['order_book'];
        return $this->parse_order_book($orderBook);
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($since === null)
            $since = $this->milliseconds () - 86400000;
        $request = array (
            'symbol' => $market['id'],
            'resolution' => $this->timeframes[$timeframe],
            'from' => $since / 1000,
            'to' => $this->seconds (),
        );
        $response = $this->publicGetTvHistory (array_merge ($request, $params));
        return $this->parse_trading_view_ohlcv($response, $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetBalances ();
        $result = array ( 'info' => $response );
        $balances = $response['balances'];
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $id = $balance['id'];
            $currency = $this->common_currency_code($id);
            $total = floatval ($balance['amount'][0]);
            $free = floatval ($balance['available_amount'][0]);
            $account = array (
                'free' => $free,
                'used' => $total - $free,
                'total' => $total,
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateGetOrdersId (array_merge (array (
            'id' => intval ($id),
        ), $params));
        $order = $response['order'];
        return $this->parse_order($order);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null)
            $market = $this->market ($symbol);
        $response = $this->privateGetMarketsMarketOrders (array_merge (array (
            'market' => $market['id'],
            'per' => $limit,
        ), $params));
        $orders = $response['orders'];
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'state' => 'pending',
        ), $params));
        return $orders;
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'state' => 'traded',
        ), $params));
        return $orders;
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $side = ($side === 'buy') ? 'Bid' : 'Ask';
        $request = array (
            'market' => $this->market_id($symbol),
            'price_type' => $type,
            'type' => $side,
            'amount' => $this->amount_to_precision($symbol, $amount),
        );
        if ($type === 'limit')
            $request['limit'] = $this->price_to_precision($symbol, $price);
        $response = $this->privatePostMarketsMarketOrders (array_merge ($request, $params));
        $order = $response['order'];
        return $this->parse_order($order);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePutOrdersId (array_merge (array (
            'id' => intval ($id),
            'state' => 'canceling',
        ), $params));
        $order = $response['order'];
        return $this->parse_order($order);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'traded' => 'closed',
            'received' => 'open',
            'canceling' => 'canceled',
        );
        return (is_array ($statuses) && array_key_exists ($status, $statuses)) ? $statuses[$status] : $status;
    }

    public function parse_order ($order, $market = null) {
        $id = $order['id'];
        $timestamp = $this->parse8601 ($this->safe_string($order, 'created_at'));
        $symbol = null;
        if ($market === null) {
            $marketId = $order['market_id'];
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                $market = $this->markets_by_id[$marketId];
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $type = $order['price_type'];
        $side = strtolower ($order['type']);
        $status = $this->parse_order_status($this->safe_string($order, 'state'));
        $amount = floatval ($order['original_amount'][0]);
        $remaining = floatval ($order['amount'][0]);
        $filled = floatval ($order['traded_amount'][0]);
        $cost = floatval ($order['total_exchanged'][0]);
        $price = $order['limit'];
        if ($price !== null)
            $price = floatval ($price[0]);
        if ($cost > 0 && $filled > 0)
            $price = $this->price_to_precision($symbol, $cost / $filled);
        $fee = array (
            'cost' => floatval ($order['paid_fee'][0]),
            'currency' => $order['paid_fee'][1],
        );
        return array (
            'id' => $id,
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
            'fee' => $fee,
            'info' => $order,
        );
    }

    public function is_fiat ($code) {
        $fiats = array (
            'ARS' => true,
            'CLP' => true,
            'COP' => true,
            'PEN' => true,
        );
        return $this->safe_value($fiats, $code, false);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        if ($this->is_fiat ($code)) {
            throw new NotSupported ($this->id . ' fetchDepositAddress() for fiat ' . $code . ' is not supported');
        }
        $response = $this->privateGetCurrenciesCurrencyReceiveAddresses (array_merge (array (
            'currency' => $currency['id'],
        ), $params));
        $receiveAddresses = $response['receive_addresses'];
        $addressPool = array ();
        for ($i = 1; $i < count ($receiveAddresses); $i++) {
            $receiveAddress = $receiveAddresses[$i];
            if ($receiveAddress['ready']) {
                $address = $receiveAddress['address'];
                $this->check_address($address);
                $addressPool[] = $address;
            }
        }
        $addressPoolLength = is_array ($addressPool) ? count ($addressPool) : 0;
        if ($addressPoolLength < 1) {
            throw new AddressPending ($this->name . ' => there are no addresses ready for receiving ' . $code . ', retry again later)');
        }
        $address = $addressPool[0];
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $receiveAddresses,
        );
    }

    public function create_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        if ($this->is_fiat ($code))
            throw new NotSupported ($this->name . ' => fiat fetchDepositAddress() for ' . $code . ' is not supported');
        $response = $this->privatePostCurrenciesCurrencyReceiveAddresses (array_merge (array (
            'currency' => $currency['id'],
        ), $params));
        $address = $this->safe_string($response['receive_address'], 'address');  // the creation is async and returns a null $address, returns only the id
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $response,
        );
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'rejected' => 'failed',
            'confirmed' => 'ok',
            'anulled' => 'canceled',
            'retained' => 'canceled',
            'pending_confirmation' => 'pending',
        );
        return (is_array ($statuses) && array_key_exists ($status, $statuses)) ? $statuses[$status] : $status;
    }

    public function parse_transaction ($transaction, $currency = null) {
        $id = $this->safe_string($transaction, 'id');
        $timestamp = $this->parse8601 ($this->safe_string($transaction, 'created_at'));
        $code = null;
        $currencyId = null;
        if ($currency === null) {
            $currencyId = $this->safe_string($transaction, 'currency');
            $currency = $this->safe_value($this->currencies_by_id, $currencyId);
        }
        if ($currency !== null) {
            $code = $currency['code'];
        } else {
            $code = $this->common_currency_code($currencyId);
        }
        $amount = floatval ($transaction['amount'][0]);
        $fee = floatval ($transaction['fee'][0]);
        $feeCurrency = $transaction['fee'][1];
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'state'));
        $type = (is_array ($transaction) && array_key_exists ('deposit_data', $transaction)) ? 'deposit' : 'withdrawal';
        $data = $this->safe_value($transaction, $type . '_data', array ());
        $address = $this->safe_value($data, 'target_address');
        $txid = $this->safe_string($data, 'tx_hash');
        $updated = $this->parse8601 ($this->safe_string($data, 'updated_at'));
        return array (
            'info' => $transaction,
            'id' => $id,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'address' => $address,
            'type' => $type,
            'amount' => $amount,
            'currency' => $code,
            'status' => $status,
            'updated' => $updated,
            'fee' => array (
                'cost' => $fee,
                'rate' => $feeCurrency,
            ),
        );
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($code === null)
            throw new ExchangeError ($this->name . ' => fetchDeposits() requires a $currency $code argument');
        $currency = $this->currency ($code);
        $response = $this->privateGetCurrenciesCurrencyDeposits (array_merge (array (
            'currency' => $currency['id'],
            'per' => $limit,
        ), $params));
        $deposits = $response['deposits'];
        return $this->parseTransactions ($deposits, $currency, $since, $limit);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($code === null)
            throw new ExchangeError ($this->name . ' => fetchDeposits() requires a $currency $code argument');
        $currency = $this->currency ($code);
        $response = $this->privateGetCurrenciesCurrencyWithdrawals (array_merge (array (
            'currency' => $currency['id'],
            'per' => $limit,
        ), $params));
        $withdrawals = $response['withdrawals'];
        return $this->parseTransactions ($withdrawals, $currency, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privatePostCurrenciesCurrencyWithdrawals (array_merge (array (
            'currency' => $currency['id'],
            'amount' => $amount,
            'withdrawal_data' => array (
                'target_address' => $address,
            ),
        ), $params));
        $withdrawal = $response['withdrawal'];
        return $this->parse_transaction ($withdrawal);
    }

    public function nonce () {
        return $this->microseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($query) {
            if ($method === 'GET') {
                $request .= '?' . $this->urlencode ($query);
            } else {
                $body = $this->json ($query);
            }
        }
        $url = $this->urls['api'] . '/' . $this->version . '/' . $request;
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $components = array ( $method, '/$api/' . $this->version . '/' . $request );
            if ($body) {
                $base64_body = base64_encode ($this->encode ($body));
                $components[] = $this->decode ($base64_body);
            }
            $components[] = $nonce;
            $message = implode (' ', $components);
            $signature = $this->hmac ($this->encode ($message), $this->encode ($this->secret), 'sha384');
            $headers = array (
                'X-SBTC-APIKEY' => $this->apiKey,
                'X-SBTC-SIGNATURE' => $signature,
                'X-SBTC-NONCE' => $nonce,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (!$this->is_json_encoded_object($body)) {
            return; // fallback to default error handler
        }
        if ($code >= 400) {
            $response = json_decode ($body, $as_associative_array = true);
            $errorCode = $this->safe_string($response, 'code');
            $message = $this->safe_string($response, 'message', $body);
            $feedback = $this->name . ' => ' . $message;
            $exceptions = $this->exceptions;
            if ($errorCode !== null) {
                if (is_array ($exceptions) && array_key_exists ($errorCode, $exceptions)) {
                    throw new $exceptions[$errorCode] ($feedback);
                } else {
                    throw new ExchangeError ($feedback);
                }
            }
        }
    }
}
