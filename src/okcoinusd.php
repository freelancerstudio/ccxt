<?php

namespace ccxt;

use Exception as Exception; // a common import

class okcoinusd extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'okcoinusd',
            'name' => 'OKCoin USD',
            'countries' => array ( 'CN', 'US' ),
            'version' => 'v1',
            'rateLimit' => 1000, // up to 3000 requests per 5 minutes ≈ 600 requests per minute ≈ 10 requests per second ≈ 100 ms
            'has' => array (
                'CORS' => false,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOrders' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchTickers' => true,
                'withdraw' => true,
                'futures' => false,
            ),
            'extension' => '.do', // appended to endpoint URL
            'timeframes' => array (
                '1m' => '1min',
                '3m' => '3min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '2h' => '2hour',
                '4h' => '4hour',
                '6h' => '6hour',
                '12h' => '12hour',
                '1d' => '1day',
                '3d' => '3day',
                '1w' => '1week',
            ),
            'api' => array (
                'web' => array (
                    'get' => array (
                        'futures/pc/market/marketOverview',
                        'spot/markets/index-tickers',
                        'spot/markets/currencies',
                        'spot/markets/products',
                        'spot/markets/tickers',
                        'spot/user-level',
                    ),
                    'post' => array (
                        'futures/pc/market/futuresCoin',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'depth',
                        'exchange_rate',
                        'future_depth',
                        'future_estimated_price',
                        'future_hold_amount',
                        'future_index',
                        'future_kline',
                        'future_price_limit',
                        'future_ticker',
                        'future_trades',
                        'kline',
                        'otcs',
                        'ticker',
                        'tickers',
                        'trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'account_records',
                        'batch_trade',
                        'borrow_money',
                        'borrow_order_info',
                        'borrows_info',
                        'cancel_borrow',
                        'cancel_order',
                        'cancel_otc_order',
                        'cancel_withdraw',
                        'funds_transfer',
                        'future_batch_trade',
                        'future_cancel',
                        'future_devolve',
                        'future_explosive',
                        'future_order_info',
                        'future_orders_info',
                        'future_position',
                        'future_position_4fix',
                        'future_trade',
                        'future_trades_history',
                        'future_userinfo',
                        'future_userinfo_4fix',
                        'lend_depth',
                        'order_fee',
                        'order_history',
                        'order_info',
                        'orders_info',
                        'otc_order_history',
                        'otc_order_info',
                        'repayment',
                        'submit_otc_order',
                        'trade',
                        'trade_history',
                        'trade_otc_order',
                        'wallet_info',
                        'withdraw',
                        'withdraw_info',
                        'unrepayments_info',
                        'userinfo',
                    ),
                ),
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766791-89ffb502-5ee5-11e7-8a5b-c5950b68ac65.jpg',
                'api' => array (
                    'web' => 'https://www.okcoin.com/v2',
                    'public' => 'https://www.okcoin.com/api',
                    'private' => 'https://www.okcoin.com',
                ),
                'www' => 'https://www.okcoin.com',
                'doc' => array (
                    'https://www.okcoin.com/docs/en/',
                    'https://www.npmjs.com/package/okcoin.com',
                ),
                'referral' => 'https://www.okcoin.com/account/register?flag=activity&channelId=600001513',
            ),
            // these are okcoin.com fees, okex fees are in okex.js
            'fees' => array (
                'trading' => array (
                    'taker' => 0.001,
                    'maker' => 0.0005,
                ),
            ),
            'exceptions' => array (
                // see https://github.com/okcoin-okex/API-docs-OKEx.com/blob/master/API-For-Spot-EN/Error%20Code%20For%20Spot.md
                '10000' => '\\ccxt\\ExchangeError', // "Required field, can not be null"
                '10001' => '\\ccxt\\DDoSProtection', // "Request frequency too high to exceed the limit allowed"
                '10005' => '\\ccxt\\AuthenticationError', // "'SecretKey' does not exist"
                '10006' => '\\ccxt\\AuthenticationError', // "'Api_key' does not exist"
                '10007' => '\\ccxt\\AuthenticationError', // "Signature does not match"
                '1002' => '\\ccxt\\InsufficientFunds', // "The transaction amount exceed the balance"
                '1003' => '\\ccxt\\InvalidOrder', // "The transaction amount is less than the minimum requirement"
                '1004' => '\\ccxt\\InvalidOrder', // "The transaction amount is less than 0"
                '1013' => '\\ccxt\\InvalidOrder', // no contract type (PR-1101)
                '1027' => '\\ccxt\\InvalidOrder', // createLimitBuyOrder(symbol, 0, 0) => Incorrect parameter may exceeded limits
                '1050' => '\\ccxt\\InvalidOrder', // returned when trying to cancel an order that was filled or canceled previously
                '1217' => '\\ccxt\\InvalidOrder', // "Order was sent at ±5% of the current market price. Please resend"
                '10014' => '\\ccxt\\InvalidOrder', // "Order price must be between 0 and 1,000,000"
                '1009' => '\\ccxt\\OrderNotFound', // for spot markets, cancelling closed order
                '1019' => '\\ccxt\\OrderNotFound', // order closed? ("Undo order failed")
                '1051' => '\\ccxt\\OrderNotFound', // for spot markets, cancelling "just closed" order
                '10009' => '\\ccxt\\OrderNotFound', // for spot markets, "Order does not exist"
                '20015' => '\\ccxt\\OrderNotFound', // for future markets
                '10008' => '\\ccxt\\BadRequest', // Illegal URL parameter
                // todo => sort out below
                // 10000 Required parameter is empty
                // 10001 Request frequency too high to exceed the limit allowed
                // 10002 Authentication failure
                // 10002 System error
                // 10003 This connection has requested other user data
                // 10004 Request failed
                // 10005 api_key or sign is invalid, 'SecretKey' does not exist
                // 10006 'Api_key' does not exist
                // 10007 Signature does not match
                // 10008 Illegal parameter, Parameter erorr
                // 10009 Order does not exist
                // 10010 Insufficient funds
                // 10011 Amount too low
                // 10012 Only btc_usd ltc_usd supported
                // 10013 Only support https request
                // 10014 Order price must be between 0 and 1,000,000
                // 10015 Order price differs from current market price too much / Channel subscription temporally not available
                // 10016 Insufficient coins balance
                // 10017 API authorization error / WebSocket authorization error
                // 10018 borrow amount less than lower limit [usd:100,btc:0.1,ltc:1]
                // 10019 loan agreement not checked
                // 1002 The transaction amount exceed the balance
                // 10020 rate cannot exceed 1%
                // 10021 rate cannot less than 0.01%
                // 10023 fail to get latest ticker
                // 10024 balance not sufficient
                // 10025 quota is full, cannot borrow temporarily
                // 10026 Loan (including reserved loan) and margin cannot be withdrawn
                // 10027 Cannot withdraw within 24 hrs of authentication information modification
                // 10028 Withdrawal amount exceeds daily limit
                // 10029 Account has unpaid loan, please cancel/pay off the loan before withdraw
                // 1003 The transaction amount is less than the minimum requirement
                // 10031 Deposits can only be withdrawn after 6 confirmations
                // 10032 Please enabled phone/google authenticator
                // 10033 Fee higher than maximum network transaction fee
                // 10034 Fee lower than minimum network transaction fee
                // 10035 Insufficient BTC/LTC
                // 10036 Withdrawal amount too low
                // 10037 Trade password not set
                // 1004 The transaction amount is less than 0
                // 10040 Withdrawal cancellation fails
                // 10041 Withdrawal address not exsit or approved
                // 10042 Admin password error
                // 10043 Account equity error, withdrawal failure
                // 10044 fail to cancel borrowing order
                // 10047 this function is disabled for sub-account
                // 10048 withdrawal information does not exist
                // 10049 User can not have more than 50 unfilled small orders (amount<0.15BTC)
                // 10050 can't cancel more than once
                // 10051 order completed transaction
                // 10052 not allowed to withdraw
                // 10064 after a USD deposit, that portion of assets will not be withdrawable for the next 48 hours
                // 1007 No trading market information
                // 1008 No latest market information
                // 1009 No order
                // 1010 Different user of the cancelled order and the original order
                // 10100 User account frozen
                // 10101 order type is wrong
                // 10102 incorrect ID
                // 10103 the private otc order's key incorrect
                // 10106 API key domain not matched
                // 1011 No documented user
                // 1013 No order type
                // 1014 No login
                // 1015 No market depth information
                // 1017 Date error
                // 1018 Order failed
                // 1019 Undo order failed
                // 10216 Non-available API / non-public API
                // 1024 Currency does not exist
                // 1025 No chart type
                // 1026 No base currency quantity
                // 1027 Incorrect parameter may exceeded limits
                // 1028 Reserved decimal failed
                // 1029 Preparing
                // 1030 Account has margin and futures, transactions can not be processed
                // 1031 Insufficient Transferring Balance
                // 1032 Transferring Not Allowed
                // 1035 Password incorrect
                // 1036 Google Verification code Invalid
                // 1037 Google Verification code incorrect
                // 1038 Google Verification replicated
                // 1039 Message Verification Input exceed the limit
                // 1040 Message Verification invalid
                // 1041 Message Verification incorrect
                // 1042 Wrong Google Verification Input exceed the limit
                // 1043 Login password cannot be same as the trading password
                // 1044 Old password incorrect
                // 1045 2nd Verification Needed
                // 1046 Please input old password
                // 1048 Account Blocked
                // 1050 Orders have been withdrawn or withdrawn
                // 1051 Order completed
                // 1201 Account Deleted at 00 => 00
                // 1202 Account Not Exist
                // 1203 Insufficient Balance
                // 1204 Invalid currency
                // 1205 Invalid Account
                // 1206 Cash Withdrawal Blocked
                // 1207 Transfer Not Support
                // 1208 No designated account
                // 1209 Invalid api
                // 1216 Market order temporarily suspended. Please send limit order
                // 1217 Order was sent at ±5% of the current market price. Please resend
                // 1218 Place order failed. Please try again later
                // 20001 User does not exist
                // 20002 Account frozen
                // 20003 Account frozen due to forced liquidation
                // 20004 Contract account frozen
                // 20005 User contract account does not exist
                // 20006 Required field missing
                // 20007 Illegal parameter
                // 20008 Contract account balance is too low
                // 20009 Contract status error
                // 20010 Risk rate ratio does not exist
                // 20011 Risk rate lower than 90%/80% before opening BTC position with 10x/20x leverage. or risk rate lower than 80%/60% before opening LTC position with 10x/20x leverage
                // 20012 Risk rate lower than 90%/80% after opening BTC position with 10x/20x leverage. or risk rate lower than 80%/60% after opening LTC position with 10x/20x leverage
                // 20013 Temporally no counter party price
                // 20014 System error
                // 20015 Order does not exist
                // 20016 Close amount bigger than your open positions, liquidation quantity bigger than holding
                // 20017 Not authorized/illegal operation/illegal order ID
                // 20018 Order price cannot be more than 103-105% or less than 95-97% of the previous minute price
                // 20019 IP restricted from accessing the resource
                // 20020 Secret key does not exist
                // 20021 Index information does not exist
                // 20022 Wrong API interface (Cross margin mode shall call cross margin API, fixed margin mode shall call fixed margin API)
                // 20023 Account in fixed-margin mode
                // 20024 Signature does not match
                // 20025 Leverage rate error
                // 20026 API Permission Error
                // 20027 no transaction record
                // 20028 no such contract
                // 20029 Amount is large than available funds
                // 20030 Account still has debts
                // 20038 Due to regulation, this function is not availavle in the country/region your currently reside in.
                // 20049 Request frequency too high
                // 20100 request time out
                // 20101 the format of data is error
                // 20102 invalid login
                // 20103 event type error
                // 20104 subscription type error
                // 20107 JSON format error
                // 20115 The quote is not match
                // 20116 Param not match
                // 21020 Contracts are being delivered, orders cannot be placed
                // 21021 Contracts are being settled, contracts cannot be placed
            ),
            'options' => array (
                'marketBuyPrice' => false,
                'fetchOHLCVWarning' => true,
                'contractTypes' => array (
                    '1' => 'this_week',
                    '2' => 'next_week',
                    '4' => 'quarter',
                ),
                'fetchTickersMethod' => 'fetch_tickers_from_api',
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        // TODO => they have a new fee schedule as of Feb 7
        // the new $fees are progressive and depend on 30-day traded volume
        // the following is the worst case
        $result = array();
        $spotResponse = $this->webGetSpotMarketsProducts ();
        //
        //     {
        //         "code" => 0,
        //         "data" => array (
        //             array (
        //                 "baseCurrency":0,
        //                 "brokerId":0,
        //                 "callAuctionOrCallNoCancelAuction":false,
        //                 "callNoCancelSwitchTime":array(),
        //                 "collect":"0",
        //                 "continuousSwitchTime":array(),
        //                 "groupId":1,
        //                 "isMarginOpen":true,
        //                 "listDisplay":0,
        //                 "marginRiskPreRatio":1.2,
        //                 "marginRiskRatio":1.1,
        //                 "marketFrom":118,
        //                 "maxMarginLeverage":5,
        //                 "maxPriceDigit":1,
        //                 "maxSizeDigit":8,
        //                 "mergeTypes":"0.1,1,10",
        //                 "minTradeSize":0.00100000,
        //                 "online":1,
        //                 "productId":20,
        //                 "quoteCurrency":7,
        //                 "quoteIncrement":"0.1",
        //                 "quotePrecision":2,
        //                 "sort":30038,
        //                 "$symbol":"btc_usdt",
        //                 "tradingMode":3
        //             ),
        //         )
        //     }
        //
        $spotMarkets = $this->safe_value($spotResponse, 'data', array());
        $markets = $spotMarkets;
        if ($this->has['futures']) {
            $futuresResponse = $this->webPostFuturesPcMarketFuturesCoin ();
            //
            //     {
            //         "msg":"success",
            //         "code":0,
            //         "detailMsg":"",
            //         "data" => [
            //             array (
            //                 "symbolId":0,
            //                 "$symbol":"f_usd_btc",
            //                 "iceSingleAvgMinAmount":2,
            //                 "minTradeSize":1,
            //                 "iceSingleAvgMaxAmount":500,
            //                 "contractDepthLevel":["0.01","0.2"],
            //                 "dealAllMaxAmount":999,
            //                 "maxSizeDigit":4,
            //                 "$contracts":array (
            //                     array( "marketFrom":34, "$id":201905240000034, "$type":1, "desc":"BTC0524" ),
            //                     array( "marketFrom":13, "$id":201905310000013, "$type":2, "desc":"BTC0531" ),
            //                     array( "marketFrom":12, "$id":201906280000012, "$type":4, "desc":"BTC0628" ),
            //                 ),
            //                 "maxPriceDigit":2,
            //                 "nativeRate":1,
            //                 "$quote":"usd",
            //                 "nativeCurrency":"usd",
            //                 "nativeCurrencyMark":"$",
            //                 "contractSymbol":0,
            //                 "unitAmount":100.00,
            //                 "symbolMark":"฿",
            //                 "symbolDesc":"BTC"
            //             ),
            //         ]
            //     }
            //
            $futuresMarkets = $this->safe_value($futuresResponse, 'data', array());
            $markets = $this->array_concat($spotMarkets, $futuresMarkets);
        }
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'symbol');
            $symbol = null;
            $base = null;
            $quote = null;
            $baseId = null;
            $quoteId = null;
            $baseNumericId = null;
            $quoteNumericId = null;
            $lowercaseId = null;
            $uppercaseBaseId = null;
            $uppercaseQuoteId = null;
            $precision = array (
                'amount' => $this->safe_integer($market, 'maxSizeDigit'),
                'price' => $this->safe_integer($market, 'maxPriceDigit'),
            );
            $minAmount = $this->safe_float($market, 'minTradeSize');
            $minPrice = pow(10, -$precision['price']);
            $contracts = $this->safe_value($market, 'contracts');
            if ($contracts === null) {
                // $spot $markets
                $lowercaseId = $id;
                $parts = explode('_', $id);
                $baseId = $parts[0];
                $quoteId = $parts[1];
                $baseNumericId = $this->safe_integer($market, 'baseCurrency');
                $quoteNumericId = $this->safe_integer($market, 'quoteCurrency');
                $uppercaseBaseId = strtoupper($baseId);
                $uppercaseQuoteId = strtoupper($quoteId);
                $base = $this->common_currency_code($uppercaseBaseId);
                $quote = $this->common_currency_code($uppercaseQuoteId);
                $contracts = [array()];
            } else {
                // futures $markets
                $quoteId = $this->safe_string($market, 'quote');
                $uppercaseBaseId = $this->safe_string($market, 'symbolDesc');
                $uppercaseQuoteId = strtoupper($quoteId);
                $baseId = strtolower($uppercaseBaseId);
                $lowercaseId = $baseId . '_' . $quoteId;
                $base = $this->common_currency_code($uppercaseBaseId);
                $quote = $this->common_currency_code($uppercaseQuoteId);
            }
            for ($k = 0; $k < count ($contracts); $k++) {
                $contract = $contracts[$k];
                $type = $this->safe_string($contract, 'type', 'spot');
                $contractType = null;
                $spot = true;
                $future = false;
                $active = true;
                if ($type === 'spot') {
                    $symbol = $base . '/' . $quote;
                    $active = $market['online'] !== 0;
                } else {
                    $contractId = $this->safe_string($contract, 'id');
                    $symbol = $base . '-' . $quote . '-' . mb_substr($contractId, 2, 8 - 2);
                    $contractType = $this->safe_string($this->options['contractTypes'], $type);
                    $type = 'future';
                    $spot = false;
                    $future = true;
                }
                $fees = $this->safe_value_2($this->fees, $type, 'trading', array());
                $result[] = array_merge ($fees, array (
                    'id' => $id,
                    'lowercaseId' => $lowercaseId,
                    'contractType' => $contractType,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'baseNumericId' => $baseNumericId,
                    'quoteNumericId' => $quoteNumericId,
                    'info' => $market,
                    'type' => $type,
                    'spot' => $spot,
                    'future' => $future,
                    'active' => $active,
                    'precision' => $precision,
                    'limits' => array (
                        'amount' => array (
                            'min' => $minAmount,
                            'max' => null,
                        ),
                        'price' => array (
                            'min' => $minPrice,
                            'max' => null,
                        ),
                        'cost' => array (
                            'min' => $minAmount * $minPrice,
                            'max' => null,
                        ),
                    ),
                ));
            }
        }
        return $result;
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
            'cost' => floatval ($this->fee_to_precision($symbol, $cost)),
        );
    }

    public function fetch_tickers_from_api ($symbols = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $response = $this->publicGetTickers (array_merge ($request, $params));
        $tickers = $response['tickers'];
        $timestamp = $this->safe_integer($response, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $result = array();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $tickers[$i];
            $ticker = $this->parse_ticker(array_merge ($tickers[$i], array( 'timestamp' => $timestamp )));
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
    }

    public function fetch_tickers_from_web ($symbols = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $response = $this->webGetSpotMarketsTickers (array_merge ($request, $params));
        $tickers = $this->safe_value($response, 'data');
        $result = array();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $this->parse_ticker($tickers[$i]);
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $method = $this->options['fetchTickersMethod'];
        return $this->$method ($symbols, $params);
    }

    public function fetch_order_book ($symbol = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'publicGetFutureDepth' : 'publicGetDepth';
        $request = $this->create_request ($market, $params);
        if ($limit !== null) {
            $request['size'] = $limit;
        }
        $response = $this->$method ($request);
        return $this->parse_order_book($response);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {              buy =>   "48.777300",
        //                 $change =>   "-1.244500",
        //       changePercentage =>   "-2.47%",
        //                  close =>   "49.064000",
        //            createdDate =>    1531704852254,
        //             currencyId =>    527,
        //                dayHigh =>   "51.012500",
        //                 dayLow =>   "48.124200",
        //                   high =>   "51.012500",
        //                inflows =>   "0",
        //                   $last =>   "49.064000",
        //                    low =>   "48.124200",
        //             marketFrom =>    627,
        //                   name => array(  ),
        //                   $open =>   "50.308500",
        //               outflows =>   "0",
        //              productId =>    527,
        //                   sell =>   "49.064000",
        //                 $symbol =>   "zec_okb",
        //                 volume =>   "1049.092535"   }
        //
        $timestamp = $this->safe_integer_2($ticker, 'timestamp', 'createdDate');
        $symbol = null;
        if ($market === null) {
            if (is_array($ticker) && array_key_exists('symbol', $ticker)) {
                $marketId = $ticker['symbol'];
                if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                    $market = $this->markets_by_id[$marketId];
                } else {
                    list($baseId, $quoteId) = explode('_', $ticker['symbol']);
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
        $last = $this->safe_float($ticker, 'last');
        $open = $this->safe_float($ticker, 'open');
        $change = $this->safe_float($ticker, 'change');
        $percentage = $this->safe_float($ticker, 'changePercentage');
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
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $this->safe_float_2($ticker, 'vol', 'volume'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'publicGetFutureTicker' : 'publicGetTicker';
        $request = $this->create_request ($market, $params);
        $response = $this->$method ($request);
        $ticker = $this->safe_value($response, 'ticker');
        if ($ticker === null) {
            throw new ExchangeError($this->id . ' fetchTicker returned an empty $response => ' . $this->json ($response));
        }
        $timestamp = $this->safe_integer($response, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
            $ticker = array_merge ($ticker, array( 'timestamp' => $timestamp ));
        }
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer($trade, 'date_ms');
        $id = $this->safe_string($trade, 'tid');
        $type = null;
        $side = $this->safe_string($trade, 'type');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => null,
            'type' => $type,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'publicGetFutureTrades' : 'publicGetTrades';
        $request = $this->create_request ($market, $params);
        $response = $this->$method ($request);
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        $numElements = is_array ($ohlcv) ? count ($ohlcv) : 0;
        $volumeIndex = ($numElements > 6) ? 6 : 5;
        return [
            $ohlcv[0], // timestamp
            floatval ($ohlcv[1]), // Open
            floatval ($ohlcv[2]), // High
            floatval ($ohlcv[3]), // Low
            floatval ($ohlcv[4]), // Close
            // floatval ($ohlcv[5]), // quote volume
            // floatval ($ohlcv[6]), // base volume
            floatval ($ohlcv[$volumeIndex]), // okex will return base volume in the 7th element for future markets
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'publicGetFutureKline' : 'publicGetKline';
        $request = $this->create_request ($market, array (
            'type' => $this->timeframes[$timeframe],
            // 'since' => $since === null ? $this->milliseconds () - 86400000 : $since,  // default last 24h
        ));
        if ($since !== null) {
            $request['since'] = $this->milliseconds () - 86400000; // default last 24h
        }
        if ($limit !== null) {
            if ($this->options['fetchOHLCVWarning']) {
                throw new ExchangeError($this->id . ' fetchOHLCV counts "$limit" candles backwards in chronological ascending order, therefore the "$limit" argument for ' . $this->id . ' is disabled. Set ' . $this->id . '.options["fetchOHLCVWarning"] = false to suppress this warning message.');
            }
            $request['size'] = intval ($limit); // max is 1440 candles
        }
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostUserinfo ($params);
        $info = $this->safe_value($response, 'info', array());
        $balances = $this->safe_value($info, 'funds', array());
        $result = array( 'info' => $response );
        $ids = is_array($balances['free']) ? array_keys($balances['free']) : array();
        $usedField = 'freezed';
        // wtf, okex?
        // https://github.com/okcoin-okex/API-docs-OKEx.com/commit/01cf9dd57b1f984a8737ef76a037d4d3795d2ac7
        if (!(is_array($balances) && array_key_exists($usedField, $balances))) {
            $usedField = 'holds';
        }
        $usedKeys = is_array($balances[$usedField]) ? array_keys($balances[$usedField]) : array();
        $ids = $this->array_concat($ids, $usedKeys);
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $code = strtoupper($id);
            if (is_array($this->currencies_by_id) && array_key_exists($id, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$id]['code'];
            } else {
                $code = $this->common_currency_code($code);
            }
            $account = $this->account ();
            $account['free'] = $this->safe_float($balances['free'], $id);
            $account['used'] = $this->safe_float($balances[$usedField], $id);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'privatePostFutureTrade' : 'privatePostTrade';
        $orderSide = ($type === 'market') ? ($side . '_market') : $side;
        $isMarketBuy = (($market['spot']) && ($type === 'market') && ($side === 'buy') && (!$this->options['marketBuyPrice']));
        $orderPrice = $isMarketBuy ? $this->safe_float($params, 'cost') : $price;
        $request = $this->create_request ($market, array (
            'type' => $orderSide,
        ));
        if ($market['future']) {
            $request['match_price'] = 0; // match best counter party $price? 0 or 1, ignores $price if 1
            $request['lever_rate'] = 10; // leverage rate value => 10 or 20 (10 by default)
        } else if ($type === 'market') {
            if ($side === 'buy') {
                if (!$orderPrice) {
                    if ($this->options['marketBuyPrice']) {
                        // eslint-disable-next-line quotes
                        throw new ExchangeError($this->id . " $market buy orders require a $price argument (the $amount you want to spend or the cost of the order) when $this->options['marketBuyPrice'] is true.");
                    } else {
                        // eslint-disable-next-line quotes
                        throw new ExchangeError($this->id . " $market buy orders require an additional cost parameter, cost = $price * $amount-> If you want to pass the cost of the $market order (the $amount you want to spend) in the $price argument (the default " . $this->id . " behaviour), set $this->options['marketBuyPrice'] = true. It will effectively suppress this warning exception as well.");
                    }
                } else {
                    $request['price'] = $orderPrice;
                }
            } else {
                $request['amount'] = $amount;
            }
        }
        if ($type !== 'market') {
            $request['price'] = $orderPrice;
            $request['amount'] = $amount;
        }
        $params = $this->omit ($params, 'cost');
        $response = $this->$method (array_merge ($request, $params));
        $timestamp = $this->milliseconds ();
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'order_id'),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'cost' => null,
            'trades' => null,
            'fee' => null,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder() requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'privatePostFutureCancel' : 'privatePostCancelOrder';
        $request = $this->create_request ($market, array (
            'order_id' => $id,
        ));
        $response = $this->$method (array_merge ($request, $params));
        return $response;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '-1' => 'canceled',
            '0' => 'open',
            '1' => 'open',
            '2' => 'closed',
            '3' => 'open',
            '4' => 'canceled',
        );
        return $this->safe_value($statuses, $status, $status);
    }

    public function parse_order_side ($side) {
        if ($side === 1) {
            return 'buy'; // open long position
        } else if ($side === 2) {
            return 'sell'; // open short position
        } else if ($side === 3) {
            return 'sell'; // liquidate long position
        } else if ($side === 4) {
            return 'buy'; // liquidate short position
        }
        return $side;
    }

    public function parse_order ($order, $market = null) {
        $side = null;
        $type = null;
        if (is_array($order) && array_key_exists('type', $order)) {
            if (($order['type'] === 'buy') || ($order['type'] === 'sell')) {
                $side = $order['type'];
                $type = 'limit';
            } else if ($order['type'] === 'buy_market') {
                $side = 'buy';
                $type = 'market';
            } else if ($order['type'] === 'sell_market') {
                $side = 'sell';
                $type = 'market';
            } else {
                $side = $this->parse_order_side ($order['type']);
                if ((is_array($order) && array_key_exists('contract_name', $order)) || (is_array($order) && array_key_exists('lever_rate', $order))) {
                    $type = 'margin';
                }
            }
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'symbol');
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market) {
            $symbol = $market['symbol'];
        }
        $createDateField = $this->get_create_date_field ();
        $timestamp = $this->safe_integer($order, $createDateField);
        $amount = $this->safe_float($order, 'amount');
        $filled = $this->safe_float($order, 'deal_amount');
        $amount = max ($amount, $filled);
        $remaining = max (0, $amount - $filled);
        if ($type === 'market') {
            $remaining = 0;
        }
        $average = $this->safe_float($order, 'avg_price');
        // https://github.com/ccxt/ccxt/issues/2452
        $average = $this->safe_float($order, 'price_avg', $average);
        $cost = $average * $filled;
        return array (
            'info' => $order,
            'id' => $this->safe_string($order, 'order_id'),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $this->safe_float($order, 'price'),
            'average' => $average,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
        );
    }

    public function get_create_date_field () {
        // needed for derived exchanges
        // allcoin typo create_data instead of create_date
        return 'create_date';
    }

    public function get_orders_field () {
        // needed for derived exchanges
        // allcoin typo order instead of orders (expected based on their API docs)
        return 'orders';
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ExchangeError($this->id . ' fetchOrder requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'privatePostFutureOrderInfo' : 'privatePostOrderInfo';
        $request = $this->create_request ($market, array (
            'order_id' => $id,
            // 'status' => 0, // 0 for unfilled orders, 1 for filled orders
            // 'current_page' => 1, // current page number
            // 'page_length' => 200, // number of orders returned per page, maximum 200
        ));
        $response = $this->$method (array_merge ($request, $params));
        $ordersField = $this->get_orders_field ();
        $numOrders = is_array ($response[$ordersField]) ? count ($response[$ordersField]) : 0;
        if ($numOrders > 0) {
            return $this->parse_order($response[$ordersField][0]);
        }
        throw new OrderNotFound($this->id . ' order ' . $id . ' not found');
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ExchangeError($this->id . ' fetchOrders requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['future'] ? 'privatePostFutureOrdersInfo' : 'privatePost';
        $request = $this->create_request ($market);
        $order_id_in_params = (is_array($params) && array_key_exists('order_id', $params));
        if ($market['future']) {
            if (!$order_id_in_params) {
                throw new ExchangeError($this->id . ' fetchOrders() requires order_id param for futures $market ' . $symbol . ' (a string of one or more order ids, comma-separated)');
            }
        } else {
            $status = (is_array($params) && array_key_exists('type', $params)) ? $params['type'] : $params['status'];
            if ($status === null) {
                $name = $order_id_in_params ? 'type' : 'status';
                throw new ExchangeError($this->id . ' fetchOrders() requires ' . $name . ' param for spot $market ' . $symbol . ' (0 - for unfilled orders, 1 - for filled/canceled orders)');
            }
            if ($order_id_in_params) {
                $method .= 'OrdersInfo';
                $request = array_merge ($request, array (
                    'type' => $status,
                    'order_id' => $params['order_id'],
                ));
            } else {
                $method .= 'OrderHistory';
                $request = array_merge ($request, array (
                    'status' => $status,
                    'current_page' => 1, // current page number
                    'page_length' => 200, // number of orders returned per page, maximum 200
                ));
            }
            $params = $this->omit ($params, array ( 'type', 'status' ));
        }
        $response = $this->$method (array_merge ($request, $params));
        $ordersField = $this->get_orders_field ();
        return $this->parse_orders($response[$ordersField], $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $request = array (
            'status' => 0, // 0 for unfilled orders, 1 for filled orders
        );
        return $this->fetch_orders($symbol, $since, $limit, array_merge ($request, $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $request = array (
            'status' => 1, // 0 for unfilled orders, 1 for filled orders
        );
        return $this->fetch_orders($symbol, $since, $limit, array_merge ($request, $params));
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        // if ($amount < 0.01)
        //     throw new ExchangeError($this->id . ' withdraw() requires $amount > 0.01');
        // for some reason they require to supply a pair of currencies for withdrawing one $currency
        $currencyId = $currency['id'] . '_usd';
        if ($tag) {
            $address = $address . ':' . $tag;
        }
        $request = array (
            'symbol' => $currencyId,
            'withdraw_address' => $address,
            'withdraw_amount' => $amount,
            'target' => 'address', // or 'okcn', 'okcom', 'okex'
        );
        $query = $params;
        if (is_array($query) && array_key_exists('chargefee', $query)) {
            $request['chargefee'] = $query['chargefee'];
            $query = $this->omit ($query, 'chargefee');
        } else {
            throw new ExchangeError($this->id . ' withdraw() requires a `chargefee` parameter');
        }
        if ($this->password) {
            $request['trade_pwd'] = $this->password;
        } else if (is_array($query) && array_key_exists('password', $query)) {
            $request['trade_pwd'] = $query['password'];
            $query = $this->omit ($query, 'password');
        } else if (is_array($query) && array_key_exists('trade_pwd', $query)) {
            $request['trade_pwd'] = $query['trade_pwd'];
            $query = $this->omit ($query, 'trade_pwd');
        }
        $passwordInRequest = (is_array($request) && array_key_exists('trade_pwd', $request));
        if (!$passwordInRequest) {
            throw new ExchangeError($this->id . ' withdraw() requires $this->password set on the exchange instance or a password / trade_pwd parameter');
        }
        $response = $this->privatePostWithdraw (array_merge ($request, $query));
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'withdraw_id'),
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = '/';
        if ($api !== 'web') {
            $url .= $this->version . '/';
        }
        $url .= $path;
        if ($api !== 'web') {
            $url .= $this->extension;
        }
        if ($api === 'private') {
            $this->check_required_credentials();
            $query = $this->keysort (array_merge (array (
                'api_key' => $this->apiKey,
            ), $params));
            // secret key must be at the end of $query
            $queryString = $this->rawencode ($query) . '&secret_key=' . $this->secret;
            $query['sign'] = strtoupper($this->hash ($this->encode ($queryString)));
            $body = $this->urlencode ($query);
            $headers = array( 'Content-Type' => 'application/x-www-form-urlencoded' );
        } else {
            if ($params) {
                $url .= '?' . $this->urlencode ($params);
            }
        }
        $url = $this->urls['api'][$api] . $url;
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function create_request ($market, $params = array ()) {
        if ($market['future']) {
            return array_replace_recursive (array (
                'symbol' => $market['lowercaseId'],
                'contract_type' => $market['contractType'],
            ), $params);
        }
        return array_replace_recursive (array (
            'symbol' => $market['id'],
        ), $params);
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response) {
        if ($response === null) {
            return; // fallback to default $error handler
        }
        if (is_array($response) && array_key_exists('error_code', $response)) {
            $error = $this->safe_string($response, 'error_code');
            $message = $this->id . ' ' . $this->json ($response);
            if (is_array($this->exceptions) && array_key_exists($error, $this->exceptions)) {
                $ExceptionClass = $this->exceptions[$error];
                throw new $ExceptionClass($message);
            } else {
                throw new ExchangeError($message);
            }
        }
        if (is_array($response) && array_key_exists('result', $response)) {
            if (!$response['result']) {
                throw new ExchangeError($this->id . ' ' . $this->json ($response));
            }
        }
    }
}
