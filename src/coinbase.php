<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinbase extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinbase',
            'name' => 'Coinbase',
            'countries' => array ( 'US' ),
            'rateLimit' => 400, // 10k calls per hour
            'version' => 'v2',
            'userAgent' => $this->userAgents['chrome'],
            'headers' => array (
                'CB-VERSION' => '2018-05-30',
            ),
            'has' => array (
                'CORS' => true,
                'cancelOrder' => false,
                'createDepositAddress' => false,
                'createOrder' => false,
                'deposit' => false,
                'fetchBalance' => true,
                'fetchClosedOrders' => false,
                'fetchCurrencies' => true,
                'fetchDepositAddress' => false,
                'fetchMarkets' => false,
                'fetchMyTrades' => false,
                'fetchOHLCV' => false,
                'fetchOpenOrders' => false,
                'fetchOrder' => false,
                'fetchOrderBook' => false,
                'fetchL2OrderBook' => false,
                'fetchOrders' => false,
                'fetchTicker' => true,
                'fetchTickers' => false,
                'fetchBidsAsks' => false,
                'fetchTrades' => false,
                'withdraw' => false,
                'fetchTransactions' => false,
                'fetchDeposits' => true,
                'fetchWithdrawals' => true,
                'fetchMySells' => true,
                'fetchMyBuys' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/40811661-b6eceae2-653a-11e8-829e-10bfadb078cf.jpg',
                'api' => 'https://api.coinbase.com',
                'www' => 'https://www.coinbase.com',
                'doc' => 'https://developers.coinbase.com/api/v2',
                'fees' => 'https://support.coinbase.com/customer/portal/articles/2109597-buy-sell-bank-transfer-fees',
                'referral' => 'https://www.coinbase.com/join/58cbe25a355148797479dbd2',
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currencies',
                        'time',
                        'exchange-rates',
                        'users/{user_id}',
                        'prices/{symbol}/buy',
                        'prices/{symbol}/sell',
                        'prices/{symbol}/spot',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{account_id}',
                        'accounts/{account_id}/addresses',
                        'accounts/{account_id}/addresses/{address_id}',
                        'accounts/{account_id}/addresses/{address_id}/transactions',
                        'accounts/{account_id}/transactions',
                        'accounts/{account_id}/transactions/{transaction_id}',
                        'accounts/{account_id}/buys',
                        'accounts/{account_id}/buys/{buy_id}',
                        'accounts/{account_id}/sells',
                        'accounts/{account_id}/sells/{sell_id}',
                        'accounts/{account_id}/deposits',
                        'accounts/{account_id}/deposits/{deposit_id}',
                        'accounts/{account_id}/withdrawals',
                        'accounts/{account_id}/withdrawals/{withdrawal_id}',
                        'payment-methods',
                        'payment-methods/{payment_method_id}',
                        'user',
                        'user/auth',
                    ),
                    'post' => array (
                        'accounts',
                        'accounts/{account_id}/primary',
                        'accounts/{account_id}/addresses',
                        'accounts/{account_id}/transactions',
                        'accounts/{account_id}/transactions/{transaction_id}/complete',
                        'accounts/{account_id}/transactions/{transaction_id}/resend',
                        'accounts/{account_id}/buys',
                        'accounts/{account_id}/buys/{buy_id}/commit',
                        'accounts/{account_id}/sells',
                        'accounts/{account_id}/sells/{sell_id}/commit',
                        'accounts/{account_id}/deposists',
                        'accounts/{account_id}/deposists/{deposit_id}/commit',
                        'accounts/{account_id}/withdrawals',
                        'accounts/{account_id}/withdrawals/{withdrawal_id}/commit',
                    ),
                    'put' => array (
                        'accounts/{account_id}',
                        'user',
                    ),
                    'delete' => array (
                        'accounts/{id}',
                        'accounts/{account_id}/transactions/{transaction_id}',
                    ),
                ),
            ),
            'exceptions' => array (
                'two_factor_required' => '\\ccxt\\AuthenticationError', // 402 When sending money over 2fa limit
                'param_required' => '\\ccxt\\ExchangeError', // 400 Missing parameter
                'validation_error' => '\\ccxt\\ExchangeError', // 400 Unable to validate POST/PUT
                'invalid_request' => '\\ccxt\\ExchangeError', // 400 Invalid request
                'personal_details_required' => '\\ccxt\\AuthenticationError', // 400 User’s personal detail required to complete this request
                'identity_verification_required' => '\\ccxt\\AuthenticationError', // 400 Identity verification is required to complete this request
                'jumio_verification_required' => '\\ccxt\\AuthenticationError', // 400 Document verification is required to complete this request
                'jumio_face_match_verification_required' => '\\ccxt\\AuthenticationError', // 400 Document verification including face match is required to complete this request
                'unverified_email' => '\\ccxt\\AuthenticationError', // 400 User has not verified their email
                'authentication_error' => '\\ccxt\\AuthenticationError', // 401 Invalid auth (generic)
                'invalid_token' => '\\ccxt\\AuthenticationError', // 401 Invalid Oauth token
                'revoked_token' => '\\ccxt\\AuthenticationError', // 401 Revoked Oauth token
                'expired_token' => '\\ccxt\\AuthenticationError', // 401 Expired Oauth token
                'invalid_scope' => '\\ccxt\\AuthenticationError', // 403 User hasn’t authenticated necessary scope
                'not_found' => '\\ccxt\\ExchangeError', // 404 Resource not found
                'rate_limit_exceeded' => '\\ccxt\\DDoSProtection', // 429 Rate limit exceeded
                'internal_server_error' => '\\ccxt\\ExchangeError', // 500 Internal server error
            ),
            'markets' => array (
                'BTC/USD' => array( 'id' => 'btc-usd', 'symbol' => 'BTC/USD', 'base' => 'BTC', 'quote' => 'USD' ),
                'LTC/USD' => array( 'id' => 'ltc-usd', 'symbol' => 'LTC/USD', 'base' => 'LTC', 'quote' => 'USD' ),
                'ETH/USD' => array( 'id' => 'eth-usd', 'symbol' => 'ETH/USD', 'base' => 'ETH', 'quote' => 'USD' ),
                'BCH/USD' => array( 'id' => 'bch-usd', 'symbol' => 'BCH/USD', 'base' => 'BCH', 'quote' => 'USD' ),
            ),
            'options' => array (
                'accounts' => array (
                    'wallet',
                    'fiat',
                    // 'vault',
                ),
            ),
        ));
    }

    public function fetch_time ($params = array ()) {
        $response = $this->publicGetTime ($params);
        $data = $this->safe_value($response, 'data', array());
        return $this->parse8601 ($this->safe_string($data, 'iso'));
    }

    public function fetch_accounts ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetAccounts ($params);
        return $response['data'];
    }

    public function fetch_my_sells ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // they don't have an endpoint for all historical trades
        $accountId = $this->safe_string_2($params, 'account_id', 'accountId');
        if ($accountId === null) {
            throw new ArgumentsRequired($this->id . ' fetchMyTrades requires an account_id or $accountId extra parameter, use fetchAccounts or loadAccounts to get ids of all your accounts.');
        }
        $this->load_markets();
        $query = $this->omit ($params, array ( 'account_id', 'accountId' ));
        $sells = $this->privateGetAccountsAccountIdSells (array_merge (array (
            'account_id' => $accountId,
        ), $query));
        return $this->parse_trades($sells['data'], null, $since, $limit);
    }

    public function fetch_my_buys ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // they don't have an endpoint for all historical trades
        $accountId = $this->safe_string_2($params, 'account_id', 'accountId');
        if ($accountId === null) {
            throw new ArgumentsRequired($this->id . ' fetchMyTrades requires an account_id or $accountId extra parameter, use fetchAccounts or loadAccounts to get ids of all your accounts.');
        }
        $this->load_markets();
        $query = $this->omit ($params, array ( 'account_id', 'accountId' ));
        $buys = $this->privateGetAccountsAccountIdBuys (array_merge (array (
            'account_id' => $accountId,
        ), $query));
        return $this->parse_trades($buys['data'], null, $since, $limit);
    }

    public function fetch_transactions_with_method ($method, $code = null, $since = null, $limit = null, $params = array ()) {
        $accountId = $this->safe_string_2($params, 'account_id', 'accountId');
        if ($accountId === null) {
            throw new ArgumentsRequired($this->id . ' fetchTransactionsWithMethod requires an account_id or $accountId extra parameter, use fetchAccounts or loadAccounts to get ids of all your accounts.');
        }
        $this->load_markets();
        $query = $this->omit ($params, array ( 'account_id', 'accountId' ));
        $response = $this->$method (array_merge (array (
            'account_id' => $accountId,
        ), $query));
        return $this->parseTransactions ($response['data'], null, $since, $limit);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_with_method ('privateGetAccountsAccountIdWithdrawals', $code, $since, $limit, $params);
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_with_method ('privateGetAccountsAccountIdDeposits', $code, $since, $limit, $params);
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'created' => 'pending',
            'completed' => 'ok',
            'canceled' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_transaction ($transaction, $market = null) {
        //
        //    DEPOSIT
        //        $id => '406176b1-92cf-598f-ab6e-7d87e4a6cac1',
        //        $status => 'completed',
        //        payment_method => [Object],
        //        $transaction => [Object],
        //        user_reference => 'JQKBN85B',
        //        created_at => '2018-10-01T14:58:21Z',
        //        updated_at => '2018-10-01T17:57:27Z',
        //        resource => 'deposit',
        //        resource_path => '/v2/accounts/7702be4f-de96-5f08-b13b-32377c449ecf/deposits/406176b1-92cf-598f-ab6e-7d87e4a6cac1',
        //        $committed => true,
        //        payout_at => '2018-10-01T14:58:34Z',
        //        instant => true,
        //        $fee => [Object],
        //        $amount => [Object],
        //        subtotal => [Object],
        //        hold_until => '2018-10-04T07:00:00Z',
        //        hold_days => 3
        //
        //    WITHDRAWAL
        //       {
        //           "$id" => "67e0eaec-07d7-54c4-a72c-2e92826897df",
        //           "$status" => "completed",
        //           "payment_method" => array (
        //             "$id" => "83562370-3e5c-51db-87da-752af5ab9559",
        //             "resource" => "payment_method",
        //             "resource_path" => "/v2/payment-methods/83562370-3e5c-51db-87da-752af5ab9559"
        //           ),
        //           "$transaction" => array (
        //             "$id" => "441b9494-b3f0-5b98-b9b0-4d82c21c252a",
        //             "resource" => "$transaction",
        //             "resource_path" => "/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/transactions/441b9494-b3f0-5b98-b9b0-4d82c21c252a"
        //           ),
        //           "$amount" => array (
        //             "$amount" => "10.00",
        //             "$currency" => "USD"
        //           ),
        //           "subtotal" => array (
        //             "$amount" => "10.00",
        //             "$currency" => "USD"
        //           ),
        //           "created_at" => "2015-01-31T20:49:02Z",
        //           "updated_at" => "2015-02-11T16:54:02-08:00",
        //           "resource" => "withdrawal",
        //           "resource_path" => "/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/withdrawals/67e0eaec-07d7-54c4-a72c-2e92826897df",
        //           "$committed" => true,
        //           "$fee" => array (
        //             "$amount" => "0.00",
        //             "$currency" => "USD"
        //           ),
        //           "payout_at" => "2015-02-18T16:54:00-08:00"
        //         }
        $amountObject = $this->safe_value($transaction, 'amount', array());
        $feeObject = $this->safe_value($transaction, 'fee', array());
        $id = $this->safe_string($transaction, 'id');
        $timestamp = $this->parse8601 ($this->safe_value($transaction, 'created_at'));
        $updated = $this->parse8601 ($this->safe_value($transaction, 'updated_at'));
        $orderId = null;
        $type = $this->safe_string($transaction, 'resource');
        $amount = $this->safe_float($amountObject, 'amount');
        $currencyId = $this->safe_string($amountObject, 'currency');
        $currency = $this->common_currency_code($currencyId);
        $feeCost = $this->safe_float($feeObject, 'amount');
        $feeCurrencyId = $this->safe_string($feeObject, 'currency');
        $feeCurrency = $this->common_currency_code($feeCurrencyId);
        $fee = array (
            'cost' => $feeCost,
            'currency' => $feeCurrency,
        );
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'status'));
        if ($status === null) {
            $committed = $this->safe_value($transaction, 'committed');
            $status = $committed ? 'ok' : 'pending';
        }
        return array (
            'info' => $transaction,
            'id' => $id,
            'txid' => $id,
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'address' => null,
            'tag' => null,
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'updated' => $updated,
            'fee' => $fee,
        );
    }

    public function parse_trade ($trade, $market = null) {
        //
        //     {
        //       "$id" => "67e0eaec-07d7-54c4-a72c-2e92826897df",
        //       "status" => "completed",
        //       "payment_method" => array (
        //         "$id" => "83562370-3e5c-51db-87da-752af5ab9559",
        //         "resource" => "payment_method",
        //         "resource_path" => "/v2/payment-methods/83562370-3e5c-51db-87da-752af5ab9559"
        //       ),
        //       "transaction" => array (
        //         "$id" => "441b9494-b3f0-5b98-b9b0-4d82c21c252a",
        //         "resource" => "transaction",
        //         "resource_path" => "/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/transactions/441b9494-b3f0-5b98-b9b0-4d82c21c252a"
        //       ),
        //       "$amount" => array (
        //         "$amount" => "1.00000000",
        //         "currency" => "BTC"
        //       ),
        //       "total" => array (
        //         "$amount" => "10.25",
        //         "currency" => "USD"
        //       ),
        //       "subtotal" => array (
        //         "$amount" => "10.10",
        //         "currency" => "USD"
        //       ),
        //       "created_at" => "2015-01-31T20:49:02Z",
        //       "updated_at" => "2015-02-11T16:54:02-08:00",
        //       "resource" => "buy",
        //       "resource_path" => "/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/buys/67e0eaec-07d7-54c4-a72c-2e92826897df",
        //       "committed" => true,
        //       "instant" => false,
        //       "$fee" => array (
        //         "$amount" => "0.15",
        //         "currency" => "USD"
        //       ),
        //       "payout_at" => "2015-02-18T16:54:00-08:00"
        //     }
        //
        $symbol = null;
        $totalObject = $this->safe_value($trade, 'total', array());
        $amountObject = $this->safe_value($trade, 'amount', array());
        $subtotalObject = $this->safe_value($trade, 'subtotal', array());
        $feeObject = $this->safe_value($trade, 'fee', array());
        $id = $this->safe_string($trade, 'id');
        $timestamp = $this->parse8601 ($this->safe_value($trade, 'created_at'));
        if ($market === null) {
            $baseId = $this->safe_string($totalObject, 'currency');
            $quoteId = $this->safe_string($amountObject, 'currency');
            if (($baseId !== null) && ($quoteId !== null)) {
                $base = $this->common_currency_code($baseId);
                $quote = $this->common_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        $orderId = null;
        $side = $this->safe_string($trade, 'resource');
        $type = null;
        $cost = $this->safe_float($subtotalObject, 'amount');
        $amount = $this->safe_float($amountObject, 'amount');
        $price = null;
        if ($cost !== null) {
            if ($amount !== null) {
                $price = $cost / $amount;
            }
        }
        $feeCost = $this->safe_float($feeObject, 'amount');
        $feeCurrencyId = $this->safe_string($feeObject, 'currency');
        $feeCurrency = $this->common_currency_code($feeCurrencyId);
        $fee = array (
            'cost' => $feeCost,
            'currency' => $feeCurrency,
        );
        return array (
            'info' => $trade,
            'id' => $id,
            'order' => $orderId,
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

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetCurrencies ($params);
        $currencies = $response['data'];
        $result = array();
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $id = $currency['id'];
            $name = $currency['name'];
            $code = $this->common_currency_code($id);
            $minimum = $this->safe_float($currency, 'min_size');
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency, // the original payload
                'name' => $name,
                'active' => true,
                'fee' => null,
                'precision' => null,
                'limits' => array (
                    'amount' => array (
                        'min' => $minimum,
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'withdraw' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $timestamp = $this->seconds ();
        $market = $this->market ($symbol);
        $request = array_merge (array (
            'symbol' => $market['id'],
        ), $params);
        $buy = $this->publicGetPricesSymbolBuy ($request);
        $sell = $this->publicGetPricesSymbolSell ($request);
        $spot = $this->publicGetPricesSymbolSpot ($request);
        $ask = $this->safe_float($buy['data'], 'amount');
        $bid = $this->safe_float($sell['data'], 'amount');
        $last = $this->safe_float($spot['data'], 'amount');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'bid' => $bid,
            'ask' => $ask,
            'last' => $last,
            'high' => null,
            'low' => null,
            'bidVolume' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => null,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => null,
            'info' => array (
                'buy' => $buy,
                'sell' => $sell,
                'spot' => $spot,
            ),
        );
    }

    public function fetch_balance ($params = array ()) {
        $response = $this->privateGetAccounts ();
        $balances = $response['data'];
        $accounts = $this->safe_value($params, 'type', $this->options['accounts']);
        $result = array( 'info' => $response );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            if ($this->in_array($balance['type'], $accounts)) {
                $currencyId = $balance['balance']['currency'];
                $code = $currencyId;
                if (is_array($this->currencies_by_id) && array_key_exists($currencyId, $this->currencies_by_id))
                    $code = $this->currencies_by_id[$currencyId]['code'];
                $total = $this->safe_float($balance['balance'], 'amount');
                $free = $total;
                $used = null;
                if (is_array($result) && array_key_exists($code, $result)) {
                    $result[$code]['free'] .= $total;
                    $result[$code]['total'] .= $total;
                } else {
                    $account = array (
                        'free' => $free,
                        'used' => $used,
                        'total' => $total,
                    );
                    $result[$code] = $account;
                }
            }
        }
        return $this->parse_balance($result);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($method === 'GET') {
            if ($query)
                $request .= '?' . $this->urlencode ($query);
        }
        $url = $this->urls['api'] . '/' . $this->version . $request;
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
            $what = $nonce . $method . '/' . $this->version . $request . $payload;
            $signature = $this->hmac ($this->encode ($what), $this->encode ($this->secret));
            $headers = array (
                'CB-ACCESS-KEY' => $this->apiKey,
                'CB-ACCESS-SIGN' => $signature,
                'CB-ACCESS-TIMESTAMP' => $nonce,
                'Content-Type' => 'application/json',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $feedback = $this->id . ' ' . $body;
            //
            //    array("error" => "invalid_request", "error_description" => "The request is missing a required parameter, includes an unsupported parameter value, or is otherwise malformed.")
            //
            // or
            //
            //    {
            //      "$errors" => array (
            //        {
            //          "id" => "not_found",
            //          "message" => "Not found"
            //        }
            //      )
            //    }
            //
            $exceptions = $this->exceptions;
            $errorCode = $this->safe_string($response, 'error');
            if ($errorCode !== null) {
                if (is_array($exceptions) && array_key_exists($errorCode, $exceptions)) {
                    throw new $exceptions[$errorCode]($feedback);
                } else {
                    throw new ExchangeError($feedback);
                }
            }
            $errors = $this->safe_value($response, 'errors');
            if ($errors !== null) {
                if (gettype ($errors) === 'array' && count (array_filter (array_keys ($errors), 'is_string')) == 0) {
                    $numErrors = is_array ($errors) ? count ($errors) : 0;
                    if ($numErrors > 0) {
                        $errorCode = $this->safe_string($errors[0], 'id');
                        if ($errorCode !== null) {
                            if (is_array($exceptions) && array_key_exists($errorCode, $exceptions)) {
                                throw new $exceptions[$errorCode]($feedback);
                            } else {
                                throw new ExchangeError($feedback);
                            }
                        }
                    }
                }
            }
            $data = $this->safe_value($response, 'data');
            if ($data === null)
                throw new ExchangeError($this->id . ' failed due to a malformed $response ' . $this->json ($response));
        }
    }
}
