<?php

namespace ccxt;

use Exception as Exception; // a common import

class uex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'uex',
            'name' => 'UEX',
            'countries' => array ( 'SG', 'US' ),
            'version' => 'v1.0.3',
            'rateLimit' => 1000,
            'certified' => false,
            // new metainfo interface
            'has' => array (
                'CORS' => false,
                'fetchMyTrades' => true,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchDepositAddress' => true,
                'fetchDeposits' => true,
                'fetchWithdrawals' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1',
                '5m' => '5',
                '15m' => '15',
                '30m' => '30',
                '1h' => '60',
                '2h' => '120',
                '3h' => '180',
                '4h' => '240',
                '6h' => '360',
                '12h' => '720',
                '1d' => '1440',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/43999923-051d9884-9e1f-11e8-965a-76948cb17678.jpg',
                'api' => 'https://open-api.uex.com/open/api',
                'www' => 'https://www.uex.com',
                'doc' => 'https://download.uex.com/doc/UEX-API-English-1.0.3.pdf',
                'fees' => 'https://www.uex.com/footer/ufees.html',
                'referral' => 'https://www.uex.com/signup.html?code=VAGQLL',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'common/coins', // funding limits
                        'common/symbols',
                        'get_records', // ohlcvs
                        'get_ticker',
                        'get_trades',
                        'market_dept', // dept here is not a typo... they mean depth
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'deposit_address_list',
                        'withdraw_address_list',
                        'deposit_history',
                        'withdraw_history',
                        'user/account',
                        'market', // an assoc array of market ids to corresponding prices traded most recently (prices of last trades per market)
                        'order_info',
                        'new_order', // a list of currently open orders
                        'all_order',
                        'all_trade',
                    ),
                    'post' => array (
                        'create_order',
                        'cancel_order',
                        'create_withdraw',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.0010,
                    'taker' => 0.0010,
                ),
            ),
            'exceptions' => array (
                // descriptions from ↓ exchange
                // '0' => 'no error', // succeed
                '4' => '\\ccxt\\InsufficientFunds', // array("code":"4","msg":"余额不足:0E-16","data":null)
                '5' => '\\ccxt\\InvalidOrder', // fail to order array("code":"5","msg":"Price fluctuates more than1000.0%","data":null)
                '6' => '\\ccxt\\InvalidOrder', // the quantity value less than the minimum one array("code":"6","msg":"数量小于最小值:0.001","data":null)
                '7' => '\\ccxt\\InvalidOrder', // the quantity value more than the maximum one array("code":"7","msg":"数量大于最大值:10000","data":null)
                '8' => '\\ccxt\\InvalidOrder', // fail to cancel order
                '9' => '\\ccxt\\ExchangeError', // transaction be frozen
                '13' => '\\ccxt\\ExchangeError', // Sorry, the program made an error, please contact with the manager.
                '19' => '\\ccxt\\InsufficientFunds', // Available balance is insufficient.
                '22' => '\\ccxt\\OrderNotFound', // The order does not exist. array("code":"22","msg":"not exist order","data":null)
                '23' => '\\ccxt\\InvalidOrder', // Lack of parameters of numbers of transaction
                '24' => '\\ccxt\\InvalidOrder', // Lack of parameters of transaction price
                '100001' => '\\ccxt\\ExchangeError', // System is abnormal
                '100002' => '\\ccxt\\ExchangeNotAvailable', // Update System
                '100004' => '\\ccxt\\ExchangeError', // array("code":"100004","msg":"request parameter illegal","data":null)
                '100005' => '\\ccxt\\AuthenticationError', // array("code":"100005","msg":"request sign illegal","data":null)
                '100007' => '\\ccxt\\PermissionDenied', // illegal IP
                '110002' => '\\ccxt\\ExchangeError', // unknown currency code
                '110003' => '\\ccxt\\AuthenticationError', // fund password error
                '110004' => '\\ccxt\\AuthenticationError', // fund password error
                '110005' => '\\ccxt\\InsufficientFunds', // Available balance is insufficient.
                '110020' => '\\ccxt\\AuthenticationError', // Username does not exist.
                '110023' => '\\ccxt\\AuthenticationError', // Phone number is registered.
                '110024' => '\\ccxt\\AuthenticationError', // Email box is registered.
                '110025' => '\\ccxt\\PermissionDenied', // Account is locked by background manager
                '110032' => '\\ccxt\\PermissionDenied', // The user has no authority to do this operation.
                '110033' => '\\ccxt\\ExchangeError', // fail to recharge
                '110034' => '\\ccxt\\ExchangeError', // fail to withdraw
                '-100' => '\\ccxt\\ExchangeError', // array("code":"-100","msg":"Your request path is not exist or you can try method GET/POST.","data":null)
                '-1000' => '\\ccxt\\ExchangeNotAvailable', // array("msg":"System maintenance!","code":"-1000","data":null)
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
            ),
            'options' => array (
                'createMarketBuyOrderRequiresPrice' => true,
                'limits' => array (
                    'BTC/USDT' => array( 'amount' => array ( 'min' => 0.001 ), 'price' => array( 'min' => 0.01 )),
                    'ETH/USDT' => array( 'amount' => array ( 'min' => 0.001 ), 'price' => array( 'min' => 0.01 )),
                    'BCH/USDT' => array( 'amount' => array ( 'min' => 0.001 ), 'price' => array( 'min' => 0.01 )),
                    'ETH/BTC' => array( 'amount' => array ( 'min' => 0.001 ), 'price' => array( 'min' => 0.000001 )),
                    'BCH/BTC' => array( 'amount' => array ( 'min' => 0.001 ), 'price' => array( 'min' => 0.000001 )),
                    'LEEK/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'CTXC/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'COSM/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'MANA/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'LBA/BTC' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'OLT/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'DTA/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'KNT/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'REN/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'LBA/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'EXC/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'ZIL/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'RATING/ETH' => array( 'amount' => array ( 'min' => 100 ), 'price' => array( 'min' => 100 )),
                    'CENNZ/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                    'TTC/ETH' => array( 'amount' => array ( 'min' => 10 ), 'price' => array( 'min' => 10 )),
                ),
            ),
        ));
    }

    public function calculate_fee ($symbol, $type, $side, $amount, $price, $takerOrMaker = 'taker', $params = array ()) {
        $market = $this->markets[$symbol];
        $key = 'quote';
        $rate = $market[$takerOrMaker];
        $cost = floatval ($this->cost_to_precision($symbol, $amount * $rate));
        if ($side === 'sell') {
            $cost *= $price;
        } else {
            $key = 'base';
        }
        return array (
            'type' => $takerOrMaker,
            'currency' => $market[$key],
            'rate' => $rate,
            'cost' => floatval ($this->currency_to_precision($market[$key], $cost)),
        );
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetCommonSymbols ();
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => [ array (           $symbol => "btcusdt",
        //                       count_coin => "usdt",
        //                 amount_precision =>  3,
        //                        base_coin => "btc",
        //                  price_precision =>  2         ),
        //               array (           $symbol => "ethusdt",
        //                       count_coin => "usdt",
        //                 amount_precision =>  3,
        //                        base_coin => "eth",
        //                  price_precision =>  2         ),
        //               array (           $symbol => "ethbtc",
        //                       count_coin => "btc",
        //                 amount_precision =>  3,
        //                        base_coin => "eth",
        //                  price_precision =>  6        )]}
        //
        $result = array();
        $markets = $response['data'];
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['symbol'];
            $baseId = $market['base_coin'];
            $quoteId = $market['count_coin'];
            $base = strtoupper($baseId);
            $quote = strtoupper($quoteId);
            $base = $this->common_currency_code($base);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $market['amount_precision'],
                'price' => $market['price_precision'],
            );
            $active = true;
            $defaultLimits = $this->safe_value($this->options['limits'], $symbol, array());
            $limits = array_replace_recursive (array (
                'amount' => array (
                    'min' => null,
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
            ), $defaultLimits);
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
                'limits' => $limits,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetUserAccount ($params);
        //
        //     { $code =>   "0",
        //        msg =>   "suc",
        //       data => array ( total_asset =>   "0.00000000",
        //                 coin_list => [ array (      normal => "0.00000000",
        //                                btcValuatin => "0.00000000",
        //                                     locked => "0.00000000",
        //                                       coin => "usdt"        ),
        //                              array (      normal => "0.00000000",
        //                                btcValuatin => "0.00000000",
        //                                     locked => "0.00000000",
        //                                       coin => "btc"         ),
        //                              array (      normal => "0.00000000",
        //                                btcValuatin => "0.00000000",
        //                                     locked => "0.00000000",
        //                                       coin => "eth"         ),
        //                              array (      normal => "0.00000000",
        //                                btcValuatin => "0.00000000",
        //                                     locked => "0.00000000",
        //                                       coin => "ren"         )])}
        //
        $balances = $response['data']['coin_list'];
        $result = array( 'info' => $balances );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $balance['coin'];
            $code = strtoupper($currencyId);
            if (is_array($this->currencies_by_id) && array_key_exists($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            } else {
                $code = $this->common_currency_code($code);
            }
            $account = $this->account ();
            $free = floatval ($balance['normal']);
            $used = floatval ($balance['locked']);
            $total = $this->sum ($free, $used);
            $account['free'] = $free;
            $account['used'] = $used;
            $account['total'] = $total;
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarketDept (array_merge (array (
            'symbol' => $this->market_id($symbol),
            'type' => 'step0', // step1, step2 from most detailed to least detailed
        ), $params));
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => { tick => { asks => [ ["0.05824200", 9.77],
        //                               ["0.05830000", 7.81],
        //                               ["0.05832900", 8.59],
        //                               ["0.10000000", 0.001]  ],
        //                       bids => [ ["0.05780000", 8.25],
        //                               ["0.05775000", 8.12],
        //                               ["0.05773200", 8.57],
        //                               ["0.00010000", 0.79]   ],
        //                       time =>    1533412622463            } } }
        //
        $timestamp = $this->safe_integer($response['data']['tick'], 'time');
        return $this->parse_order_book($response['data']['tick'], $timestamp);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => { $symbol => "ETHBTC",
        //                 high =>  0.058426,
        //                  vol =>  19055.875,
        //                 $last =>  0.058019,
        //                  low =>  0.055802,
        //               $change =>  0.03437271,
        //                  buy => "0.05780000",
        //                 sell => "0.05824200",
        //                 time =>  1533413083184 } }
        //
        $timestamp = $this->safe_integer($ticker, 'time');
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($ticker, 'symbol');
            $marketId = strtolower($marketId);
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'last');
        $change = $this->safe_float($ticker, 'change');
        $percentage = $change * 100;
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
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetGetTicker (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => { $symbol => "ETHBTC",
        //                 high =>  0.058426,
        //                  vol =>  19055.875,
        //                 last =>  0.058019,
        //                  low =>  0.055802,
        //               change =>  0.03437271,
        //                  buy => "0.05780000",
        //                 sell => "0.05824200",
        //                 time =>  1533413083184 } }
        //
        return $this->parse_ticker($response['data'], $market);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // public fetchTrades
        //
        //   array (      $amount =>  0.88,
        //     create_time =>  1533414358000,
        //           $price =>  0.058019,
        //              $id =>  406531,
        //            type => "sell"          ),
        //
        // private fetchMyTrades, fetchOrder, fetchOpenOrders, fetchClosedOrders
        //
        //   {     volume => "0.010",
        //           $side => "SELL",
        //        feeCoin => "BTC",
        //          $price => "0.05816200",
        //            $fee => "0.00000029",
        //          ctime =>  1533616674000,
        //     deal_price => "0.00058162",
        //             $id =>  415779,
        //           type => "卖出",
        //         bid_id =>  3669539, // only in fetchMyTrades
        //         ask_id =>  3669583, // only in fetchMyTrades
        //   }
        //
        $timestamp = $this->safe_integer_2($trade, 'create_time', 'ctime');
        if ($timestamp === null) {
            $timestring = $this->safe_string($trade, 'created_at');
            if ($timestring !== null) {
                $timestamp = $this->parse8601 ('2018-' . $timestring . ':00Z');
            }
        }
        $side = $this->safe_string_2($trade, 'side', 'type');
        if ($side !== null) {
            $side = strtolower($side);
        }
        $id = $this->safe_string($trade, 'id');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float_2($trade, 'volume', 'amount');
        $cost = $this->safe_float($trade, 'deal_price');
        if ($cost === null) {
            if ($amount !== null) {
                if ($price !== null) {
                    $cost = $amount * $price;
                }
            }
        }
        $fee = null;
        $feeCost = $this->safe_float_2($trade, 'fee', 'deal_fee');
        if ($feeCost !== null) {
            $feeCurrency = $this->safe_string($trade, 'feeCoin');
            if ($feeCurrency !== null) {
                $currencyId = strtolower($feeCurrency);
                if (is_array($this->currencies_by_id) && array_key_exists($currencyId, $this->currencies_by_id)) {
                    $feeCurrency = $this->currencies_by_id[$currencyId]['code'];
                }
            }
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        $orderIdField = ($side === 'sell') ? 'ask_id' : 'bid_id';
        $orderId = $this->safe_string($trade, $orderIdField);
        return array (
            'id' => $id,
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
        $response = $this->publicGetGetTrades (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => array ( array (      amount =>  0.88,
        //                 create_time =>  1533414358000,
        //                       price =>  0.058019,
        //                          id =>  406531,
        //                        type => "sell"          ),
        //               array (      amount =>  4.88,
        //                 create_time =>  1533414331000,
        //                       price =>  0.058019,
        //                          id =>  406530,
        //                        type => "buy"           ),
        //               {      amount =>  0.5,
        //                 create_time =>  1533414311000,
        //                       price =>  0.058019,
        //                          id =>  406529,
        //                        type => "sell"          } ) }
        //
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1d', $since = null, $limit = null) {
        return [
            $ohlcv[0] * 1000, // timestamp
            $ohlcv[1], // open
            $ohlcv[2], // high
            $ohlcv[3], // low
            $ohlcv[4], // close
            $ohlcv[5], // volume
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'period' => $this->timeframes[$timeframe], // in minutes
        );
        $response = $this->publicGetGetRecords (array_merge ($request, $params));
        //
        //     { code => '0',
        //        msg => 'suc',
        //       data:
        //        array ( array ( 1533402420, 0.057833, 0.057833, 0.057833, 0.057833, 18.1 ),
        //          array ( 1533402480, 0.057833, 0.057833, 0.057833, 0.057833, 29.88 ),
        //          array ( 1533402540, 0.057833, 0.057833, 0.057833, 0.057833, 29.06 ) ) }
        //
        return $this->parse_ohlcvs($response['data'], $market, $timeframe, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type === 'market') {
            // for $market buy it requires the $amount of quote currency to spend
            if ($side === 'buy') {
                if ($this->options['createMarketBuyOrderRequiresPrice']) {
                    if ($price === null) {
                        throw new InvalidOrder($this->id . " createOrder() requires the $price argument with $market buy orders to calculate total order cost ($amount to spend), where cost = $amount * $price-> Supply a $price argument to createOrder() call if you want the cost to be calculated for you from $price and $amount, or, alternatively, add .options['createMarketBuyOrderRequiresPrice'] = false to supply the cost in the $amount argument (the exchange-specific behaviour)");
                    } else {
                        $amount = $amount * $price;
                    }
                }
            }
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $orderType = ($type === 'limit') ? '1' : '2';
        $orderSide = strtoupper($side);
        $amountToPrecision = $this->amount_to_precision($symbol, $amount);
        $request = array (
            'side' => $orderSide,
            'type' => $orderType,
            'symbol' => $market['id'],
            'volume' => $amountToPrecision,
            // An excerpt from their docs:
            // $side required Trading Direction
            // $type required pending order types，1:Limit-$price Delegation 2:Market- $price Delegation
            // volume required
            //     Purchase Quantity（polysemy，multiplex field）
            //     $type=1 => Quantity of buying and selling
            //     $type=2 => Buying represents gross $price, and selling represents total number
            //     Trading restriction user/me-user information
            // $price optional Delegation Price：$type=2：this parameter is no use.
            // fee_is_user_exchange_coin optional
            //     0，when making transactions with all platform currencies,
            //     this parameter represents whether to use them to pay
            //     fees or not and 0 is no, 1 is yes.
        );
        $priceToPrecision = null;
        if ($type === 'limit') {
            $priceToPrecision = $this->price_to_precision($symbol, $price);
            $request['price'] = $priceToPrecision;
        }
        $response = $this->privatePostCreateOrder (array_merge ($request, $params));
        //
        //     { code => '0',
        //        msg => 'suc',
        //       data => array( 'order_id' : 34343 ) }
        //
        $result = $this->parse_order($response['data'], $market);
        return array_merge ($result, array (
            'info' => $response,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'status' => 'open',
            'price' => floatval ($priceToPrecision),
            'amount' => floatval ($amountToPrecision),
        ));
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'order_id' => $id,
            'symbol' => $market['id'],
        );
        $response = $this->privatePostCancelOrder (array_merge ($request, $params));
        $order = $this->safe_value($response, 'data', array());
        return array_merge ($this->parse_order($order), array (
            'id' => $id,
            'symbol' => $symbol,
            'status' => 'canceled',
        ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '0' => 'open', // INIT(0,"primary order，untraded and not enter the market")
            '1' => 'open', // NEW_(1,"new order，untraded and enter the market ")
            '2' => 'closed', // FILLED(2,"complete deal")
            '3' => 'open', // PART_FILLED(3,"partial deal")
            '4' => 'canceled', // CANCELED(4,"already withdrawn")
            '5' => 'canceled', // PENDING_CANCEL(5,"pending withdrawak")
            '6' => 'canceled', // EXPIRED(6,"abnormal orders")
        );
        if (is_array($statuses) && array_key_exists($status, $statuses)) {
            return $statuses[$status];
        }
        return $status;
    }

    public function parse_order ($order, $market = null) {
        //
        // createOrder
        //
        //     array("order_id":34343)
        //
        // fetchOrder, fetchOpenOrders, fetchClosedOrders
        //
        //     {          $side =>   "BUY",
        //         total_price =>   "0.10000000",
        //          created_at =>    1510993841000,
        //           avg_price =>   "0.10000000",
        //           countCoin =>   "btc",
        //              source =>    1,
        //                type =>    1,
        //            side_msg =>   "买入",
        //              volume =>   "1.000",
        //               $price =>   "0.10000000",
        //          source_msg =>   "WEB",
        //          status_msg =>   "完全成交",
        //         deal_volume =>   "1.00000000",
        //                  $id =>    424,
        //       remain_volume =>   "0.00000000",
        //            baseCoin =>   "eth",
        //           $tradeList => array ( {     volume => "1.000",
        //                             feeCoin => "YLB",
        //                               $price => "0.10000000",
        //                                 $fee => "0.16431104",
        //                               ctime =>  1510996571195,
        //                          deal_price => "0.10000000",
        //                                  $id =>  306,
        //                                type => "买入"            } ),
        //              $status =>    2                                 }
        //
        // fetchOrder
        //
        //      { trade_list => array ( {     volume => "0.010",
        //                           feeCoin => "BTC",
        //                             $price => "0.05816200",
        //                               $fee => "0.00000029",
        //                             ctime =>  1533616674000,
        //                        deal_price => "0.00058162",
        //                                $id =>  415779,
        //                              type => "卖出"            } ),
        //        order_info => {          $side =>   "SELL",
        //                        total_price =>   "0.010",
        //                         created_at =>    1533616673000,
        //                          avg_price =>   "0.05816200",
        //                          countCoin =>   "btc",
        //                             source =>    3,
        //                               type =>    2,
        //                           side_msg =>   "卖出",
        //                             volume =>   "0.010",
        //                              $price =>   "0.00000000",
        //                         source_msg =>   "API",
        //                         status_msg =>   "完全成交",
        //                        deal_volume =>   "0.01000000",
        //                                 $id =>    3669583,
        //                      remain_volume =>   "0.00000000",
        //                           baseCoin =>   "eth",
        //                          $tradeList => array ( {     volume => "0.010",
        //                                            feeCoin => "BTC",
        //                                              $price => "0.05816200",
        //                                                $fee => "0.00000029",
        //                                              ctime =>  1533616674000,
        //                                         deal_price => "0.00058162",
        //                                                 $id =>  415779,
        //                                               type => "卖出"            } ),
        //                             $status =>    2                                 } }
        //
        $side = $this->safe_string($order, 'side');
        if ($side !== null)
            $side = strtolower($side);
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $symbol = null;
        if ($market === null) {
            $baseId = $this->safe_string($order, 'baseCoin');
            $quoteId = $this->safe_string($order, 'countCoin');
            $marketId = $baseId . $quoteId;
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                if (($baseId !== null) && ($quoteId !== null)) {
                    $base = strtoupper($baseId);
                    $quote = strtoupper($quoteId);
                    $base = $this->common_currency_code($base);
                    $quote = $this->common_currency_code($quote);
                    $symbol = $base . '/' . $quote;
                }
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer($order, 'created_at');
        if ($timestamp === null) {
            $timestring = $this->safe_string($order, 'created_at');
            if ($timestring !== null) {
                $timestamp = $this->parse8601 ('2018-' . $timestring . ':00Z');
            }
        }
        $lastTradeTimestamp = null;
        $fee = null;
        $average = $this->safe_float($order, 'avg_price');
        $price = $this->safe_float($order, 'price');
        if ($price === 0) {
            $price = $average;
        }
        $amount = $this->safe_float($order, 'volume');
        $filled = $this->safe_float($order, 'deal_volume');
        $remaining = $this->safe_float($order, 'remain_volume');
        $cost = $this->safe_float($order, 'total_price');
        $id = $this->safe_string_2($order, 'id', 'order_id');
        $trades = null;
        $tradeList = $this->safe_value($order, 'tradeList', array());
        $feeCurrencies = array();
        $feeCost = null;
        for ($i = 0; $i < count ($tradeList); $i++) {
            $trade = $this->parse_trade($tradeList[$i], $market);
            if ($feeCost === null) {
                $feeCost = 0;
            }
            $feeCost = $feeCost . $trade['fee']['cost'];
            $tradeFeeCurrency = $trade['fee']['currency'];
            $feeCurrencies[$tradeFeeCurrency] = $trade['fee']['cost'];
            if ($trades === null) {
                $trades = array();
            }
            $lastTradeTimestamp = $trade['timestamp'];
            $trades[] = array_merge ($trade, array (
                'order' => $id,
            ));
        }
        if ($feeCost !== null) {
            $feeCurrency = null;
            $keys = is_array($feeCurrencies) ? array_keys($feeCurrencies) : array();
            $numCurrencies = is_array ($keys) ? count ($keys) : 0;
            if ($numCurrencies === 1) {
                $feeCurrency = $keys[0];
            }
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'average' => $average,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
            'trades' => $trades,
        );
        return $result;
    }

    public function fetch_orders_with_method ($method, $symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrdersWithMethod() requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            // pageSize optional page size
            // page optional page number
            'symbol' => $market['id'],
        );
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => {     count =>    1,
        //               orderList => array ( {          side =>   "SELL",
        //                                total_price =>   "0.010",
        //                                 created_at =>    1533616673000,
        //                                  avg_price =>   "0.05816200",
        //                                  countCoin =>   "btc",
        //                                     source =>    3,
        //                                       type =>    2,
        //                                   side_msg =>   "卖出",
        //                                     volume =>   "0.010",
        //                                      price =>   "0.00000000",
        //                                 source_msg =>   "API",
        //                                 status_msg =>   "完全成交",
        //                                deal_volume =>   "0.01000000",
        //                                         id =>    3669583,
        //                              remain_volume =>   "0.00000000",
        //                                   baseCoin =>   "eth",
        //                                  tradeList => array ( {     volume => "0.010",
        //                                                    feeCoin => "BTC",
        //                                                      price => "0.05816200",
        //                                                        fee => "0.00000029",
        //                                                      ctime =>  1533616674000,
        //                                                 deal_price => "0.00058162",
        //                                                         id =>  415779,
        //                                                       type => "卖出"            } ),
        //                                     status =>    2                                 } ) } }
        //
        // privateGetNewOrder returns resultList, privateGetAllOrder returns orderList
        $orders = $this->safe_value_2($response['data'], 'orderList', 'resultList', array());
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_with_method ('privateGetNewOrder', $symbol, $since, $limit, $params);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_with_method ('privateGetAllOrder', $symbol, $since, $limit, $params);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'order_id' => $id,
            'symbol' => $market['id'],
        );
        $response = $this->privateGetOrderInfo (array_merge ($request, $params));
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => { trade_list => array ( {     volume => "0.010",
        //                                  feeCoin => "BTC",
        //                                    price => "0.05816200",
        //                                      fee => "0.00000029",
        //                                    ctime =>  1533616674000,
        //                               deal_price => "0.00058162",
        //                                       $id =>  415779,
        //                                     type => "卖出"            } ),
        //               order_info => {          side =>   "SELL",
        //                               total_price =>   "0.010",
        //                                created_at =>    1533616673000,
        //                                 avg_price =>   "0.05816200",
        //                                 countCoin =>   "btc",
        //                                    source =>    3,
        //                                      type =>    2,
        //                                  side_msg =>   "卖出",
        //                                    volume =>   "0.010",
        //                                     price =>   "0.00000000",
        //                                source_msg =>   "API",
        //                                status_msg =>   "完全成交",
        //                               deal_volume =>   "0.01000000",
        //                                        $id =>    3669583,
        //                             remain_volume =>   "0.00000000",
        //                                  baseCoin =>   "eth",
        //                                 tradeList => array ( {     volume => "0.010",
        //                                                   feeCoin => "BTC",
        //                                                     price => "0.05816200",
        //                                                       fee => "0.00000029",
        //                                                     ctime =>  1533616674000,
        //                                                deal_price => "0.00058162",
        //                                                        $id =>  415779,
        //                                                      type => "卖出"            } ),
        //                                    status =>    2                                 } } }
        //
        return $this->parse_order($response['data']['order_info'], $market);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchMyTrades requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            // pageSize optional page size
            // page optional page number
            'symbol' => $market['id'],
        );
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $response = $this->privateGetAllTrade (array_merge ($request, $params));
        //
        //     { code =>   "0",
        //        msg =>   "suc",
        //       data => {      count =>    1,
        //               resultList => array ( {     volume => "0.010",
        //                                     side => "SELL",
        //                                  feeCoin => "BTC",
        //                                    price => "0.05816200",
        //                                      fee => "0.00000029",
        //                                    ctime =>  1533616674000,
        //                               deal_price => "0.00058162",
        //                                       id =>  415779,
        //                                     type => "卖出",
        //                                   bid_id =>  3669539,
        //                                   ask_id =>  3669583        } ) } }
        //
        $trades = $this->safe_value($response['data'], 'resultList', array());
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'coin' => $currency['id'],
        );
        // https://github.com/UEX-OpenAPI/API_Docs_en/wiki/Query-deposit-$address-of-assigned-token
        $response = $this->privateGetDepositAddressList (array_merge ($request, $params));
        //
        //     {
        //         "$code" => "0",
        //         "msg" => "suc",
        //         "$data" => array (
        //             "$addressList" => array (
        //                 array (
        //                     "$address" => "0x198803ef8e0df9e8812c0105421885e843e6d2e2",
        //                     "$tag" => "",
        //                 ),
        //             ),
        //         ),
        //     }
        //
        $data = $this->safe_value($response, 'data');
        if ($data === null) {
            throw new InvalidAddress($this->id . ' privateGetDepositAddressList() returned no data');
        }
        $addressList = $this->safe_value($data, 'addressList');
        if ($addressList === null) {
            throw new InvalidAddress($this->id . ' privateGetDepositAddressList() returned no $address list');
        }
        $numAddresses = is_array ($addressList) ? count ($addressList) : 0;
        if ($numAddresses < 1) {
            throw new InvalidAddress($this->id . ' privatePostDepositAddresses() returned no addresses');
        }
        $firstAddress = $addressList[0];
        $address = $this->safe_string($firstAddress, 'address');
        $tag = $this->safe_string($firstAddress, 'tag');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function fetch_transactions_by_type ($type, $code = null, $since = null, $limit = null, $params = array ()) {
        if ($code === null) {
            throw new ArgumentsRequired($this->id . ' fetchWithdrawals requires a $currency $code argument');
        }
        $currency = $this->currency ($code);
        $request = array (
            'coin' => $currency['id'],
        );
        if ($limit !== null) {
            $request['pageSize'] = $limit; // default 10
        }
        $transactionType = ($type === 'deposit') ? 'deposit' : 'withdraw'; // instead of withdrawal...
        $method = 'privateGet' . $this->capitalize ($transactionType) . 'History';
        // https://github.com/UEX-OpenAPI/API_Docs_en/wiki/Query-deposit-record-of-assigned-token
        // https://github.com/UEX-OpenAPI/API_Docs_en/wiki/Query-withdraw-record-of-assigned-token
        $response = $this->$method (array_merge ($request, $params));
        //
        //     { $code =>   "0",
        //        msg =>   "suc",
        //       data => { depositList => array ( {     createdAt =>  1533615955000,
        //                                       amount => "0.01",
        //                                     updateAt =>  1533616311000,
        //                                         txid => "0x0922fde6ab8270fe6eb31cb5a37dc732d96dc8193f81cf46c4ab29fde…",
        //                                          tag => "",
        //                                confirmations =>  30,
        //                                    addressTo => "0x198803ef8e0df9e8812c0105421885e843e6d2e2",
        //                                       status =>  1,
        //                                         coin => "ETH"                                                           } ) } }
        //
        //     {
        //         "$code" => "0",
        //         "msg" => "suc",
        //         "data" => {
        //             "withdrawList" => [array (
        //                 "updateAt" => 1540344965000,
        //                 "createdAt" => 1539311971000,
        //                 "status" => 0,
        //                 "addressTo" => "tz1d7DXJXU3AKWh77gSmpP7hWTeDYs8WF18q",
        //                 "tag" => "100128877",
        //                 "id" => 5,
        //                 "txid" => "",
        //                 "fee" => 0.0,
        //                 "amount" => "1",
        //                 "symbol" => "XTZ"
        //             )]
        //         }
        //     }
        //
        $transactions = $this->safe_value($response['data'], $transactionType . 'List');
        return $this->parse_transactions_by_type ($type, $transactions, $code, $since, $limit);
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type ('deposit', $code, $since, $limit, $params);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type ('withdrawal', $code, $since, $limit, $params);
    }

    public function parse_transactions_by_type ($type, $transactions, $code = null, $since = null, $limit = null) {
        $result = array();
        for ($i = 0; $i < count ($transactions); $i++) {
            $transaction = $this->parse_transaction (array_merge (array (
                'type' => $type,
            ), $transactions[$i]));
            $result[] = $transaction;
        }
        return $this->filterByCurrencySinceLimit ($result, $code, $since, $limit);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // deposits
        //
        //      {     createdAt =>  1533615955000,
        //               $amount => "0.01",
        //             updateAt =>  1533616311000,
        //                 $txid => "0x0922fde6ab8270fe6eb31cb5a37dc732d96dc8193f81cf46c4ab29fde…",
        //                  $tag => "",
        //        confirmations =>  30,
        //            addressTo => "0x198803ef8e0df9e8812c0105421885e843e6d2e2",
        //               $status =>  1,
        //                 coin => "ETH"                                                           } ] } }
        //
        // withdrawals
        //
        //     {
        //         "updateAt" => 1540344965000,
        //         "createdAt" => 1539311971000,
        //         "$status" => 0,
        //         "addressTo" => "tz1d7DXJXU3AKWh77gSmpP7hWTeDYs8WF18q",
        //         "$tag" => "100128877",
        //         "$id" => 5,
        //         "$txid" => "",
        //         "fee" => 0.0,
        //         "$amount" => "1",
        //         "symbol" => "XTZ"
        //     }
        //
        $id = $this->safe_string($transaction, 'id');
        $txid = $this->safe_string($transaction, 'txid');
        $timestamp = $this->safe_integer($transaction, 'createdAt');
        $updated = $this->safe_integer($transaction, 'updateAt');
        $code = null;
        $currencyId = $this->safe_string_2($transaction, 'symbol', 'coin');
        $currency = $this->safe_value($this->currencies_by_id, $currencyId);
        if ($currency !== null) {
            $code = $currency['code'];
        } else {
            $code = $this->common_currency_code($currencyId);
        }
        $address = $this->safe_string($transaction, 'addressTo');
        $tag = $this->safe_string($transaction, 'tag');
        $amount = $this->safe_float($transaction, 'amount');
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'status'));
        $type = $this->safe_string($transaction, 'type'); // injected from the outside
        $feeCost = $this->safe_float($transaction, 'fee');
        if (($type === 'deposit') && ($feeCost === null)) {
            $feeCost = 0;
        }
        return array (
            'info' => $transaction,
            'id' => $id,
            'currency' => $code,
            'amount' => $amount,
            'address' => $address,
            'tag' => $tag,
            'status' => $status,
            'type' => $type,
            'updated' => $updated,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => array (
                'currency' => $code,
                'cost' => $feeCost,
            ),
        );
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            '0' => 'pending', // unaudited
            '1' => 'ok', // audited
            '2' => 'failed', // audit failed
            '3' => 'pending', // "payment"
            '4' => 'failed', // payment failed
            '5' => 'ok',
            '6' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->load_markets();
        $fee = $this->safe_float($params, 'fee');
        if ($fee === null) {
            throw new ArgumentsRequired($this->id . 'requires a "$fee" extra parameter in its last argument');
        }
        $this->check_address($address);
        $currency = $this->currency ($code);
        $request = array (
            'coin' => $currency['id'],
            'address' => $address, // only supports existing addresses in your withdraw $address list
            'amount' => $amount,
            'fee' => $fee, // balance >= $this->sum ($amount, $fee)
        );
        if ($tag !== null) {
            $request['tag'] = $tag;
        }
        // https://github.com/UEX-OpenAPI/API_Docs_en/wiki/Withdraw
        $response = $this->privatePostCreateWithdraw (array_merge ($request, $params));
        $id = null;
        return array (
            'info' => $response,
            'id' => $id,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        if ($api === 'public') {
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        } else {
            $this->check_required_credentials();
            $timestamp = (string) $this->seconds ();
            $auth = '';
            $query = $this->keysort (array_merge ($params, array (
                'api_key' => $this->apiKey,
                'time' => $timestamp,
            )));
            $keys = is_array($query) ? array_keys($query) : array();
            for ($i = 0; $i < count ($keys); $i++) {
                $key = $keys[$i];
                $auth .= $key;
                $auth .= (string) $query[$key];
            }
            $signature = $this->hash ($this->encode ($auth . $this->secret));
            if ($query) {
                if ($method === 'GET') {
                    $url .= '?' . $this->urlencode ($query) . '&sign=' . $signature;
                } else {
                    $body = $this->urlencode ($query) . '&sign=' . $signature;
                }
            }
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            //
            // array("$code":"0","msg":"suc","data":array())
            //
            $code = $this->safe_string($response, 'code');
            // $message = $this->safe_string($response, 'msg');
            $feedback = $this->id . ' ' . $this->json ($response);
            $exceptions = $this->exceptions;
            if ($code !== '0') {
                if (is_array($exceptions) && array_key_exists($code, $exceptions)) {
                    throw new $exceptions[$code]($feedback);
                } else {
                    throw new ExchangeError($feedback);
                }
            }
        }
    }
}
