<?php

namespace ccxt;

use Exception as Exception; // a common import

class coss extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coss',
            'name' => 'COSS',
            'countries' => array ( 'SG', 'NL' ),
            'rateLimit' => 1000,
            'version' => 'v1',
            'certified' => true,
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/50328158-22e53c00-0503-11e9-825c-c5cfd79bfa74.jpg',
                'api' => array (
                    'trade' => 'https://trade.coss.io/c/api/v1',
                    'engine' => 'https://engine.coss.io/api/v1',
                    'public' => 'https://trade.coss.io/c/api/v1',
                    'web' => 'https://trade.coss.io/c', // undocumented
                    'exchange' => 'https://exchange.coss.io/api',
                ),
                'www' => 'https://www.coss.io',
                'doc' => 'https://api.coss.io/v1/spec',
                'referral' => 'https://www.coss.io/c/reg?r=OWCMHQVW2Q',
            ),
            'has' => array (
                'fetchTrades' => true,
                'fetchTicker' => true,
                'fetchTickers' => true,
                'fetchMarkets' => true,
                'fetchCurrencies' => true,
                'fetchBalance' => true,
                'fetchOrderBook' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOrderTrades' => true,
                'fetchClosedOrders' => true,
                'fetchOpenOrders' => true,
                'fetchOHLCV' => true,
                'createOrder' => true,
                'cancelOrder' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
                '5m' => '5m',
                '15m' => '15m',
                '30m' => '30m',
                '1h' => '1h',
                '2h' => '2h',
                '4h' => '4h',
                '6h' => '6h',
                '12h' => '12h',
                '1d' => '1d',
                '1w' => '1w',
            ),
            'api' => array (
                'exchange' => array (
                    'get' => array (
                        'getmarketsummaries',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'market-price',
                        'exchange-info',
                    ),
                ),
                'web' => array (
                    'get' => array (
                        'coins/getinfo/all', // undocumented
                        'order/symbols', // undocumented
                        'coins/get_base_list', // undocumented
                    ),
                ),
                'engine' => array (
                    'get' => array (
                        'dp',
                        'ht',
                        'cs',
                    ),
                ),
                'trade' => array (
                    'get' => array (
                        'ping',
                        'time',
                        'account/balances',
                        'account/details',
                    ),
                    'post' => array (
                        'order/add',
                        'order/details',
                        'order/list/open',
                        'order/list/completed',
                        'order/list/all',
                        'order/trade-detail',
                    ),
                    'delete' => array (
                        'order/cancel',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => true,
                    'percentage' => true,
                    'taker' => 0.0020,
                    'maker' => 0.0014,
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(),
                    'deposit' => array(),
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetExchangeInfo ($params);
        //
        //     {        timezone =>   "UTC",
        //           server_time =>    1545171487108,
        //           rate_limits => array ( {     type => "REQUESTS",
        //                            interval => "MINUTE",
        //                               limit =>  1000       } ),
        //       base_currencies => array ( array( currency_code => "BTC", minimum_total_order => "0.0001" ),
        //                          array( currency_code => "USDT", minimum_total_order => "1" ),
        //                          array( currency_code => "EUR", minimum_total_order => "1" ) ),
        //                 coins => array ( array (        currency_code => "ADI",
        //                                            name => "Aditus",
        //                            minimum_order_amount => "0.00000001" ),
        //                          ...
        //                          {        currency_code => "NPXSXEM",
        //                                            name => "PundiX-XEM",
        //                            minimum_order_amount => "0.00000001"  }                ),
        //               symbols => array ( array (               $symbol => "ADI_BTC",
        //                            amount_limit_decimal =>  0,
        //                             price_limit_decimal =>  8,
        //                                   allow_trading =>  true      ),
        //                          ...
        //                          {               $symbol => "ETH_GUSD",
        //                            amount_limit_decimal =>  5,
        //                             price_limit_decimal =>  3,
        //                                   allow_trading =>  true       }     )               }
        //
        $result = array();
        $markets = $this->safe_value($response, 'symbols', array());
        $baseCurrencies = $this->safe_value($response, 'base_currencies', array());
        $baseCurrenciesByIds = $this->index_by($baseCurrencies, 'currency_code');
        $currencies = $this->safe_value($response, 'coins', array());
        $currenciesByIds = $this->index_by($currencies, 'currency_code');
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $marketId = $market['symbol'];
            list($baseId, $quoteId) = explode('_', $marketId);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $this->safe_integer($market, 'amount_limit_decimal'),
                'price' => $this->safe_integer($market, 'price_limit_decimal'),
            );
            $active = $this->safe_value($market, 'allow_trading', false);
            $baseCurrency = $this->safe_value($baseCurrenciesByIds, $baseId, array());
            $minCost = $this->safe_float($baseCurrency, 'minimum_total_order');
            $currency = $this->safe_value($currenciesByIds, $baseId, array());
            $defaultMinAmount = pow(10, -$precision['amount']);
            $minAmount = $this->safe_float($currency, 'minimum_order_amount', $defaultMinAmount);
            $result[] = array (
                'symbol' => $symbol,
                'id' => $marketId,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'base' => $base,
                'quote' => $quote,
                'active' => $active,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $minAmount,
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => $minCost,
                        'max' => null,
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->webGetCoinsGetinfoAll ($params);
        //
        //     [ array (                 currency_code => "VET",
        //                                  $name => "VeChain",
        //                             buy_limit =>  0,
        //                            sell_limit =>  0,
        //                                  usdt =>  0,
        //                transaction_time_limit =>  5,
        //                                status => "trade",
        //                         withdrawn_fee => "0.6",
        //              minimum_withdrawn_amount => "1.2",
        //                minimum_deposit_amount => "0.6",
        //                  minimum_order_amount => "0.00000001",
        //                        decimal_format => "0.########",
        //                            token_type =>  null, // "erc", "eos", "stellar", "tron", "ripple"...
        //                                buy_at =>  0,
        //                               sell_at =>  0,
        //                              min_rate =>  0,
        //                              max_rate =>  0,
        //                       allow_withdrawn =>  false,
        //                         allow_deposit =>  false,
        //         explorer_website_mainnet_link =>  null,
        //         explorer_website_testnet_link =>  null,
        //            deposit_block_confirmation => "6",
        //           withdraw_block_confirmation => "0",
        //                              icon_url => "https://s2.coinmarketcap.com/static/img/coins/32x32/3077.png",
        //                               is_fiat =>  false,
        //                            allow_sell =>  true,
        //                             allow_buy =>  true                                                           )]
        //
        $result = array();
        for ($i = 0; $i < count ($response); $i++) {
            $currency = $response[$i];
            $currencyId = $this->safe_string($currency, 'currency_code');
            $code = $this->common_currency_code($currencyId);
            $name = $this->safe_string($currency, 'name');
            $allowBuy = $this->safe_value($currency, 'allow_buy');
            $allowSell = $this->safe_value($currency, 'allow_sell');
            $allowWithdrawals = $this->safe_value($currency, 'allow_withdrawn');
            $allowDeposits = $this->safe_value($currency, 'allow_deposit');
            $active = $allowBuy && $allowSell && $allowWithdrawals && $allowDeposits;
            $fee = $this->safe_float($currency, 'withdrawn_fee');
            $type = $this->safe_string($currency, 'token_type');
            //
            // decimal_format can be anything...
            //
            //     0.########
            //     #.########
            //     0.##
            //     '' (empty string)
            //     0.000000
            //     null (null)
            //     0.0000
            //     0.###
            //
            $decimalFormat = $this->safe_string($currency, 'decimal_format');
            $precision = 8;
            if ($decimalFormat !== null) {
                $parts = explode('.', $decimalFormat);
                $numParts = is_array ($parts) ? count ($parts) : 0; // transpiler workaround for array lengths
                if ($numParts > 1) {
                    if (strlen ($parts[1]) > 1) {
                        $precision = is_array ($parts[1]) ? count ($parts[1]) : 0;
                    }
                }
            }
            $result[$code] = array (
                'id' => $currencyId,
                'code' => $code,
                'info' => $currency,
                'name' => $name,
                'active' => $active,
                'fee' => $fee,
                'precision' => $precision,
                'type' => $type,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($currency, 'minimum_order_amount'),
                        'max' => null,
                    ),
                    'withdraw' => array (
                        'min' => $this->safe_float($currency, 'minimum_withdrawn_amount'),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->tradeGetAccountBalances ($params);
        //
        //     array ( array ( currency_code => "ETH",
        //               address => "0x6820511d43111a941d3e187b9e36ec64af763bde", // deposit address
        //                 $total => "0.20399125",
        //             available => "0.20399125",
        //              in_order => "0",
        //                  memo =>  null                                         ), // tag, if any
        //       { currency_code => "ICX",
        //               address => "",
        //                 $total => "0",
        //             available => "0",
        //              in_order => "0",
        //                  memo =>  null  }                                         )
        //
        $result = array();
        for ($i = 0; $i < count ($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'currency_code');
            $code = $this->common_currency_code($currencyId);
            $total = $this->safe_float($balance, 'total');
            $used = $this->safe_float($balance, 'in_order');
            $free = $this->safe_float($balance, 'available');
            $result[$code] = array (
                'total' => $total,
                'used' => $used,
                'free' => $free,
            );
        }
        return $this->parse_balance($result);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            intval ($ohlcv[0]),   // timestamp
            floatval ($ohlcv[1]), // Open
            floatval ($ohlcv[2]), // High
            floatval ($ohlcv[3]), // Low
            floatval ($ohlcv[4]), // Close
            floatval ($ohlcv[5]), // base Volume
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'tt' => $this->timeframes[$timeframe],
        );
        $response = $this->engineGetCs (array_merge ($request, $params));
        //
        //     {       tt =>   "1m",
        //         $symbol =>   "ETH_BTC",
        //       nextTime =>    1545138960000,
        //         series => array ( array (  1545138960000,
        //                     "0.02705000",
        //                     "0.02705000",
        //                     "0.02705000",
        //                     "0.02705000",
        //                     "0.00000000"    ),
        //                   ...
        //                   array (  1545168900000,
        //                     "0.02684000",
        //                     "0.02684000",
        //                     "0.02684000",
        //                     "0.02684000",
        //                     "0.00000000"    )  ),
        //          $limit =>    500                    }
        //
        return $this->parse_ohlcvs($response['series'], $market, $timeframe, $since, $limit);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $marketId = $this->market_id($symbol);
        $request = array( 'symbol' => $marketId );
        // $limit argument is not supported on COSS's end
        $response = $this->engineGetDp (array_merge ($request, $params));
        //
        //     { $symbol =>   "COSS_ETH",
        //         asks => [ ["0.00065200", "214.15000000"],
        //                 ["0.00065300", "645.45000000"],
        //                 ...
        //                 ["0.00076400", "380.00000000"],
        //                 ["0.00076900", "25.00000000"]     ],
        //        $limit =>    100,
        //         bids => [ ["0.00065100", "666.99000000"],
        //                 ["0.00065000", "1171.93000000"],
        //                 ...
        //                 ["0.00037700", "3300.00000000"],
        //                 ["0.00037600", "2010.82000000"]   ],
        //         time =>    1545180569354                       }
        //
        $timestamp = $this->safe_integer($response, 'time');
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //      { MarketName => "COSS-ETH",
        //              High =>  0.00066,
        //               Low =>  0.000628,
        //        BaseVolume =>  131.09652674,
        //              Last =>  0.000636,
        //         TimeStamp => "2018-12-19T05:16:41.369Z",
        //            Volume =>  206126.6143710692,
        //               Ask => "0.00063600",
        //               Bid => "0.00063400",
        //           PrevDay =>  0.000636                   }
        //
        $timestamp = $this->parse8601 ($this->safe_string($ticker, 'TimeStamp'));
        $symbol = null;
        $marketId = $this->safe_string($ticker, 'MarketName');
        if ($marketId !== null) {
            $marketId = str_replace('-', '_', $marketId);
        }
        $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        if ($market === null) {
            if ($marketId !== null) {
                list($baseId, $quoteId) = explode('_', $marketId);
                $base = $this->common_currency_code($baseId);
                $quote = $this->common_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $previous = $this->safe_float($ticker, 'PrevDay');
        $last = $this->safe_float($ticker, 'Last');
        $change = null;
        $percentage = null;
        if ($last !== null) {
            if ($previous !== null) {
                $change = $last - $previous;
                if ($previous > 0) {
                    $percentage = ($change / $previous) * 100;
                }
            }
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'High'),
            'low' => $this->safe_float($ticker, 'Low'),
            'bid' => $this->safe_float($ticker, 'Bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'Ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $previous,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'Volume'),
            'quoteVolume' => $this->safe_float($ticker, 'BaseVolume'),
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->exchangeGetGetmarketsummaries ($params);
        //
        //     { success =>    true,
        //       message =>   "",
        //        $result => array ( array ( MarketName => "COSS-ETH",
        //                          High =>  0.00066,
        //                           Low =>  0.000628,
        //                    BaseVolume =>  131.09652674,
        //                          Last =>  0.000636,
        //                     TimeStamp => "2018-12-19T05:16:41.369Z",
        //                        Volume =>  206126.6143710692,
        //                           Ask => "0.00063600",
        //                           Bid => "0.00063400",
        //                       PrevDay =>  0.000636                   ),
        //                  ...
        //                  { MarketName => "XLM-BTC",
        //                          High =>  0.0000309,
        //                           Low =>  0.0000309,
        //                    BaseVolume =>  0,
        //                          Last =>  0.0000309,
        //                     TimeStamp => "2018-12-19T02:00:02.145Z",
        //                        Volume =>  0,
        //                           Ask => "0.00003300",
        //                           Bid => "0.00003090",
        //                       PrevDay =>  0.0000309                  }  ),
        //       volumes => array ( array( CoinName => "ETH", Volume => 668.1928095999999 ), // these are overall exchange volumes
        //                  array( CoinName => "USD", Volume => 9942.58480324 ),
        //                  array( CoinName => "BTC", Volume => 43.749184570000004 ),
        //                  array( CoinName => "COSS", Volume => 909909.26644574 ),
        //                  array( CoinName => "EUR", Volume => 0 ),
        //                  array( CoinName => "TUSD", Volume => 2613.3395026999997 ),
        //                  array( CoinName => "USDT", Volume => 1017152.07416519 ),
        //                  array( CoinName => "GUSD", Volume => 1.80438 ),
        //                  array( CoinName => "XRP", Volume => 15.95508 ),
        //                  array( CoinName => "GBP", Volume => 0 ),
        //                  array( CoinName => "USDC", Volume => 0 )                   ),
        //             t =>    1545196604371                                       }
        //
        $tickers = $this->safe_value($response, 'result', array());
        $result = array();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $this->parse_ticker($tickers[$i]);
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $tickers = $this->fetch_tickers(array ( $symbol ), $params);
        return $tickers[$symbol];
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->engineGetHt (array_merge ($request, $params));
        //
        //     {  $symbol =>   "COSS_ETH",
        //         $limit =>    100,
        //       history => array ( array (           id =>  481321,
        //                           price => "0.00065100",
        //                             qty => "272.92000000",
        //                    isBuyerMaker =>  false,
        //                            time =>  1545180845019  ),
        //                  array (           id =>  481322,
        //                           price => "0.00065200",
        //                             qty => "1.90000000",
        //                    isBuyerMaker =>  true,
        //                            time =>  1545180847535 ),
        //                  ...
        //                  {           id =>  481420,
        //                           price => "0.00065300",
        //                             qty => "2.00000000",
        //                    isBuyerMaker =>  true,
        //                            time =>  1545181167702 }   ),
        //          time =>    1545181171274                        }
        //
        return $this->parse_trades($response['history'], $market, $since, $limit);
    }

    public function parse_trade_fee ($fee) {
        if ($fee === null) {
            return $fee;
        }
        $parts = explode(' ', $fee);
        $numParts = is_array ($parts) ? count ($parts) : 0;
        $cost = $parts[0];
        $code = null;
        if ($numParts > 1) {
            $code = $this->common_currency_code($parts[1]);
        }
        return array (
            'cost' => $cost,
            'currency' => $code,
        );
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //      {           $id =>  481322,
        //               $price => "0.00065200",
        //                 qty => "1.90000000",
        //        isBuyerMaker =>  true,
        //                time =>  1545180847535 }
        //
        // fetchOrderTrades (private)
        //
        //     array ( {         hex_id =>  null,
        //                 $symbol => "COSS_ETH",
        //               order_id => "ad6f6b47-3def-4add-a5d5-2549a9df1593",
        //             order_side => "BUY",
        //                  $price => "0.00065900",
        //               quantity => "10",
        //                    $fee => "0.00700000 COSS",
        //         additional_fee => "0.00000461 ETH",
        //                  total => "0.00659000 ETH",
        //              $timestamp =>  1545152356075                          } )
        //
        $id = $this->safe_string($trade, 'id');
        $timestamp = $this->safe_integer($trade, 'time');
        $orderId = $this->safe_string($trade, 'order_id');
        $side = $this->safe_string($trade, 'order_side');
        if ($side !== null) {
            $side = strtolower($side);
        }
        $symbol = null;
        $marketId = $this->safe_string($trade, 'symbol');
        if ($marketId !== null) {
            $market = $this->safe_value($this->markets_by_id, $marketId, $market);
            if ($market === null) {
                list($baseId, $quoteId) = explode('_', $marketId);
                $base = $this->common_currency_code($baseId);
                $quote = $this->common_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        } else if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $cost = null;
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float_2($trade, 'qty', 'quantity');
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $price * $amount;
            }
        }
        $result = array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $id,
            'order' => $orderId,
            'type' => null,
            'takerOrMaker' => null,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
        );
        $fee = $this->parse_trade_fee ($this->safe_string($trade, 'fee'));
        if ($fee !== null) {
            $additionalFee = $this->parse_trade_fee ($this->safe_string($trade, 'additional_fee'));
            if ($additionalFee === null) {
                $result['fee'] = $fee;
            } else {
                $result['fees'] = array (
                    $fee,
                    $additionalFee,
                );
            }
        }
        return $result;
    }

    public function fetch_orders_by_type ($type, $symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrders requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            // 'from_id' => 'b2a2d379-f9b6-418b-9414-cbf8330b20d1', // string (uuid), fetchOrders (all $orders) only
            // 'page' => 0, // different pagination in fetchOpenOrders and fetchClosedOrders
            // 'limit' => 50, // optional, max = default = 50
            'symbol' => $market['id'], // required
        );
        if ($limit !== null) {
            $request['limit'] = $limit; // max = default = 50
        }
        $method = 'tradePostOrderList' . $type;
        $response = $this->$method (array_merge ($request, $params));
        //
        // fetchOrders, fetchClosedOrders
        //
        //     array ( {       hex_id => "5c192784330fe51149f556bb",
        //             order_id => "5e46e1b1-93d5-4656-9b43-a5635b08eae9",
        //           account_id => "a0c20128-b9e0-484e-9bc8-b8bb86340e5b",
        //         order_symbol => "COSS_ETH",
        //           order_side => "BUY",
        //               status => "filled",
        //           createTime =>  1545152388019,
        //                 $type => "$limit",
        //         timeMatching =>  0,
        //          order_price => "0.00065900",
        //           order_size => "10",
        //             executed => "10",
        //           stop_price => "0.00000000",
        //                  avg => "0.00065900",
        //                total => "0.00659000 ETH"                        }  )
        //
        // fetchOpenOrders
        //
        //     {
        //         "total" => 2,
        //         "list" => array (
        //             {
        //                 "order_id" => "9e5ae4dd-3369-401d-81f5-dff985e1c4ty",
        //                 "account_id" => "9e5ae4dd-3369-401d-81f5-dff985e1c4a6",
        //                 "order_symbol" => "eth-btc",
        //                 "order_side" => "BUY",
        //                 "status" => "OPEN",
        //                 "createTime" => 1538114348750,
        //                 "$type" => "$limit",
        //                 "order_price" => "0.12345678",
        //                 "order_size" => "10.12345678",
        //                 "executed" => "0",
        //                 "stop_price" => "02.12345678",
        //                 "avg" => "1.12345678",
        //                 "total" => "2.12345678"
        //             }
        //         )
        //     }
        //
        // the following code is to handle the above difference in $response formats
        $orders = null;
        if (gettype ($response) === 'array' && count (array_filter (array_keys ($response), 'is_string')) == 0) {
            $orders = $response;
        } else {
            $orders = $this->safe_value($response, 'list', array());
        }
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type ('All', $symbol, $since, $limit, $params);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type ('Completed', $symbol, $since, $limit, $params);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type ('Open', $symbol, $since, $limit, $params);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'order_id' => $id,
        );
        $response = $this->tradePostOrderDetails (array_merge ($request, $params));
        return $this->parse_order($response);
    }

    public function fetch_order_trades ($id, $symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $request = array (
            'order_id' => $id,
        );
        $response = $this->tradePostOrderTradeDetail (array_merge ($request, $params));
        //
        //     array ( {         hex_id =>  null,
        //                 $symbol => "COSS_ETH",
        //               order_id => "ad6f6b47-3def-4add-a5d5-2549a9df1593",
        //             order_side => "BUY",
        //                  price => "0.00065900",
        //               quantity => "10",
        //                    fee => "0.00700000 COSS",
        //         additional_fee => "0.00000461 ETH",
        //                  total => "0.00659000 ETH",
        //              timestamp =>  1545152356075                          } )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_order_status ($status) {
        if ($status === null) {
            return $status;
        }
        $statuses = array (
            'OPEN' => 'open',
            'CANCELLED' => 'canceled',
            'FILLED' => 'closed',
            'PARTIAL_FILL' => 'closed',
            'CANCELLING' => 'open',
        );
        return $this->safe_string($statuses, strtoupper($status), $status);
    }

    public function parse_order ($order, $market = null) {
        //
        //       {       hex_id => "5c192784330fe51149f556bb", // missing in fetchOpenOrders
        //             order_id => "5e46e1b1-93d5-4656-9b43-a5635b08eae9",
        //           account_id => "a0c20128-b9e0-484e-9bc8-b8bb86340e5b",
        //         order_symbol => "COSS_ETH", // coss-eth in docs
        //           order_side => "BUY",
        //               $status => "$filled",
        //           createTime =>  1545152388019,
        //                 $type => "limit",
        //         timeMatching =>  0, // missing in fetchOpenOrders
        //          order_price => "0.00065900",
        //           order_size => "10",
        //             executed => "10",
        //           stop_price => "0.00000000",
        //                  avg => "0.00065900",
        //                total => "0.00659000 ETH"                        }
        //
        $id = $this->safe_string($order, 'order_id');
        $symbol = null;
        $marketId = $this->safe_string($order, 'order_symbol');
        if ($marketId === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        } else {
            // a minor workaround for lowercase eth-btc symbols
            $marketId = strtoupper($marketId);
            $marketId = str_replace('-', '_', $marketId);
            $market = $this->safe_value($this->markets_by_id, $marketId, $market);
            if ($market === null) {
                list($baseId, $quoteId) = explode('_', $marketId);
                $base = $this->common_currency_code($baseId);
                $quote = $this->common_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            } else {
                $symbol = $market['symbol'];
            }
        }
        $timestamp = $this->safe_integer($order, 'createTime');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $price = $this->safe_float($order, 'order_price');
        $filled = $this->safe_float($order, 'executed');
        $type = $this->safe_string($order, 'type');
        $amount = $this->safe_float($order, 'order_size');
        $remaining = null;
        if ($amount !== null) {
            if ($filled !== null) {
                $remaining = $amount - $filled;
            }
        }
        $average = $this->safe_float($order, 'avg');
        $side = $this->safe_string($order, 'order_side');
        if ($side !== null) {
            $side = strtolower($side);
        }
        $cost = $this->safe_float($order, 'total');
        $fee = null;
        $trades = null;
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'average' => $average,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
            'trades' => $trades,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'order_symbol' => $market['id'],
            'order_price' => floatval ($this->price_to_precision($symbol, $price)),
            'order_size' => floatval ($this->amount_to_precision($symbol, $amount)),
            'order_side' => strtoupper($side),
            'type' => $type,
        );
        $response = $this->tradePostOrderAdd (array_merge ($request, $params));
        //
        //     {
        //         "order_id" => "9e5ae4dd-3369-401d-81f5-dff985e1c4ty",
        //         "account_id" => "9e5ae4dd-3369-401d-81f5-dff985e1c4a6",
        //         "order_symbol" => "eth-btc",
        //         "order_side" => "BUY",
        //         "status" => "OPEN",
        //         "createTime" => 1538114348750,
        //         "$type" => "limit",
        //         "order_price" => "0.12345678",
        //         "order_size" => "10.12345678",
        //         "executed" => "0",
        //         "stop_price" => "02.12345678",
        //         "avg" => "1.12345678",
        //         "total" => "2.12345678"
        //     }
        //
        return $this->parse_order($response, $market);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'order_id' => $id,
            'order_symbol' => $market['id'],
        );
        $response = $this->tradeDeleteOrderCancel (array_merge ($request, $params));
        //
        //     { order_symbol => "COSS_ETH",
        //           order_id => "30f2d698-39a0-4b9f-a3a6-a179542373bd",
        //         order_size =>  0,
        //         account_id => "a0c20128-b9e0-484e-9bc8-b8bb86340e5b",
        //          timestamp =>  1545202728814,
        //         recvWindow =>  null                                   }
        //
        return $this->parse_order($response);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/' . $path;
        if ($api === 'trade') {
            $this->check_required_credentials();
            $timestamp = $this->nonce ();
            $query = array_merge (array (
                'timestamp' => $timestamp, // required (int64)
                // 'recvWindow' => 10000, // optional (int32)
            ), $params);
            $request = null;
            if ($method === 'GET') {
                $request = $this->urlencode ($query);
                $url .= '?' . $request;
            } else {
                $request = $this->json ($query);
                $body = $request;
            }
            $headers = array (
                'Signature' => $this->hmac ($this->encode ($request), $this->encode ($this->secret)),
                'Authorization' => $this->apiKey,
            );
        } else {
            if ($params) {
                $url .= '?' . $this->urlencode ($params);
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
