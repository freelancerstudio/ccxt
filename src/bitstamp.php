<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\AuthenticationError;
use \ccxt\NotSupported;

class bitstamp extends Exchange {

    public function describe() {
        return array_replace_recursive(parent::describe (), array(
            'id' => 'bitstamp',
            'name' => 'Bitstamp',
            'countries' => array( 'GB' ),
            'rateLimit' => 1000,
            'version' => 'v2',
            'userAgent' => $this->userAgents['chrome'],
            'pro' => true,
            'has' => array(
                'CORS' => true,
                'fetchDepositAddress' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchMyTrades' => true,
                'fetchTransactions' => true,
                'fetchWithdrawals' => true,
                'withdraw' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/27786377-8c8ab57e-5fe9-11e7-8ea4-2b05b6bcceec.jpg',
                'api' => array(
                    'public' => 'https://www.bitstamp.net/api',
                    'private' => 'https://www.bitstamp.net/api',
                    'v1' => 'https://www.bitstamp.net/api',
                ),
                'www' => 'https://www.bitstamp.net',
                'doc' => 'https://www.bitstamp.net/api',
            ),
            'requiredCredentials' => array(
                'apiKey' => true,
                'secret' => true,
                'uid' => true,
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'order_book/{pair}/',
                        'ticker_hour/{pair}/',
                        'ticker/{pair}/',
                        'transactions/{pair}/',
                        'trading-pairs-info/',
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'balance/',
                        'balance/{pair}/',
                        'bch_withdrawal/',
                        'bch_address/',
                        'user_transactions/',
                        'user_transactions/{pair}/',
                        'open_orders/all/',
                        'open_orders/{pair}/',
                        'order_status/',
                        'cancel_order/',
                        'buy/{pair}/',
                        'buy/market/{pair}/',
                        'buy/instant/{pair}/',
                        'sell/{pair}/',
                        'sell/market/{pair}/',
                        'sell/instant/{pair}/',
                        'ltc_withdrawal/',
                        'ltc_address/',
                        'eth_withdrawal/',
                        'eth_address/',
                        'xrp_withdrawal/',
                        'xrp_address/',
                        'transfer-to-main/',
                        'transfer-from-main/',
                        'withdrawal-requests/',
                        'withdrawal/open/',
                        'withdrawal/status/',
                        'withdrawal/cancel/',
                        'liquidation_address/new/',
                        'liquidation_address/info/',
                    ),
                ),
                'v1' => array(
                    'post' => array(
                        'bitcoin_deposit_address/',
                        'unconfirmed_btc/',
                        'bitcoin_withdrawal/',
                        'ripple_withdrawal/',
                        'ripple_address/',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => true,
                    'percentage' => true,
                    'taker' => 0.5 / 100,
                    'maker' => 0.5 / 100,
                    'tiers' => array(
                        'taker' => [
                            [0, 0.5 / 100],
                            [20000, 0.25 / 100],
                            [100000, 0.24 / 100],
                            [200000, 0.22 / 100],
                            [400000, 0.20 / 100],
                            [600000, 0.15 / 100],
                            [1000000, 0.14 / 100],
                            [2000000, 0.13 / 100],
                            [4000000, 0.12 / 100],
                            [20000000, 0.11 / 100],
                            [20000001, 0.10 / 100],
                        ],
                        'maker' => [
                            [0, 0.5 / 100],
                            [20000, 0.25 / 100],
                            [100000, 0.24 / 100],
                            [200000, 0.22 / 100],
                            [400000, 0.20 / 100],
                            [600000, 0.15 / 100],
                            [1000000, 0.14 / 100],
                            [2000000, 0.13 / 100],
                            [4000000, 0.12 / 100],
                            [20000000, 0.11 / 100],
                            [20000001, 0.10 / 100],
                        ],
                    ),
                ),
                'funding' => array(
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(
                        'BTC' => 0,
                        'BCH' => 0,
                        'LTC' => 0,
                        'ETH' => 0,
                        'XRP' => 0,
                        'USD' => 25,
                        'EUR' => 0.90,
                    ),
                    'deposit' => array(
                        'BTC' => 0,
                        'BCH' => 0,
                        'LTC' => 0,
                        'ETH' => 0,
                        'XRP' => 0,
                        'USD' => 25,
                        'EUR' => 0,
                    ),
                ),
            ),
            'exceptions' => array(
                'exact' => array(
                    'No permission found' => '\\ccxt\\PermissionDenied',
                    'API key not found' => '\\ccxt\\AuthenticationError',
                    'IP address not allowed' => '\\ccxt\\PermissionDenied',
                    'Invalid nonce' => '\\ccxt\\InvalidNonce',
                    'Invalid signature' => '\\ccxt\\AuthenticationError',
                    'Authentication failed' => '\\ccxt\\AuthenticationError',
                    'Missing key, signature and nonce parameters' => '\\ccxt\\AuthenticationError',
                    'Your account is frozen' => '\\ccxt\\PermissionDenied',
                    'Please update your profile with your FATCA information, before using API.' => '\\ccxt\\PermissionDenied',
                    'Order not found' => '\\ccxt\\OrderNotFound',
                    'Price is more than 20% below market price.' => '\\ccxt\\InvalidOrder',
                ),
                'broad' => array(
                    'Minimum order size is' => '\\ccxt\\InvalidOrder', // Minimum order size is 5.0 EUR.
                    'Check your account balance for details.' => '\\ccxt\\InsufficientFunds', // You have only 0.00100000 BTC available. Check your account balance for details.
                    'Ensure this value has at least' => '\\ccxt\\InvalidAddress', // Ensure this value has at least 25 characters (it has 4).
                ),
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        $response = $this->publicGetTradingPairsInfo ($params);
        $result = array();
        for ($i = 0; $i < count($response); $i++) {
            $market = $response[$i];
            $name = $this->safe_string($market, 'name');
            list($base, $quote) = explode('/', $name);
            $baseId = strtolower($base);
            $quoteId = strtolower($quote);
            $base = $this->safe_currency_code($base);
            $quote = $this->safe_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $symbolId = $baseId . '_' . $quoteId;
            $id = $this->safe_string($market, 'url_symbol');
            $precision = array(
                'amount' => $market['base_decimals'],
                'price' => $market['counter_decimals'],
            );
            $parts = explode(' ', $market['minimum_order']);
            $cost = $parts[0];
            // list($cost, $currency) = explode(' ', $market['minimum_order']);
            $active = ($market['trading'] === 'Enabled');
            $result[] = array(
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'symbolId' => $symbolId,
                'info' => $market,
                'active' => $active,
                'precision' => $precision,
                'limits' => array(
                    'amount' => array(
                        'min' => pow(10, -$precision['amount']),
                        'max' => null,
                    ),
                    'price' => array(
                        'min' => pow(10, -$precision['price']),
                        'max' => null,
                    ),
                    'cost' => array(
                        'min' => floatval ($cost),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'pair' => $this->market_id($symbol),
        );
        $response = $this->publicGetOrderBookPair (array_merge($request, $params));
        //
        //     {
        //         "$timestamp" => "1583652948",
        //         "$microtimestamp" => "1583652948955826",
        //         "bids" => array(
        //             array( "8750.00", "1.33685271" ),
        //             array( "8749.39", "0.07700000" ),
        //             array( "8746.98", "0.07400000" ),
        //         )
        //         "asks" => array(
        //             array( "8754.10", "1.51995636" ),
        //             array( "8754.71", "1.40000000" ),
        //             array( "8754.72", "2.50000000" ),
        //         )
        //     }
        //
        $microtimestamp = $this->safe_integer($response, 'microtimestamp');
        $timestamp = intval ($microtimestamp / 1000);
        $orderbook = $this->parse_order_book($response, $timestamp);
        $orderbook['nonce'] = $microtimestamp;
        return $orderbook;
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $request = array(
            'pair' => $this->market_id($symbol),
        );
        $ticker = $this->publicGetTickerPair (array_merge($request, $params));
        $timestamp = $this->safe_timestamp($ticker, 'timestamp');
        $vwap = $this->safe_float($ticker, 'vwap');
        $baseVolume = $this->safe_float($ticker, 'volume');
        $quoteVolume = null;
        if ($baseVolume !== null && $vwap !== null) {
            $quoteVolume = $baseVolume * $vwap;
        }
        $last = $this->safe_float($ticker, 'last');
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => $this->safe_float($ticker, 'open'),
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

    public function get_currency_id_from_transaction($transaction) {
        //
        //     {
        //         "fee" => "0.00000000",
        //         "btc_usd" => "0.00",
        //         "datetime" => XXX,
        //         "usd" => 0.0,
        //         "btc" => 0.0,
        //         "eth" => "0.05000000",
        //         "type" => "0",
        //         "$id" => XXX,
        //         "eur" => 0.0
        //     }
        //
        $currencyId = $this->safe_string_lower($transaction, 'currency');
        if ($currencyId !== null) {
            return $currencyId;
        }
        $transaction = $this->omit($transaction, array(
            'fee',
            'price',
            'datetime',
            'type',
            'status',
            'id',
        ));
        $ids = is_array($transaction) ? array_keys($transaction) : array();
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            if (mb_strpos($id, '_') < 0) {
                $value = $this->safe_float($transaction, $id);
                if (($value !== null) && ($value !== 0)) {
                    return $id;
                }
            }
        }
        return null;
    }

    public function get_market_from_trade($trade) {
        $trade = $this->omit($trade, array(
            'fee',
            'price',
            'datetime',
            'tid',
            'type',
            'order_id',
            'side',
        ));
        $currencyIds = is_array($trade) ? array_keys($trade) : array();
        $numCurrencyIds = is_array($currencyIds) ? count($currencyIds) : 0;
        if ($numCurrencyIds > 2) {
            throw new ExchangeError($this->id . ' getMarketFromTrade too many keys => ' . $this->json($currencyIds) . ' in the $trade => ' . $this->json($trade));
        }
        if ($numCurrencyIds === 2) {
            $marketId = $currencyIds[0] . $currencyIds[1];
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                return $this->markets_by_id[$marketId];
            }
            $marketId = $currencyIds[1] . $currencyIds[0];
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                return $this->markets_by_id[$marketId];
            }
        }
        return null;
    }

    public function get_market_from_trades($trades) {
        $tradesBySymbol = $this->index_by($trades, 'symbol');
        $symbols = is_array($tradesBySymbol) ? array_keys($tradesBySymbol) : array();
        $numSymbols = is_array($symbols) ? count($symbols) : 0;
        if ($numSymbols === 1) {
            return $this->markets[$symbols[0]];
        }
        return null;
    }

    public function parse_trade($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     array(
        //         date => '1551814435',
        //         tid => '83581898',
        //         $price => '0.03532850',
        //         $type => '1',
        //         $amount => '0.85945907'
        //     ),
        //
        // fetchMyTrades, trades returned within fetchOrder (private)
        //
        //     {
        //         "usd" => "6.0134400000000000",
        //         "$price" => "4008.96000000",
        //         "datetime" => "2019-03-28 23:07:37.233599",
        //         "$fee" => "0.02",
        //         "btc" => "0.00150000",
        //         "tid" => 84452058,
        //         "$type" => 2
        //     }
        //
        // from fetchOrder:
        //    { $fee => '0.000019',
        //     $price => '0.00015803',
        //     datetime => '2018-01-07 10:45:34.132551',
        //     btc => '0.0079015000000000',
        //     tid => 42777395,
        //     $type => 2, //(0 - deposit; 1 - withdrawal; 2 - $market $trade) NOT buy/sell
        //     xrp => '50.00000000' }
        $id = $this->safe_string_2($trade, 'id', 'tid');
        $symbol = null;
        $side = null;
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $orderId = $this->safe_string($trade, 'order_id');
        $type = null;
        $cost = $this->safe_float($trade, 'cost');
        if ($market === null) {
            $keys = is_array($trade) ? array_keys($trade) : array();
            for ($i = 0; $i < count($keys); $i++) {
                if (mb_strpos($keys[$i], '_') !== false) {
                    $marketId = str_replace('_', '', $keys[$i]);
                    if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                        $market = $this->markets_by_id[$marketId];
                    }
                }
            }
            // if the $market is still not defined
            // try to deduce it from used $keys
            if ($market === null) {
                $market = $this->get_market_from_trade($trade);
            }
        }
        $feeCost = $this->safe_float($trade, 'fee');
        $feeCurrency = null;
        if ($market !== null) {
            $price = $this->safe_float($trade, $market['symbolId'], $price);
            $amount = $this->safe_float($trade, $market['baseId'], $amount);
            $cost = $this->safe_float($trade, $market['quoteId'], $cost);
            $feeCurrency = $market['quote'];
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_string_2($trade, 'date', 'datetime');
        if ($timestamp !== null) {
            if (mb_strpos($timestamp, ' ') !== false) {
                // iso8601
                $timestamp = $this->parse8601($timestamp);
            } else {
                // string unix epoch in seconds
                $timestamp = intval ($timestamp);
                $timestamp = $timestamp * 1000;
            }
        }
        // if it is a private $trade
        if (is_array($trade) && array_key_exists('id', $trade)) {
            if ($amount !== null) {
                if ($amount < 0) {
                    $side = 'sell';
                    $amount = -$amount;
                } else {
                    $side = 'buy';
                }
            }
        } else {
            $side = $this->safe_string($trade, 'type');
            if ($side === '1') {
                $side = 'sell';
            } else if ($side === '0') {
                $side = 'buy';
            }
        }
        if ($cost === null) {
            if ($price !== null) {
                if ($amount !== null) {
                    $cost = $price * $amount;
                }
            }
        }
        if ($cost !== null) {
            $cost = abs($cost);
        }
        $fee = null;
        if ($feeCost !== null) {
            $fee = array(
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        return array(
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => $type,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
            'time' => 'hour',
        );
        $response = $this->publicGetTransactionsPair (array_merge($request, $params));
        //
        //     array(
        //         array(
        //             date => '1551814435',
        //             tid => '83581898',
        //             price => '0.03532850',
        //             type => '1',
        //             amount => '0.85945907'
        //         ),
        //         array(
        //             date => '1551814434',
        //             tid => '83581896',
        //             price => '0.03532851',
        //             type => '1',
        //             amount => '11.34130961'
        //         ),
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $balance = $this->privatePostBalance ($params);
        $result = array( 'info' => $balance );
        $codes = is_array($this->currencies) ? array_keys($this->currencies) : array();
        for ($i = 0; $i < count($codes); $i++) {
            $code = $codes[$i];
            $currency = $this->currency($code);
            $currencyId = $currency['id'];
            $account = $this->account();
            $account['free'] = $this->safe_float($balance, $currencyId . '_available');
            $account['used'] = $this->safe_float($balance, $currencyId . '_reserved');
            $account['total'] = $this->safe_float($balance, $currencyId . '_balance');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $method = 'privatePost' . $this->capitalize($side);
        $request = array(
            'pair' => $market['id'],
            'amount' => $this->amount_to_precision($symbol, $amount),
        );
        if ($type === 'market') {
            $method .= 'Market';
        } else {
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $method .= 'Pair';
        $response = $this->$method (array_merge($request, $params));
        $order = $this->parse_order($response, $market);
        return array_merge($order, array(
            'type' => $type,
        ));
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $id,
        );
        return $this->privatePostCancelOrder (array_merge($request, $params));
    }

    public function parse_order_status($status) {
        $statuses = array(
            'In Queue' => 'open',
            'Open' => 'open',
            'Finished' => 'closed',
            'Canceled' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function fetch_order_status($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $id,
        );
        $response = $this->privatePostOrderStatus (array_merge($request, $params));
        return $this->parse_order_status($this->safe_string($response, 'status'));
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
        }
        $request = array( 'id' => $id );
        $response = $this->privatePostOrderStatus (array_merge($request, $params));
        //
        //     {
        //         "status" => "Finished",
        //         "$id" => 3047704374,
        //         "transactions" => array(
        //             {
        //                 "usd" => "6.0134400000000000",
        //                 "price" => "4008.96000000",
        //                 "datetime" => "2019-03-28 23:07:37.233599",
        //                 "fee" => "0.02",
        //                 "btc" => "0.00150000",
        //                 "tid" => 84452058,
        //                 "type" => 2
        //             }
        //         )
        //     }
        return $this->parse_order($response, $market);
    }

    public function fetch_my_trades($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $method = 'privatePostUserTransactions';
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['pair'] = $market['id'];
            $method .= 'Pair';
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->$method (array_merge($request, $params));
        $result = $this->filter_by($response, 'type', '2');
        return $this->parse_trades($result, $market, $since, $limit);
    }

    public function fetch_transactions($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privatePostUserTransactions (array_merge($request, $params));
        //
        //     array(
        //         array(
        //             "fee" => "0.00000000",
        //             "btc_usd" => "0.00",
        //             "id" => 1234567894,
        //             "usd" => 0,
        //             "btc" => 0,
        //             "datetime" => "2018-09-08 09:00:31",
        //             "type" => "1",
        //             "xrp" => "-20.00000000",
        //             "eur" => 0,
        //         ),
        //         array(
        //             "fee" => "0.00000000",
        //             "btc_usd" => "0.00",
        //             "id" => 1134567891,
        //             "usd" => 0,
        //             "btc" => 0,
        //             "datetime" => "2018-09-07 18:47:52",
        //             "type" => "0",
        //             "xrp" => "20.00000000",
        //             "eur" => 0,
        //         ),
        //     )
        //
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency($code);
        }
        $transactions = $this->filter_by_array($response, 'type', array( '0', '1' ), false);
        return $this->parse_transactions($transactions, $currency, $since, $limit);
    }

    public function fetch_withdrawals($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        if ($since !== null) {
            $request['timedelta'] = $this->milliseconds() - $since;
        }
        $response = $this->privatePostWithdrawalRequests (array_merge($request, $params));
        //
        //     array(
        //         array(
        //             status => 2,
        //             datetime => '2018-10-17 10:58:13',
        //             currency => 'BTC',
        //             amount => '0.29669259',
        //             address => 'aaaaa',
        //             type => 1,
        //             id => 111111,
        //             transaction_id => 'xxxx',
        //         ),
        //         array(
        //             status => 2,
        //             datetime => '2018-10-17 10:55:17',
        //             currency => 'ETH',
        //             amount => '1.11010664',
        //             address => 'aaaa',
        //             type => 16,
        //             id => 222222,
        //             transaction_id => 'xxxxx',
        //         ),
        //     )
        //
        return $this->parse_transactions($response, null, $since, $limit);
    }

    public function parse_transaction($transaction, $currency = null) {
        //
        // fetchTransactions
        //
        //     {
        //         "$fee" => "0.00000000",
        //         "btc_usd" => "0.00",
        //         "$id" => 1234567894,
        //         "usd" => 0,
        //         "btc" => 0,
        //         "datetime" => "2018-09-08 09:00:31",
        //         "$type" => "1",
        //         "xrp" => "-20.00000000",
        //         "eur" => 0,
        //     }
        //
        // fetchWithdrawals
        //
        //     {
        //         $status => 2,
        //         datetime => '2018-10-17 10:58:13',
        //         $currency => 'BTC',
        //         $amount => '0.29669259',
        //         $address => 'aaaaa',
        //         $type => 1,
        //         $id => 111111,
        //         transaction_id => 'xxxx',
        //     }
        //
        //     {
        //         "$id" => 3386432,
        //         "$type" => 14,
        //         "$amount" => "863.21332500",
        //         "$status" => 2,
        //         "$address" => "rE1sdh25BJQ3qFwngiTBwaq3zPGGYcrjp1?dt=1455",
        //         "$currency" => "XRP",
        //         "datetime" => "2018-01-05 15:27:55",
        //         "transaction_id" => "001743B03B0C79BA166A064AC0142917B050347B4CB23BA2AB4B91B3C5608F4C"
        //     }
        //
        $timestamp = $this->parse8601($this->safe_string($transaction, 'datetime'));
        $id = $this->safe_string($transaction, 'id');
        $currencyId = $this->get_currency_id_from_transaction($transaction);
        $code = $this->safe_currency_code($currencyId, $currency);
        $feeCost = $this->safe_float($transaction, 'fee');
        $feeCurrency = null;
        $amount = null;
        if (is_array($transaction) && array_key_exists('amount', $transaction)) {
            $amount = $this->safe_float($transaction, 'amount');
        } else if ($currency !== null) {
            $amount = $this->safe_float($transaction, $currency['id'], $amount);
            $feeCurrency = $currency['code'];
        } else if (($code !== null) && ($currencyId !== null)) {
            $amount = $this->safe_float($transaction, $currencyId, $amount);
            $feeCurrency = $code;
        }
        if ($amount !== null) {
            // withdrawals have a negative $amount
            $amount = abs($amount);
        }
        $status = 'ok';
        if (is_array($transaction) && array_key_exists('status', $transaction)) {
            $status = $this->parse_transaction_status($this->safe_string($transaction, 'status'));
        }
        $type = null;
        if (is_array($transaction) && array_key_exists('type', $transaction)) {
            // from fetchTransactions
            $rawType = $this->safe_string($transaction, 'type');
            if ($rawType === '0') {
                $type = 'deposit';
            } else if ($rawType === '1') {
                $type = 'withdrawal';
            }
        } else {
            // from fetchWithdrawals
            $type = 'withdrawal';
        }
        $txid = $this->safe_string($transaction, 'transaction_id');
        $tag = null;
        $address = $this->safe_string($transaction, 'address');
        if ($address !== null) {
            // dt (destination $tag) is embedded into the $address field
            $addressParts = explode('?dt=', $address);
            $numParts = is_array($addressParts) ? count($addressParts) : 0;
            if ($numParts > 1) {
                $address = $addressParts[0];
                $tag = $addressParts[1];
            }
        }
        $addressFrom = null;
        $addressTo = $address;
        $tagFrom = null;
        $tagTo = $tag;
        $fee = null;
        if ($feeCost !== null) {
            $fee = array(
                'currency' => $feeCurrency,
                'cost' => $feeCost,
                'rate' => null,
            );
        }
        return array(
            'info' => $transaction,
            'id' => $id,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'addressFrom' => $addressFrom,
            'addressTo' => $addressTo,
            'address' => $address,
            'tagFrom' => $tagFrom,
            'tagTo' => $tagTo,
            'tag' => $tag,
            'type' => $type,
            'amount' => $amount,
            'currency' => $code,
            'status' => $status,
            'updated' => null,
            'fee' => $fee,
        );
    }

    public function parse_transaction_status($status) {
        // withdrawals:
        // 0 (open), 1 (in process), 2 (finished), 3 (canceled) or 4 (failed).
        $statuses = array(
            '0' => 'pending', // Open
            '1' => 'pending', // In process
            '2' => 'ok', // Finished
            '3' => 'canceled', // Canceled
            '4' => 'failed', // Failed
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order($order, $market = null) {
        // from fetch $order:
        //   { $status => 'Finished',
        //     $id => 731693945,
        //     $transactions:
        //     array( { $fee => '0.000019',
        //         $price => '0.00015803',
        //         datetime => '2018-01-07 10:45:34.132551',
        //         btc => '0.0079015000000000',
        //         tid => 42777395,
        //         type => 2,
        //         xrp => '50.00000000' } ) }
        //
        // partially $filled $order:
        //   { "$id" => 468646390,
        //     "$status" => "Canceled",
        //     "$transactions" => [array(
        //         "eth" => "0.23000000",
        //         "$fee" => "0.09",
        //         "tid" => 25810126,
        //         "usd" => "69.8947000000000000",
        //         "type" => 2,
        //         "$price" => "303.89000000",
        //         "datetime" => "2017-11-11 07:22:20.710567"
        //     )]}
        //
        // from create $order response:
        //     {
        //         $price => '0.00008012',
        //         currency_pair => 'XRP/BTC',
        //         datetime => '2019-01-31 21:23:36',
        //         $amount => '15.00000000',
        //         type => '0',
        //         $id => '2814205012'
        //     }
        //
        $id = $this->safe_string($order, 'id');
        $side = $this->safe_string($order, 'type');
        if ($side !== null) {
            $side = ($side === '1') ? 'sell' : 'buy';
        }
        // there is no $timestamp from fetchOrder
        $timestamp = $this->parse8601($this->safe_string($order, 'datetime'));
        $lastTradeTimestamp = null;
        $symbol = null;
        $marketId = $this->safe_string_lower($order, 'currency_pair');
        if ($marketId !== null) {
            $marketId = str_replace('/', '', $marketId);
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            }
        }
        $amount = $this->safe_float($order, 'amount');
        $filled = 0.0;
        $trades = array();
        $transactions = $this->safe_value($order, 'transactions', array());
        $feeCost = null;
        $cost = null;
        $numTransactions = is_array($transactions) ? count($transactions) : 0;
        if ($numTransactions > 0) {
            $feeCost = 0.0;
            for ($i = 0; $i < $numTransactions; $i++) {
                $trade = $this->parse_trade(array_merge(array(
                    'order_id' => $id,
                    'side' => $side,
                ), $transactions[$i]), $market);
                $filled = $this->sum($filled, $trade['amount']);
                $feeCost = $this->sum($feeCost, $trade['fee']['cost']);
                if ($cost === null) {
                    $cost = 0.0;
                }
                $cost = $this->sum($cost, $trade['cost']);
                $trades[] = $trade;
            }
            $lastTradeTimestamp = $trades[$numTransactions - 1]['timestamp'];
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        if (($status === 'closed') && ($amount === null)) {
            $amount = $filled;
        }
        $remaining = null;
        if ($amount !== null) {
            $remaining = $amount - $filled;
        }
        $price = $this->safe_float($order, 'price');
        if ($market === null) {
            $market = $this->get_market_from_trades($trades);
        }
        $feeCurrency = null;
        if ($market !== null) {
            if ($symbol === null) {
                $symbol = $market['symbol'];
            }
            $feeCurrency = $market['quote'];
        }
        if ($cost === null) {
            if ($price !== null) {
                $cost = $price * $filled;
            }
        } else if ($price === null) {
            if ($filled > 0) {
                $price = $cost / $filled;
            }
        }
        $fee = null;
        if ($feeCost !== null) {
            if ($feeCurrency !== null) {
                $fee = array(
                    'cost' => $feeCost,
                    'currency' => $feeCurrency,
                );
            }
        }
        return array(
            'id' => $id,
            'datetime' => $this->iso8601($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'status' => $status,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => $trades,
            'fee' => $fee,
            'info' => $order,
        );
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $market = null;
        $this->load_markets();
        if ($symbol !== null) {
            $market = $this->market($symbol);
        }
        $response = $this->privatePostOpenOrdersAll ($params);
        //     array(
        //         {
        //             price => '0.00008012',
        //             currency_pair => 'XRP/BTC',
        //             datetime => '2019-01-31 21:23:36',
        //             amount => '15.00000000',
        //             type => '0',
        //             id => '2814205012',
        //         }
        //     )
        //
        $result = array();
        for ($i = 0; $i < count($response); $i++) {
            $order = $this->parse_order($response[$i], $market);
            $result[] = array_merge($order, array(
                'status' => 'open',
                'type' => 'limit',
            ));
        }
        if ($symbol === null) {
            return $this->filter_by_since_limit($result, $since, $limit);
        }
        return $this->filter_by_symbol_since_limit($result, $symbol, $since, $limit);
    }

    public function get_currency_name($code) {
        if ($code === 'BTC') {
            return 'bitcoin';
        }
        return strtolower($code);
    }

    public function is_fiat($code) {
        if ($code === 'USD') {
            return true;
        }
        if ($code === 'EUR') {
            return true;
        }
        return false;
    }

    public function fetch_deposit_address($code, $params = array ()) {
        if ($this->is_fiat($code)) {
            throw new NotSupported($this->id . ' fiat fetchDepositAddress() for ' . $code . ' is not implemented yet');
        }
        $name = $this->get_currency_name($code);
        $v1 = ($code === 'BTC');
        $method = $v1 ? 'v1' : 'private'; // $v1 or v2
        $method .= 'Post' . $this->capitalize($name);
        $method .= $v1 ? 'Deposit' : '';
        $method .= 'Address';
        $response = $this->$method ($params);
        if ($v1) {
            $response = json_decode($response, $as_associative_array = true);
        }
        $address = $v1 ? $response : $this->safe_string($response, 'address');
        $tag = $v1 ? null : $this->safe_string($response, 'destination_tag');
        $this->check_address($address);
        return array(
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function withdraw($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        if ($this->is_fiat($code)) {
            throw new NotSupported($this->id . ' fiat withdraw() for ' . $code . ' is not implemented yet');
        }
        $name = $this->get_currency_name($code);
        $request = array(
            'amount' => $amount,
            'address' => $address,
        );
        $v1 = ($code === 'BTC');
        $method = $v1 ? 'v1' : 'private'; // $v1 or v2
        $method .= 'Post' . $this->capitalize($name) . 'Withdrawal';
        if ($code === 'XRP') {
            if ($tag !== null) {
                $request['destination_tag'] = $tag;
            }
        }
        $response = $this->$method (array_merge($request, $params));
        return array(
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function nonce() {
        return $this->milliseconds();
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/';
        if ($api !== 'v1') {
            $url .= $this->version . '/';
        }
        $url .= $this->implode_params($path, $params);
        $query = $this->omit($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        } else {
            $this->check_required_credentials();
            $authVersion = $this->safe_value($this->options, 'auth', 'v2');
            if (($authVersion === 'v1') || ($api === 'v1')) {
                $nonce = (string) $this->nonce();
                $auth = $nonce . $this->uid . $this->apiKey;
                $signature = $this->encode($this->hmac($this->encode($auth), $this->encode($this->secret)));
                $query = array_merge(array(
                    'key' => $this->apiKey,
                    'signature' => strtoupper($signature),
                    'nonce' => $nonce,
                ), $query);
                $body = $this->urlencode($query);
                $headers = array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                );
            } else {
                $xAuth = 'BITSTAMP ' . $this->apiKey;
                $xAuthNonce = $this->uuid();
                $xAuthTimestamp = (string) $this->milliseconds();
                $xAuthVersion = 'v2';
                $contentType = '';
                $headers = array(
                    'X-Auth' => $xAuth,
                    'X-Auth-Nonce' => $xAuthNonce,
                    'X-Auth-Timestamp' => $xAuthTimestamp,
                    'X-Auth-Version' => $xAuthVersion,
                );
                if ($method === 'POST') {
                    if ($query) {
                        $body = $this->urlencode($query);
                        $contentType = 'application/x-www-form-urlencoded';
                        $headers['Content-Type'] = $contentType;
                    }
                }
                $authBody = $body ? $body : '';
                $auth = $xAuth . $method . str_replace('https://', '', $url) . $contentType . $xAuthNonce . $xAuthTimestamp . $xAuthVersion . $authBody;
                $signature = $this->encode($this->hmac($this->encode($auth), $this->encode($this->secret)));
                $headers['X-Auth-Signature'] = $signature;
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($httpCode, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        //
        //     array("$error" => "No permission found") // fetchDepositAddress returns this on apiKeys that don't have the permission required
        //     array("$status" => "$error", "$reason" => array("__all__" => ["Minimum order size is 5.0 EUR."]))
        //     reuse of a nonce gives => array( $status => 'error', $reason => 'Invalid nonce', $code => 'API0004' )
        $status = $this->safe_string($response, 'status');
        $error = $this->safe_value($response, 'error');
        if (($status === 'error') || ($error !== null)) {
            $errors = array();
            if (gettype($error) === 'string') {
                $errors[] = $error;
            } else if ($error !== null) {
                $keys = is_array($error) ? array_keys($error) : array();
                for ($i = 0; $i < count($keys); $i++) {
                    $key = $keys[$i];
                    $value = $this->safe_value($error, $key);
                    if (gettype($value) === 'array' && count(array_filter(array_keys($value), 'is_string')) == 0) {
                        $errors = $this->array_concat($errors, $value);
                    } else {
                        $errors[] = $value;
                    }
                }
            }
            $reason = $this->safe_value($response, 'reason', array());
            if (gettype($reason) === 'string') {
                $errors[] = $reason;
            } else {
                $all = $this->safe_value($reason, '__all__', array());
                for ($i = 0; $i < count($all); $i++) {
                    $errors[] = $all[$i];
                }
            }
            $code = $this->safe_string($response, 'code');
            if ($code === 'API0005') {
                throw new AuthenticationError($this->id . ' invalid signature, use the uid for the main account if you have subaccounts');
            }
            $feedback = $this->id . ' ' . $body;
            for ($i = 0; $i < count($errors); $i++) {
                $value = $errors[$i];
                $this->throw_exactly_matched_exception($this->exceptions['exact'], $value, $feedback);
                $this->throw_broadly_matched_exception($this->exceptions['broad'], $value, $feedback);
            }
            throw new ExchangeError($feedback);
        }
    }
}
