<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\ArgumentsRequired;

class btcmarkets extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'btcmarkets',
            'name' => 'BTC Markets',
            'countries' => array( 'AU' ), // Australia
            'rateLimit' => 1000, // market data cached for 1 second (trades cached for 2 seconds)
            'has' => array(
                'cancelOrder' => true,
                'cancelOrders' => true,
                'CORS' => false,
                'createOrder' => true,
                'fetchBalance' => true,
                'fetchClosedOrders' => 'emulated',
                'fetchDeposits' => true,
                'fetchMarkets' => true,
                'fetchMyTrades' => true,
                'fetchOHLCV' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchOrderBook' => true,
                'fetchOrders' => true,
                'fetchTicker' => true,
                'fetchTime' => true,
                'fetchTrades' => true,
                'fetchTransactions' => true,
                'fetchWithdrawals' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/51840849/89731817-b3fb8480-da52-11ea-817f-783b08aaf32b.jpg',
                'api' => array(
                    'public' => 'https://api.btcmarkets.net',
                    'private' => 'https://api.btcmarkets.net',
                    'privateV3' => 'https://api.btcmarkets.net/v3',
                    'web' => 'https://btcmarkets.net/data',
                ),
                'www' => 'https://btcmarkets.net',
                'doc' => array(
                    'https://api.btcmarkets.net/doc/v3#section/API-client-libraries',
                    'https://github.com/BTCMarkets/API',
                ),
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'market/{id}/tick',
                        'market/{id}/orderbook',
                        'market/{id}/trades',
                        'v2/market/{id}/tickByTime/{timeframe}',
                        'v2/market/{id}/trades',
                        'v2/market/active',
                        'v3/markets',
                        'v3/markets/{marketId}/ticker',
                        'v3/markets/{marketId}/trades',
                        'v3/markets/{marketId}/orderbook',
                        'v3/markets/{marketId}/candles',
                        'v3/markets/tickers',
                        'v3/markets/orderbooks',
                        'v3/time',
                    ),
                ),
                'private' => array(
                    'get' => array(
                        'account/balance',
                        'account/{id}/tradingfee',
                        'fundtransfer/history',
                        'v2/order/open',
                        'v2/order/open/{id}',
                        'v2/order/history/{instrument}/{currency}/',
                        'v2/order/trade/history/{id}',
                        'v2/transaction/history/{currency}',
                    ),
                    'post' => array(
                        'fundtransfer/withdrawCrypto',
                        'fundtransfer/withdrawEFT',
                        'order/create',
                        'order/cancel',
                        'order/history',
                        'order/open',
                        'order/trade/history',
                        'order/createBatch', // they promise it's coming soon...
                        'order/detail',
                    ),
                ),
                'privateV3' => array(
                    'get' => array(
                        'orders',
                        'orders/{id}',
                        'batchorders/{ids}',
                        'trades',
                        'trades/{id}',
                        'withdrawals',
                        'withdrawals/{id}',
                        'deposits',
                        'deposits/{id}',
                        'transfers',
                        'transfers/{id}',
                        'addresses',
                        'withdrawal-fees',
                        'assets',
                        'accounts/me/trading-fees',
                        'accounts/me/withdrawal-limits',
                        'accounts/me/balances',
                        'accounts/me/transactions',
                        'reports/{id}',
                    ),
                    'post' => array(
                        'orders',
                        'batchorders',
                        'withdrawals',
                        'reports',
                    ),
                    'delete' => array(
                        'orders',
                        'orders/{id}',
                        'batchorders/{ids}',
                    ),
                    'put' => array(
                        'orders/{id}',
                    ),
                ),
                'web' => array(
                    'get' => array(
                        'market/BTCMarkets/{id}/tickByTime',
                    ),
                ),
            ),
            'timeframes' => array(
                '1m' => 'minute',
                '1h' => 'hour',
                '1d' => 'day',
            ),
            'exceptions' => array(
                '3' => '\\ccxt\\InvalidOrder',
                '6' => '\\ccxt\\DDoSProtection',
                'InsufficientFund' => '\\ccxt\\InsufficientFunds',
                'InvalidPrice' => '\\ccxt\\InvalidOrder',
                'InvalidAmount' => '\\ccxt\\InvalidOrder',
                'MissingArgument' => '\\ccxt\\InvalidOrder',
                'OrderAlreadyCancelled' => '\\ccxt\\InvalidOrder',
                'OrderNotFound' => '\\ccxt\\OrderNotFound',
                'OrderStatusIsFinal' => '\\ccxt\\InvalidOrder',
            ),
            'fees' => array(
                'percentage' => true,
                'tierBased' => true,
                'maker' => -0.05 / 100,
                'taker' => 0.20 / 100,
            ),
            'options' => array(
                'fees' => array(
                    'AUD' => array(
                        'maker' => 0.85 / 100,
                        'taker' => 0.85 / 100,
                    ),
                ),
            ),
        ));
    }

    public function fetch_transactions_with_method($method, $code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        if ($since !== null) {
            $request['after'] = $since;
        }
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency($code);
        }
        $response = $this->$method (array_merge($request, $params));
        return $this->parse_transactions($response, $currency, $since, $limit);
    }

    public function fetch_transactions($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_with_method('privateV3GetTransfers', $code, $since, $limit, $params);
    }

    public function fetch_deposits($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_with_method('privateV3GetDeposits', $code, $since, $limit, $params);
    }

    public function fetch_withdrawals($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_with_method('privateV3GetWithdrawals', $code, $since, $limit, $params);
    }

    public function parse_transaction_status($status) {
        // todo => find more $statuses
        $statuses = array(
            'Complete' => 'ok',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_transaction_type($type) {
        $statuses = array(
            'Withdraw' => 'withdrawal',
            'Deposit' => 'deposit',
        );
        return $this->safe_string($statuses, $type, $type);
    }

    public function parse_transaction($transaction, $currency = null) {
        //
        //    {
        //         "id" => "6500230339",
        //         "assetName" => "XRP",
        //         "$amount" => "500",
        //         "$type" => "Deposit",
        //         "creationTime" => "2020-07-27T07:52:08.640000Z",
        //         "$status" => "Complete",
        //         "description" => "RIPPLE Deposit, XRP 500",
        //         "$fee" => "0",
        //         "$lastUpdate" => "2020-07-27T07:52:08.665000Z",
        //         "paymentDetail" => {
        //             "txId" => "lsjflsjdfljsd",
        //             "$address" => "kjasfkjsdf?dt=873874545"
        //         }
        //    }
        //
        //    {
        //         "id" => "500985282",
        //         "assetName" => "BTC",
        //         "$amount" => "0.42570126",
        //         "$type" => "Withdraw",
        //         "creationTime" => "2017-07-29T12:49:03.931000Z",
        //         "$status" => "Complete",
        //         "description" => "BTC withdraw from [nick-btcmarkets@snowmonkey.co.uk] to Address => 1B9DsnSYQ54VMqFHVJYdGoLMCYzFwrQzsj $amount => 0.42570126 $fee => 0.00000000",
        //         "$fee" => "0.0005",
        //         "$lastUpdate" => "2017-07-29T12:52:20.676000Z",
        //         "paymentDetail" => {
        //             "txId" => "fkjdsfjsfljsdfl",
        //             "$address" => "a;daddjas;djas"
        //         }
        //    }
        //
        //    {
        //         "id" => "505102262",
        //         "assetName" => "XRP",
        //         "$amount" => "979.836",
        //         "$type" => "Deposit",
        //         "creationTime" => "2017-07-31T08:50:01.053000Z",
        //         "$status" => "Complete",
        //         "description" => "Ripple Deposit, X 979.8360",
        //         "$fee" => "0",
        //         "$lastUpdate" => "2017-07-31T08:50:01.290000Z"
        //     }
        //
        $timestamp = $this->parse8601($this->safe_string($transaction, 'creationTime'));
        $lastUpdate = $this->parse8601($this->safe_string($transaction, 'lastUpdate'));
        $type = $this->parse_transaction_type($this->safe_string_lower($transaction, 'type'));
        if ($type === 'withdraw') {
            $type = 'withdrawal';
        }
        $cryptoPaymentDetail = $this->safe_value($transaction, 'paymentDetail', array());
        $txid = $this->safe_string($cryptoPaymentDetail, 'txId');
        $address = $this->safe_string($cryptoPaymentDetail, 'address');
        $tag = null;
        if ($address !== null) {
            $addressParts = explode('?dt=', $address);
            $numParts = is_array($addressParts) ? count($addressParts) : 0;
            if ($numParts > 1) {
                $address = $addressParts[0];
                $tag = $addressParts[1];
            }
        }
        $addressTo = $address;
        $tagTo = $tag;
        $addressFrom = null;
        $tagFrom = null;
        $fee = $this->safe_float($transaction, 'fee');
        $status = $this->parse_transaction_status($this->safe_string($transaction, 'status'));
        $currencyId = $this->safe_string($transaction, 'assetName');
        $code = $this->safe_currency_code($currencyId);
        $amount = $this->safe_float($transaction, 'amount');
        return array(
            'id' => $this->safe_string($transaction, 'id'),
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'address' => $address,
            'addressTo' => $addressTo,
            'addressFrom' => $addressFrom,
            'tag' => $tag,
            'tagTo' => $tagTo,
            'tagFrom' => $tagFrom,
            'type' => $type,
            'amount' => $amount,
            'currency' => $code,
            'status' => $status,
            'updated' => $lastUpdate,
            'fee' => array(
                'currency' => $code,
                'cost' => $fee,
            ),
            'info' => $transaction,
        );
    }

    public function fetch_markets($params = array ()) {
        $response = $this->publicGetV3Markets ($params);
        $result = array();
        for ($i = 0; $i < count($response); $i++) {
            $market = $response[$i];
            $baseId = $this->safe_string($market, 'baseAssetName');
            $quoteId = $this->safe_string($market, 'quoteAssetName');
            $id = $this->safe_string($market, 'marketId');
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $fees = $this->safe_value($this->safe_value($this->options, 'fees', array()), $quote, $this->fees);
            $pricePrecision = $this->safe_float($market, 'priceDecimals');
            $amountPrecision = $this->safe_float($market, 'amountDecimals');
            $minAmount = $this->safe_float($market, 'minOrderAmount');
            $maxAmount = $this->safe_float($market, 'maxOrderAmount');
            $minPrice = null;
            if ($quote === 'AUD') {
                $minPrice = pow(10, -$pricePrecision);
            }
            $precision = array(
                'amount' => $amountPrecision,
                'price' => $pricePrecision,
            );
            $limits = array(
                'amount' => array(
                    'min' => $minAmount,
                    'max' => $maxAmount,
                ),
                'price' => array(
                    'min' => $minPrice,
                    'max' => null,
                ),
                'cost' => array(
                    'min' => null,
                    'max' => null,
                ),
            );
            $result[] = array(
                'info' => $market,
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => null,
                'maker' => $fees['maker'],
                'taker' => $fees['taker'],
                'limits' => $limits,
                'precision' => $precision,
            );
        }
        return $result;
    }

    public function fetch_time($params = array ()) {
        $response = $this->publicGetV3Time ($params);
        //
        //     {
        //         "timestamp" => "2019-09-01T18:34:27.045000Z"
        //     }
        //
        return $this->parse8601($this->safe_string($response, 'timestamp'));
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privateV3GetAccountsMeBalances ($params);
        $result = array( 'info' => $response );
        for ($i = 0; $i < count($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'assetName');
            $code = $this->safe_currency_code($currencyId);
            $total = $this->safe_float($balance, 'balance');
            $used = $this->safe_float($balance, 'locked');
            $account = $this->account();
            $account['used'] = $used;
            $account['total'] = $total;
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_ohlcv($ohlcv, $market = null) {
        //
        //     {
        //         "timestamp":1572307200000,
        //         "open":1962218,
        //         "high":1974850,
        //         "low":1962208,
        //         "close":1974850,
        //         "volume":305211315,
        //     }
        //
        $multiplier = 100000000; // for price and volume
        $keys = array( 'open', 'high', 'low', 'close', 'volume' );
        $result = array(
            $this->safe_integer($ohlcv, 'timestamp'),
        );
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $value = $this->safe_float($ohlcv, $key);
            if ($value !== null) {
                $value = $value / $multiplier;
            }
            $result[] = $value;
        }
        return $result;
    }

    public function fetch_ohlcv($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'id' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
            // set to true to see candles more recent than the timestamp in the
            // $since parameter, if a $since parameter is used, default is false
            'indexForward' => true,
            // set to true to see the earliest candles first in the list of
            // returned candles in chronological order, default is false
            'sortForward' => true,
        );
        if ($since !== null) {
            $request['since'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit; // default is 3000
        }
        $response = $this->publicGetV2MarketIdTickByTimeTimeframe (array_merge($request, $params));
        //
        //     {
        //         "success":true,
        //         "paging":array(
        //             "newer":"/v2/market/ETH/BTC/tickByTime/day?indexForward=true&$since=1572307200000",
        //             "older":"/v2/market/ETH/BTC/tickByTime/day?$since=1457827200000"
        //         ),
        //         "$ticks":array(
        //             array("timestamp":1572307200000,"open":1962218,"high":1974850,"low":1962208,"close":1974850,"volume":305211315),
        //             array("timestamp":1572220800000,"open":1924700,"high":1951276,"low":1909328,"close":1951276,"volume":1086067595),
        //             array("timestamp":1572134400000,"open":1962155,"high":1962734,"low":1900905,"close":1930243,"volume":790141098),
        //         ),
        //     }
        //
        $ticks = $this->safe_value($response, 'ticks', array());
        return $this->parse_ohlcvs($ticks, $market, $timeframe, $since, $limit);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'id' => $market['id'],
        );
        $response = $this->publicGetMarketIdOrderbook (array_merge($request, $params));
        $timestamp = $this->safe_timestamp($response, 'timestamp');
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ticker($ticker, $market = null) {
        $timestamp = $this->safe_timestamp($ticker, 'timestamp');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'lastPrice');
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'bestBid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'bestAsk'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume24h'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'id' => $market['id'],
        );
        $response = $this->publicGetMarketIdTick (array_merge($request, $params));
        return $this->parse_ticker($response, $market);
    }

    public function parse_trade($trade, $market = null) {
        //
        // public fetchTrades
        //
        //     {
        //         "$id":"6191646611",
        //         "$price":"539.98",
        //         "$amount":"0.5",
        //         "$timestamp":"2020-08-09T15:21:05.016000Z",
        //         "$side":"Ask"
        //     }
        //
        // private fetchMyTrades
        //
        //     {
        //         "$id" => "36014819",
        //         "$marketId" => "XRP-AUD",
        //         "$timestamp" => "2019-06-25T16:01:02.977000Z",
        //         "$price" => "0.67",
        //         "$amount" => "1.50533262",
        //         "$side" => "Ask",
        //         "$fee" => "0.00857285",
        //         "$orderId" => "3648306",
        //         "liquidityType" => "Taker",
        //         "clientOrderId" => "48"
        //     }
        //
        $timestamp = $this->parse8601($this->safe_string($trade, 'timestamp'));
        $marketId = $this->safe_string($trade, 'marketId');
        $symbol = null;
        $base = null;
        $quote = null;
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if (($symbol === null) && ($market !== null)) {
            $symbol = $market['symbol'];
            $base = $market['base'];
            $quote = $market['quote'];
        }
        $feeCurrencyCode = null;
        if ($quote === 'AUD') {
            $feeCurrencyCode = $quote;
        } else {
            $feeCurrencyCode = $base;
        }
        $side = $this->safe_string($trade, 'side');
        if ($side === 'Bid') {
            $side = 'buy';
        } else if ($side === 'Ask') {
            $side = 'sell';
        }
        $id = $this->safe_string($trade, 'id');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $orderId = $this->safe_string($trade, 'orderId');
        $fee = null;
        $feeCost = $this->safe_float($trade, 'fee');
        if ($feeCost !== null) {
            $fee = array(
                'cost' => $feeCost,
                'currency' => $feeCurrencyCode,
            );
        }
        $takerOrMaker = $this->safe_string_lower($trade, 'liquidityType');
        return array(
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'order' => $orderId,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'takerOrMaker' => $takerOrMaker,
            'fee' => $fee,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            // 'since' => 59868345231,
            'marketId' => $market['id'],
        );
        $response = $this->publicGetV3MarketsMarketIdTrades (array_merge($request, $params));
        //
        //     array(
        //         array("id":"6191646611","price":"539.98","amount":"0.5","timestamp":"2020-08-09T15:21:05.016000Z","side":"Ask"),
        //         array("id":"6191646610","price":"539.99","amount":"0.5","timestamp":"2020-08-09T15:21:05.015000Z","side":"Ask"),
        //         array("id":"6191646590","price":"540","amount":"0.00233785","timestamp":"2020-08-09T15:21:04.171000Z","side":"Bid"),
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'marketId' => $market['id'],
            // 'price' => $this->price_to_precision($symbol, $price),
            'amount' => $this->amount_to_precision($symbol, $amount),
            // 'type' => 'Limit', // "Limit", "Market", "Stop Limit", "Stop", "Take Profit"
            'side' => ($side === 'buy') ? 'Bid' : 'Ask',
            // 'triggerPrice' => $this->price_to_precision($symbol, $triggerPrice), // required for Stop, Stop Limit, Take Profit orders
            // 'targetAmount' => $this->amount_to_precision($symbol, targetAmount), // target $amount when a desired target outcome is required for order execution
            // 'timeInForce' => 'GTC', // GTC, FOK, IOC
            // 'postOnly' => false, // boolean if this is a post-only order
            // 'selfTrade' => 'A', // A = allow, P = prevent
            // 'clientOrderId' => $this->uuid(),
        );
        $lowercaseType = strtolower($type);
        $orderTypes = $this->safe_value($this->options, 'orderTypes', array(
            'limit' => 'Limit',
            'market' => 'Market',
            'stop' => 'Stop',
            'stop limit' => 'Stop Limit',
            'take profit' => 'Take Profit',
        ));
        $request['type'] = $this->safe_string($orderTypes, $lowercaseType, $type);
        $priceIsRequired = false;
        $triggerPriceIsRequired = false;
        if ($lowercaseType === 'limit') {
            $priceIsRequired = true;
        // } else if ($lowercaseType === 'market') {
        //     ...
        // }
        } else if ($lowercaseType === 'stop limit') {
            $triggerPriceIsRequired = true;
            $priceIsRequired = true;
        } else if ($lowercaseType === 'take profit') {
            $triggerPriceIsRequired = true;
        } else if ($lowercaseType === 'stop') {
            $triggerPriceIsRequired = true;
        }
        if ($priceIsRequired) {
            if ($price === null) {
                throw new ArgumentsRequired($this->id . ' createOrder() requires a $price argument for a ' . $type . 'order');
            } else {
                $request['price'] = $this->price_to_precision($symbol, $price);
            }
        }
        if ($triggerPriceIsRequired) {
            $triggerPrice = $this->safe_float($params, 'triggerPrice');
            $params = $this->omit($params, 'triggerPrice');
            if ($triggerPrice === null) {
                throw new ArgumentsRequired($this->id . ' createOrder() requires a $triggerPrice parameter for a ' . $type . 'order');
            } else {
                $request['triggerPrice'] = $this->price_to_precision($symbol, $triggerPrice);
            }
        }
        $clientOrderId = $this->safe_string($params, 'clientOrderId');
        if ($clientOrderId !== null) {
            $request['clientOrderId'] = $clientOrderId;
        }
        $params = $this->omit($params, 'clientOrderId');
        $response = $this->privateV3PostOrders (array_merge($request, $params));
        //
        //     {
        //         "orderId" => "7524",
        //         "marketId" => "BTC-AUD",
        //         "$side" => "Bid",
        //         "$type" => "Limit",
        //         "creationTime" => "2019-08-30T11:08:21.956000Z",
        //         "$price" => "100.12",
        //         "$amount" => "1.034",
        //         "openAmount" => "1.034",
        //         "status" => "Accepted",
        //         "$clientOrderId" => "1234-5678",
        //         "timeInForce" => "IOC",
        //         "postOnly" => false,
        //         "selfTrade" => "P",
        //         "triggerAmount" => "105",
        //         "targetAmount" => "1000"
        //     }
        //
        return $this->parse_order($response, $market);
    }

    public function cancel_orders($ids, $symbol = null, $params = array ()) {
        $this->load_markets();
        for ($i = 0; $i < count($ids); $i++) {
            $ids[$i] = intval ($ids[$i]);
        }
        $request = array(
            'ids' => $ids,
        );
        return $this->privateV3DeleteBatchordersIds (array_merge($request, $params));
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $id,
        );
        return $this->privateV3DeleteOrdersId (array_merge($request, $params));
    }

    public function calculate_fee($symbol, $type, $side, $amount, $price, $takerOrMaker = 'taker', $params = array ()) {
        $market = $this->markets[$symbol];
        $rate = $market[$takerOrMaker];
        $currency = null;
        $cost = null;
        if ($market['quote'] === 'AUD') {
            $currency = $market['quote'];
            $cost = floatval ($this->cost_to_precision($symbol, $amount * $price));
        } else {
            $currency = $market['base'];
            $cost = floatval ($this->amount_to_precision($symbol, $amount));
        }
        return array(
            'type' => $takerOrMaker,
            'currency' => $currency,
            'rate' => $rate,
            'cost' => floatval ($this->fee_to_precision($symbol, $rate * $cost)),
        );
    }

    public function parse_order_status($status) {
        $statuses = array(
            'Accepted' => 'open',
            'Placed' => 'open',
            'Partially Matched' => 'open',
            'Fully Matched' => 'closed',
            'Cancelled' => 'canceled',
            'Partially Cancelled' => 'canceled',
            'Failed' => 'rejected',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order($order, $market = null) {
        //
        // createOrder
        //
        //     {
        //         "orderId" => "7524",
        //         "$marketId" => "BTC-AUD",
        //         "$side" => "Bid",
        //         "$type" => "Limit",
        //         "creationTime" => "2019-08-30T11:08:21.956000Z",
        //         "$price" => "100.12",
        //         "$amount" => "1.034",
        //         "openAmount" => "1.034",
        //         "$status" => "Accepted",
        //         "$clientOrderId" => "1234-5678",
        //         "timeInForce" => "IOC",
        //         "postOnly" => false,
        //         "selfTrade" => "P",
        //         "triggerAmount" => "105",
        //         "targetAmount" => "1000"
        //     }
        //
        $timestamp = $this->parse8601($this->safe_string($order, 'creationTime'));
        $marketId = $this->safe_string($order, 'marketId');
        $symbol = null;
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if (($symbol === null) && ($market !== null)) {
            $symbol = $market['symbol'];
        }
        $side = $this->safe_string($order, 'side');
        if ($side === 'Bid') {
            $side = 'buy';
        } else if ($side === 'Ask') {
            $side = 'sell';
        }
        $type = $this->safe_string_lower($order, 'type');
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'amount');
        $remaining = $this->safe_float($order, 'openAmount');
        $filled = null;
        if (($amount !== null) && ($remaining !== null)) {
            $filled = max (0, $amount - $remaining);
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $cost = null;
        if ($price !== null) {
            if ($filled !== null) {
                $cost = $price * $filled;
            }
        }
        $id = $this->safe_string($order, 'orderId');
        $clientOrderId = $this->safe_string($order, 'clientOrderId');
        return array(
            'info' => $order,
            'id' => $id,
            'clientOrderId' => $clientOrderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'average' => null,
            'status' => $status,
            'trades' => null,
            'fee' => null,
        );
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $id,
        );
        $response = $this->privateV3GetOrdersId (array_merge($request, $params));
        return $this->parse_order($response);
    }

    public function fetch_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'status' => 'all',
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['marketId'] = $market['id'];
        }
        if ($since !== null) {
            $request['after'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privateV3GetOrders (array_merge($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $request = array( 'status' => 'open' );
        return $this->fetch_orders($symbol, $since, $limit, array_merge($request, $params));
    }

    public function fetch_closed_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, $params);
        return $this->filter_by($orders, 'status', 'closed');
    }

    public function fetch_my_trades($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['marketId'] = $market['id'];
        }
        if ($since !== null) {
            $request['after'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privateV3GetTrades (array_merge($request, $params));
        //
        //     array(
        //         array(
        //             "id" => "36014819",
        //             "marketId" => "XRP-AUD",
        //             "timestamp" => "2019-06-25T16:01:02.977000Z",
        //             "price" => "0.67",
        //             "amount" => "1.50533262",
        //             "side" => "Ask",
        //             "fee" => "0.00857285",
        //             "orderId" => "3648306",
        //             "liquidityType" => "Taker",
        //             "clientOrderId" => "48"
        //         ),
        //         {
        //             "id" => "3568960",
        //             "marketId" => "GNT-AUD",
        //             "timestamp" => "2019-06-20T08:44:04.488000Z",
        //             "price" => "0.1362",
        //             "amount" => "0.85",
        //             "side" => "Bid",
        //             "fee" => "0.00098404",
        //             "orderId" => "3543015",
        //             "liquidityType" => "Maker"
        //         }
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function lookup_symbol_from_market_id($marketId) {
        $market = null;
        $symbol = null;
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if (($symbol === null) && ($market !== null)) {
            $symbol = $market['symbol'];
        }
        return $symbol;
    }

    public function nonce() {
        return $this->milliseconds();
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $uri = '/' . $this->implode_params($path, $params);
        $url = $this->urls['api'][$api] . $uri;
        $query = $this->keysort($this->omit($params, $this->extract_params($path)));
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce();
            $auth = null;
            $headers = array(
                'apikey' => $this->apiKey,
                'timestamp' => $nonce,
            );
            if ($method === 'POST') {
                $headers['Content-Type'] = 'application/json';
                $auth = $uri . "\n" . $nonce . "\n"; // eslint-disable-line quotes
                $body = $this->json($params);
                $auth .= $body;
            } else {
                $queryString = '';
                if ($query) {
                    $queryString = $this->urlencode($query);
                    $url .= '?' . $queryString;
                    $queryString .= "\n"; // eslint-disable-line quotes
                }
                $auth = $uri . "\n" . $queryString . $nonce . "\n"; // eslint-disable-line quotes
            }
            $secret = base64_decode($this->secret);
            $signature = $this->hmac($this->encode($auth), $secret, 'sha512', 'base64');
            $headers['signature'] = $this->decode($signature);
        } else if ($api === 'privateV3') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce();
            $secret = base64_decode($this->secret); // or stringToBase64
            $pathWithLeadingSlash = '/v3' . $uri;
            $query = $this->keysort($this->omit($params, $this->extract_params($path)));
            if ($method !== 'GET') {
                $body = $this->json($query);
            } else {
                $queryString = '';
                if ($query) {
                    $queryString = $this->urlencode($query);
                    $url .= '?' . $queryString;
                }
            }
            $auth = null;
            if ($body) {
                $auth = $method . $pathWithLeadingSlash . $nonce . $body;
            } else {
                $auth = $method . $pathWithLeadingSlash . $nonce;
            }
            $signature = $this->hmac($this->encode($auth), $secret, 'sha512', 'base64');
            $headers = array(
                'Accept' => 'application/json',
                'Accept-Charset' => 'UTF-8',
                'Content-Type' => 'application/json',
                'BM-AUTH-APIKEY' => $this->apiKey,
                'BM-AUTH-TIMESTAMP' => $nonce,
                'BM-AUTH-SIGNATURE' => $signature,
            );
        } else {
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return; // fallback to default $error handler
        }
        if (is_array($response) && array_key_exists('success', $response)) {
            if (!$response['success']) {
                $error = $this->safe_string($response, 'errorCode');
                $feedback = $this->id . ' ' . $body;
                $this->throw_exactly_matched_exception($this->exceptions, $error, $feedback);
                throw new ExchangeError($feedback);
            }
        }
        // v3 api errors
        if ($code >= 400) {
            $errorCode = $this->safe_string($response, 'code');
            $message = $this->safe_string($response, 'message');
            $feedback = $this->id . ' ' . $body;
            $this->throw_exactly_matched_exception($this->exceptions, $errorCode, $feedback);
            $this->throw_exactly_matched_exception($this->exceptions, $message, $feedback);
            throw new ExchangeError($feedback);
        }
    }
}
