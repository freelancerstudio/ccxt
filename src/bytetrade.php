<?php

namespace ccxt;

use Exception as Exception; // a common import

class bytetrade extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bytetrade',
            'name' => 'ByteTrade',
            'countries' => ['HK'],
            'rateLimit' => 500,
            'requiresWeb3' => true,
            'certified' => true,
            // new metainfo interface
            'has' => array (
                'fetchCurrencies' => true,
                'fetchDepositAddress' => true,
                'CORS' => false,
                'fetchBidsAsks' => true,
                'fetchTickers' => true,
                'fetchOHLCV' => true,
                'fetchMyTrades' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'withdraw' => true,
                'fetchDeposits' => true,
                'fetchWithdrawals' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
                '5m' => '5m',
                '15m' => '15m',
                '30m' => '30m',
                '1h' => '1h',
                '4h' => '4h',
                '1d' => '1d',
                '5d' => '5d',
                '1w' => '1w',
                '1M' => '1M',
            ),
            'urls' => array (
                'test' => 'https://api-v2-test.bytetrade.com',
                'logo' => 'https://user-images.githubusercontent.com/1294454/67288762-2f04a600-f4e6-11e9-9fd6-c60641919491.jpg',
                'api' => 'https://api-v2.bytetrade.com',
                'www' => 'https://www.bytetrade.com',
                'doc' => 'https://github.com/Bytetrade/bytetrade-official-api-docs/wiki',
            ),
            'api' => array (
                'market' => array (
                    'get' => array (
                        'klines',        // Kline of a symbol
                        'depth',         // Market Depth of a symbol
                        'trades',        // Trade records of a symbol
                        'tickers',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'symbols',        // Reference information of trading instrument, including base currency, quote precision, etc.
                        'currencies',     // The list of currencies available
                        'balance',        // Get the balance of an account
                        'orders/open',    // Get the open orders of an account
                        'orders/closed',  // Get the closed orders of an account
                        'orders/all',     // Get the open and closed orders of an account
                        'orders',         // Get the details of an order of an account
                        'orders/trades',  // Get detail match results
                        'depositaddress', // Get deposit address
                        'withdrawals',    // Get withdrawals info
                        'deposits',       // Get deposit info
                        'transfers',      // Get transfer info
                    ),
                    'post' => array (
                        'transaction/createorder',    // Post create order transaction to blockchain
                        'transaction/cancelorder',    // Post cancel order transaction to blockchain
                        'transaction/withdraw',       // Post withdraw transaction to blockchain
                        'transaction/transfer',       // Post transfer transaction to blockchain
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'taker' => 0.0008,
                    'maker' => 0.0008,
                ),
            ),
            'commonCurrencies' => array (
                '48' => 'Blocktonic',
            ),
            'exceptions' => array (
                'vertify error' => '\\ccxt\\AuthenticationError', // typo on the exchange side, 'vertify'
                'verify error' => '\\ccxt\\AuthenticationError', // private key signature is incorrect
                'transaction already in network' => '\\ccxt\\BadRequest', // same transaction submited
                'invalid argument' => '\\ccxt\\BadRequest',
            ),
        ));
    }

    public function fetch_currencies ($params = array ()) {
        $currencies = $this->publicGetCurrencies ($params);
        $result = array();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $id = $this->safe_string($currency, 'code');
            $code = null;
            if (is_array($this->commonCurrencies) && array_key_exists($id, $this->commonCurrencies)) {
                $code = $this->commonCurrencies[$id];
            } else {
                $code = $this->safe_string($currency, 'name');
            }
            $name = $this->safe_string($currency, 'fullname');
            // in bytetrade.com DEX, request https://api-v2.bytetrade.com/currencies will return $currencies,
            // the api doc is https://github.com/Bytetrade/bytetrade-official-api-docs/wiki/rest-api#get-$currencies-get-currencys-supported-in-bytetradecom
            // we can see the coin $name is none-unique in the $result, the coin which $code is 18 is the CyberMiles ERC20, and the coin which $code is 35 is the CyberMiles main chain, but their $name is same.
            // that is because bytetrade is a DEX, supports people create coin with the same $name, but the $id($code) of coin is unique, so we should use the $id or $name and $id as the identity of coin.
            // For coin $name and symbol is same with CCXT, I use $name@$id as the key of commonCurrencies dict.
            // [{
            //     "$name" => "CMT",      // $currency $name, non-unique
            //     "$code" => "18",       // $currency $id, unique
            //     "type" => "crypto",
            //     "fullname" => "CyberMiles",
            //     "$active" => true,
            //     "chainType" => "ethereum",
            //     "basePrecision" => 18,
            //     "transferPrecision" => 10,
            //     "externalPrecision" => 18,
            //     "chainContractAddress" => "0xf85feea2fdd81d51177f6b8f35f0e6734ce45f5f",
            //     "$limits" => {
            //       "$deposit" => array (
            //         "min" => "0",
            //         "max" => "-1"
            //       ),
            //       "$withdraw" => array (
            //         "min" => "0",
            //         "max" => "-1"
            //       }
            //     }
            //   ),
            //   {
            //     "$name" => "CMT",
            //     "$code" => "35",
            //     "type" => "crypto",
            //     "fullname" => "CyberMiles",
            //     "$active" => true,
            //     "chainType" => "cmt",
            //     "basePrecision" => 18,
            //     "transferPrecision" => 10,
            //     "externalPrecision" => 18,
            //     "chainContractAddress" => "0x0000000000000000000000000000000000000000",
            //     "$limits" => {
            //       "$deposit" => array (
            //         "min" => "1",
            //         "max" => "-1"
            //       ),
            //       "$withdraw" => {
            //         "min" => "10",
            //         "max" => "-1"
            //       }
            //     }
            //   }
            //   ]
            $active = $this->safe_value($currency, 'active');
            $limits = $this->safe_value($currency, 'limits');
            $deposit = $this->safe_value($limits, 'deposit');
            $amountPrecision = $this->safe_integer($currency, 'basePrecision');
            $maxDeposit = $this->safe_float($deposit, 'max');
            if ($maxDeposit === -1.0) {
                $maxDeposit = null;
            }
            $withdraw = $this->safe_value($limits, 'withdraw');
            $maxWithdraw = $this->safe_float($withdraw, 'max');
            if ($maxWithdraw === -1.0) {
                $maxWithdraw = null;
            }
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'active' => $active,
                'precision' => array (
                    'amount' => $amountPrecision,
                    'price' => null,
                ),
                'fee' => null,
                'limits' => array (
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
                    'deposit' => array (
                        'min' => $this->safe_float($deposit, 'min'),
                        'max' => $maxDeposit,
                    ),
                    'withdraw' => array (
                        'min' => $this->safe_float($withdraw, 'min'),
                        'max' => $maxWithdraw,
                    ),
                ),
                'info' => $currency,
            );
        }
        return $result;
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->publicGetSymbols ($params);
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'symbol');
            // there may be duplicate codes
            $base = $this->safe_string($market, 'baseName');
            $quote = $this->safe_string($market, 'quoteName');
            $baseId = $this->safe_string($market, 'base');
            $quoteId = $this->safe_string($market, 'quote');
            if (is_array($this->commonCurrencies) && array_key_exists($baseId, $this->commonCurrencies)) {
                $base = $this->commonCurrencies[$baseId];
            }
            if (is_array($this->commonCurrencies) && array_key_exists($quoteId, $this->commonCurrencies)) {
                $quote = $this->commonCurrencies[$quoteId];
            }
            $symbol = $base . '/' . $quote;
            $amountMin = $this->safe_float($market['limits']['amount'], 'min');
            $amountMax = $this->safe_float($market['limits']['amount'], 'max');
            $priceMin = $this->safe_float($market['limits']['price'], 'min');
            $priceMax = $this->safe_float($market['limits']['price'], 'max');
            $precision = array (
                'amount' => $this->safe_integer($market['precision'], 'amount'),
                'price' => $this->safe_integer($market['precision'], 'price'),
            );
            $active = $this->safe_string($market, 'active');
            $normalBase = explode('@', $base)[0];
            $normalQuote = explode('@', $quote)[0];
            $normalSymbol = $normalBase . '/' . $normalQuote;
            $entry = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'info' => $market,
                'active' => $active,
                'precision' => $precision,
                'normalSymbol' => $normalSymbol,
                'limits' => array (
                    'amount' => array (
                        'min' => $amountMin,
                        'max' => $amountMax,
                    ),
                    'price' => array (
                        'min' => $priceMin,
                        'max' => $priceMax,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
            $result[] = $entry;
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired($this->id . ' fetchDeposits requires $this->apiKey or userid argument');
        }
        $this->load_markets();
        $request = array (
            'userid' => $this->apiKey,
        );
        $balances = $this->publicGetBalance (array_merge ($request, $params));
        $result = array( 'info' => $balances );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $this->safe_string($balance, 'code');
            $code = $this->safe_currency_code($currencyId, null);
            $account = $this->account ();
            $account['free'] = $this->safe_float($balance, 'free');
            $account['used'] = $this->safe_float($balance, 'used');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        if ($limit !== null) {
            $request['limit'] = $limit; // default = maximum = 100
        }
        $response = $this->marketGetDepth (array_merge ($request, $params));
        $timestamp = $this->safe_value($response, 'timestamp');
        $orderbook = $this->parse_order_book($response, $timestamp);
        return $orderbook;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->safe_integer($ticker, 'timestamp');
        $symbol = $this->find_symbol($this->safe_string($ticker, 'symbol'), $market);
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => null,
            'bidVolume' => null,
            'ask' => null,
            'askVolume' => null,
            'vwap' => $this->safe_float($ticker, 'weightedAvgPrice'),
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $this->safe_float($ticker, 'close'),
            'last' => $this->safe_float($ticker, 'last'),
            'previousClose' => null, // previous day close
            'change' => $this->safe_float($ticker, 'change'),
            'percentage' => $this->safe_float($ticker, 'percentage'),
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'baseVolume'),
            'quoteVolume' => $this->safe_float($ticker, 'quoteVolume'),
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->marketGetTickers (array_merge ($request, $params));
        if (strlen ($response) > 0) {
            return $this->parse_ticker($response[0], $market);
        }
        return $this->parse_ticker($response, $market);
    }

    public function parse_tickers ($rawTickers, $symbols = null) {
        $tickers = array();
        for ($i = 0; $i < count ($rawTickers); $i++) {
            $tickers[] = $this->parse_ticker($rawTickers[$i]);
        }
        return $this->filter_by_array($tickers, 'symbol', $symbols);
    }

    public function fetch_bids_asks ($symbols = null, $params = array ()) {
        $this->load_markets();
        $rawTickers = $this->marketGetDepth ($params);
        return $this->parse_tickers ($rawTickers, $symbols);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $rawTickers = $this->marketGetTickers ($params);
        return $this->parse_tickers ($rawTickers, $symbols);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv[0],
            floatval ($ohlcv[1]),
            floatval ($ohlcv[2]),
            floatval ($ohlcv[3]),
            floatval ($ohlcv[4]),
            floatval ($ohlcv[5]),
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
        );
        if ($since !== null) {
            $request['since'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->marketGetKlines (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_integer($trade, 'timestamp');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = $this->safe_float($trade, 'cost');
        $id = $this->safe_string($trade, 'id');
        $type = $this->safe_string($trade, 'type');
        $takerOrMaker = $this->safe_string($trade, 'takerOrMaker');
        $side = $this->safe_string($trade, 'side');
        $datetime = $this->safe_string($trade, 'datetime');
        $order = $this->safe_string($trade, 'order');
        $fee = $this->safe_value($trade, 'fee');
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($trade, 'symbol');
            $market = $this->safe_value($this->markets_by_id, $marketId);
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $datetime,
            'symbol' => $symbol,
            'id' => $id,
            'order' => $order,
            'type' => $type,
            'takerOrMaker' => $takerOrMaker,
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
        $request = array (
            'symbol' => $market['id'],
        );
        if ($since !== null) {
            $request['since'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit; // default = 100, maximum = 500
        }
        $response = $this->marketGetTrades (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_order ($order, $market = null) {
        $status = $this->safe_string($order, 'status');
        $symbol = $this->find_symbol($this->safe_string($order, 'symbol'), $market);
        $timestamp = $this->safe_integer($order, 'timestamp');
        $datetime = $this->safe_string($order, 'datetime');
        $lastTradeTimestamp = $this->safe_integer($order, 'lastTradeTimestamp');
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'amount');
        $filled = $this->safe_float($order, 'filled');
        $remaining = $this->safe_float($order, 'remaining');
        $cost = $this->safe_float($order, 'cost');
        $average = $this->safe_float($order, 'average');
        $id = $this->safe_string($order, 'id');
        $type = $this->safe_string($order, 'type');
        $side = $this->safe_string($order, 'side');
        $fee = $this->safe_value($order, 'fee');
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $datetime,
            'lastTradeTimestamp' => $lastTradeTimestamp,
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
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->check_required_dependencies();
        if ($this->apiKey === null) {
            throw new ArgumentsRequired('createOrder requires $this->apiKey or userid in params');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $sideNum = null;
        $typeNum = null;
        if ($side === 'sell') {
            $sideNum = 1;
        } else {
            $sideNum = 2;
        }
        if ($type === 'limit') {
            $typeNum = 1;
        } else {
            $typeNum = 2;
        }
        $normalSymbol = $market['normalSymbol'];
        $baseId = $market['baseId'];
        $baseCurrency = $this->currency ($market['base']);
        $amountTruncated = $this->amount_to_precision($symbol, $amount);
        $amountChain = $this->toWei ($amountTruncated, 'ether', $baseCurrency['precision']['amount']);
        $quoteId = $market['quoteId'];
        $quoteCurrency = $this->currency ($market['quote']);
        $priceRounded = $this->price_to_precision($symbol, $price);
        $priceChain = $this->toWei ($priceRounded, 'ether', $quoteCurrency['precision']['amount']);
        $now = $this->milliseconds ();
        $expiration = $this->milliseconds ();
        $datetime = $this->iso8601 ($now);
        $datetime = explode('.', $datetime)[0];
        $expirationDatetime = $this->iso8601 ($expiration);
        $expirationDatetime = explode('.', $expirationDatetime)[0];
        $chainName = 'Sagittarius';
        $defaultFee = $this->safe_string($this->options, 'fee', '300000000000000');
        $fee = $this->safe_string($params, 'fee', $defaultFee);
        $eightBytes = $this->integer_pow ('2', '64');
        $byteStringArray = array (
            $this->numberToBE (1, 32),
            $this->numberToLE ((int) floor($now / 1000), 4),
            $this->numberToLE (1, 1),
            $this->numberToLE ((int) floor($expiration / 1000), 4),
            $this->numberToLE (1, 1),
            $this->numberToLE (32, 1),
            $this->numberToLE (0, 8),
            $this->numberToLE ($fee, 8),  // string for 32 bit php
            $this->numberToLE (strlen ($this->apiKey), 1),
            $this->encode ($this->apiKey),
            $this->numberToLE ($sideNum, 1),
            $this->numberToLE ($typeNum, 1),
            $this->numberToLE (strlen ($normalSymbol), 1),
            $this->encode ($normalSymbol),
            $this->numberToLE ($this->integer_divide ($amountChain, $eightBytes), 8),
            $this->numberToLE ($this->integer_modulo ($amountChain, $eightBytes), 8),
            $this->numberToLE ($this->integer_divide ($priceChain, $eightBytes), 8),
            $this->numberToLE ($this->integer_modulo ($priceChain, $eightBytes), 8),
            $this->numberToLE (0, 2),
            $this->numberToLE ((int) floor($now / 1000), 4),
            $this->numberToLE ((int) floor($expiration / 1000), 4),
            $this->numberToLE (0, 2),
            $this->numberToLE (intval ($quoteId), 4),
            $this->numberToLE (intval ($baseId), 4),
            $this->numberToLE (0, 1),
            $this->numberToLE (1, 1),
            $this->numberToLE (strlen ($chainName), 1),
            $this->encode ($chainName),
            $this->numberToLE (0, 1),
        );
        $bytestring = $this->binary_concat_array($byteStringArray);
        $hash = $this->hash ($bytestring, 'sha256', 'hex');
        $signature = $this->ecdsa ($hash, $this->secret, 'secp256k1', null, true);
        $recoveryParam = $this->decode (bin2hex($this->numberToLE ($this->sum ($signature['v'], 31), 1)));
        $mySignature = $recoveryParam . $signature['r'] . $signature['s'];
        $operation = array (
            'now' => $datetime,
            'expiration' => $expirationDatetime,
            'fee' => $fee,
            'creator' => $this->apiKey,
            'side' => $sideNum,
            'order_type' => $typeNum,
            'market_name' => $normalSymbol,
            'amount' => $amountChain,
            'price' => $priceChain,
            'use_btt_as_fee' => false,
            'money_id' => intval ($quoteId),
            'stock_id' => intval ($baseId),
        );
        $fatty = array (
            'timestamp' => $datetime,
            'expiration' => $expirationDatetime,
            'operations' => array (
                array (
                    32,
                    $operation,
                ),
            ),
            'validate_type' => 0,
            'dapp' => 'Sagittarius',
            'signatures' => array (
                $mySignature,
            ),
        );
        $request = array (
            'trObj' => $this->json ($fatty),
        );
        $response = $this->publicPostTransactionCreateorder ($request);
        $timestamp = $this->milliseconds ();
        $statusCode = $this->safe_string ($response, 'code');
        $status = ($statusCode === '0') ? 'open' : 'failed';
        return array (
            'info' => $response,
            'id' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => null,
            'type' => null,
            'side' => null,
            'price' => null,
            'amount' => null,
            'filled' => null,
            'remaining' => null,
            'cost' => null,
            'trades' => null,
            'fee' => null,
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchOrder requires $this->apiKey or userid argument');
        }
        $this->load_markets();
        $request = array();
        $market = null;
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($symbol !== null) {
            $market = $this->markets[$symbol];
            $request['symbol'] = $market['id'];
        }
        $request['id'] = $id;
        $response = $this->publicGetOrders (array_merge ($request, $params));
        return $this->parse_order($response, $market);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchOpenOrders requires $this->apiKey or userid argument');
        }
        $this->load_markets();
        $market = null;
        $request = array();
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetOrdersOpen (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchClosedOrders requires $this->apiKey or userid argument');
        }
        $this->load_markets();
        $market = null;
        $request = array( );
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetOrdersClosed (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchOrders requires $this->apiKey or userid argument');
        }
        $this->load_markets();
        $market = null;
        $request = array();
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetOrdersAll (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($this->apiKey === null) {
            throw new ArgumentsRequired('cancelOrder requires hasAlreadyAuthenticatedSuccessfully');
        }
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $baseId = $market['baseId'];
        $quoteId = $market['quoteId'];
        $normalSymbol = $market['normalSymbol'];
        $feeAmount = '300000000000000';
        $now = $this->milliseconds ();
        $expiration = 0;
        $datetime = $this->iso8601 ($now);
        $datetime = explode('.', $datetime)[0];
        $expirationDatetime = $this->iso8601 ($expiration);
        $expirationDatetime = explode('.', $expirationDatetime)[0];
        $chainName = 'Sagittarius';
        $byteStringArray = array (
            $this->numberToBE (1, 32),
            $this->numberToLE ((int) floor($now / 1000), 4),
            $this->numberToLE (1, 1),
            $this->numberToLE ($expiration, 4),
            $this->numberToLE (1, 1),
            $this->numberToLE (33, 1),
            $this->numberToLE (0, 8),
            $this->numberToLE ($feeAmount, 8),  // string for 32 bit php
            $this->numberToLE (strlen ($this->apiKey), 1),
            $this->encode ($this->apiKey),
            $this->numberToLE (strlen ($normalSymbol), 1),
            $this->encode ($normalSymbol),
            $this->base16ToBinary ($id),
            $this->numberToLE (intval ($quoteId), 4),
            $this->numberToLE (intval ($baseId), 4),
            $this->numberToLE (0, 1),
            $this->numberToLE (1, 1),
            $this->numberToLE (strlen ($chainName), 1),
            $this->encode ($chainName),
            $this->numberToLE (0, 1),
        );
        $bytestring = $this->binary_concat_array($byteStringArray);
        $hash = $this->hash ($bytestring, 'sha256', 'hex');
        $signature = $this->ecdsa ($hash, $this->secret, 'secp256k1', null, true);
        $recoveryParam = $this->decode (bin2hex($this->numberToLE ($this->sum ($signature['v'], 31), 1)));
        $mySignature = $recoveryParam . $signature['r'] . $signature['s'];
        $operation = array (
            'fee' => $feeAmount,
            'creator' => $this->apiKey,
            'order_id' => $id,
            'market_name' => $normalSymbol,
            'money_id' => intval ($quoteId),
            'stock_id' => intval ($baseId),
        );
        $fatty = array (
            'timestamp' => $datetime,
            'expiration' => $expirationDatetime,
            'operations' => array (
                array (
                    33,
                    $operation,
                ),
            ),
            'validate_type' => 0,
            'dapp' => 'Sagittarius',
            'signatures' => array (
                $mySignature,
            ),
        );
        $request = array (
            'trObj' => $this->json ($fatty),
        );
        $response = $this->publicPostTransactionCancelorder ($request);
        $timestamp = $this->milliseconds ();
        $statusCode = $this->safe_string ($response, 'code');
        $status = ($statusCode === '0') ? 'canceled' : 'failed';
        return array (
            'info' => $response,
            'id' => '',
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => null,
            'type' => null,
            'side' => null,
            'price' => null,
            'amount' => null,
            'filled' => null,
            'remaining' => null,
            'cost' => null,
            'trades' => null,
            'fee' => null,
        );
    }

    public function transfer ($code, $amount, $address, $params = array ()) {
        $this->check_required_dependencies();
        if ($this->apiKey === null) {
            throw new ArgumentsRequired('transfer requires $this->apiKey');
        }
        $this->load_markets();
        $currency = $this->currency ($code);
        $amountTruncate = $this->decimal_to_precision($amount, TRUNCATE, $currency['info']['transferPrecision'], DECIMAL_PLACES, NO_PADDING);
        $amountChain = $this->toWei ($amountTruncate, 'ether', $currency['precision']['amount']);
        $assetType = intval ($currency['id']);
        $now = $this->milliseconds ();
        $expiration = $now;
        $datetime = $this->iso8601 ($now);
        $datetime = explode('.', $datetime)[0];
        $expirationDatetime = $this->iso8601 ($expiration);
        $expirationDatetime = explode('.', $expirationDatetime)[0];
        $feeAmount = '300000000000000';
        $chainName = 'Sagittarius';
        $eightBytes = $this->integer_pow ('2', '64');
        $byteStringArray = array (
            $this->numberToBE (1, 32),
            $this->numberToLE ((int) floor($now / 1000), 4),
            $this->numberToLE (1, 1),
            $this->numberToLE ((int) floor($expiration / 1000), 4),
            $this->numberToLE (1, 1),
            $this->numberToLE (0, 1),
            $this->numberToLE (0, 8),
            $this->numberToLE ($feeAmount, 8),  // string for 32 bit php
            $this->numberToLE (strlen ($this->apiKey), 1),
            $this->encode ($this->apiKey),
            $this->numberToLE (strlen ($address), 1),
            $this->encode ($address),
            $this->numberToLE ($assetType, 4),
            $this->numberToLE ($this->integer_divide ($amountChain, $eightBytes), 8),
            $this->numberToLE ($this->integer_modulo ($amountChain, $eightBytes), 8),
            $this->numberToLE (0, 1),
            $this->numberToLE (1, 1),
            $this->numberToLE (strlen ($chainName), 1),
            $this->encode ($chainName),
            $this->numberToLE (0, 1),
        );
        $bytestring = $this->binary_concat_array($byteStringArray);
        $hash = $this->hash ($bytestring, 'sha256', 'hex');
        $signature = $this->ecdsa ($hash, $this->secret, 'secp256k1', null, true);
        $recoveryParam = $this->decode (bin2hex($this->numberToLE ($this->sum ($signature['v'], 31), 1)));
        $mySignature = $recoveryParam . $signature['r'] . $signature['s'];
        $operation = array (
            'fee' => '300000000000000',
            'from' => $this->apiKey,
            'to' => $address,
            'asset_type' => intval ($currency['id']),
            'amount' => (string) $amountChain,
        );
        $fatty = array (
            'timestamp' => $datetime,
            'expiration' => $expirationDatetime,
            'operations' => array (
                array (
                    0,
                    $operation,
                ),
            ),
            'validate_type' => 0,
            'dapp' => 'Sagittarius',
            'signatures' => array (
                $mySignature,
            ),
        );
        $request = array (
            'trObj' => $this->json ($fatty),
        );
        $response = $this->publicPostTransactionTransfer ($request);
        $timestamp = $this->milliseconds ();
        $statusCode = $this->safe_string ($response, 'code');
        $status = '';
        if ($statusCode === '0') {
            $status = 'submit success';
        } else {
            $status = 'submit fail';
        }
        return array (
            'info' => $response,
            'id' => '',
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => null,
            'type' => null,
            'side' => null,
            'price' => null,
            'amount' => null,
            'filled' => null,
            'remaining' => null,
            'cost' => null,
            'fee' => null,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchMyTrades requires $this->apiKey or userid argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array();
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($symbol !== null) {
            $request['symbol'] = $market['id'];
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetOrdersTrades (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchDeposits requires $this->apiKey or userid argument');
        }
        $currency = null;
        $request = array( );
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($code !== null) {
            $currency = $this->currency ($code);
            $request['currency'] = $currency['id'];
        }
        if ($since !== null) {
            $request['since'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetDeposits (array_merge ($request, $params));
        return $this->parse_transactions($response, $currency, $since, $limit);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchWithdrawals requires $this->apiKey or userid argument');
        }
        $currency = null;
        $request = array();
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        if ($code !== null) {
            $currency = $this->currency ($code);
            $request['currency'] = $currency['id'];
        }
        if ($since !== null) {
            $request['since'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetWithdrawals (array_merge ($request, $params));
        return $this->parse_transactions($response, $currency, $since, $limit);
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'DEPOSIT_FAILED' => 'failed',
            'FEE_SEND_FAILED' => 'failed',
            'FEE_FAILED' => 'failed',
            'PAY_SEND_FAILED' => 'failed',
            'PAY_FAILED' => 'failed',
            'BTT_FAILED' => 'failed',
            'WITHDDRAW_FAILED' => 'failed',
            'USER_FAILED' => 'failed',
            'FEE_EXECUED' => 'pending',
            'PAY_EXECUED' => 'pending',
            'WITHDDRAW_EXECUTED' => 'pending',
            'USER_EXECUED' => 'pending',
            'BTT_SUCCED' => 'ok',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_transaction ($transaction, $currency = null) {
        $id = $this->safe_string($transaction, 'id');
        $address = $this->safe_string($transaction, 'address');
        $tag = $this->safe_string($transaction, 'tag');
        if ($tag !== null) {
            if (strlen ($tag) < 1) {
                $tag = null;
            }
        }
        $txid = $this->safe_value($transaction, 'txid');
        $currencyId = $this->safe_string($transaction, 'code');
        $code = $this->safe_currency_code($currencyId, $currency);
        $timestamp = $this->safe_integer($transaction, 'timestamp');
        $datetime = $this->safe_string($transaction, 'datetime');
        $type = $this->safe_string($transaction, 'type');
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'status'));
        $amount = $this->safe_float($transaction, 'amount');
        $feeInfo = $this->safe_value($transaction, 'fee');
        $feeCost = $this->safe_float($feeInfo, 'cost');
        $feeCurrencyId = $this->safe_string($feeInfo, 'code');
        $feeCode = $this->safe_currency_code($feeCurrencyId, $currency);
        $fee = array (
            'cost' => $feeCost,
            'currency' => $feeCode,
        );
        return array (
            'info' => $transaction,
            'id' => $id,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $datetime,
            'address' => $address,
            'tag' => $tag,
            'type' => $type,
            'amount' => $amount,
            'currency' => $code,
            'status' => $status,
            'updated' => null,
            'fee' => $fee,
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        if (!(is_array($params) && array_key_exists('userid', $params)) && ($this->apiKey === null)) {
            throw new ArgumentsRequired('fetchDepositAddress requires $this->apiKey or userid argument');
        }
        $currency = $this->currency ($code);
        $request = array( );
        if (is_array($params) && array_key_exists('userid', $params)) {
            $request['userid'] = $params['userid'];
        } else {
            $request['userid'] = $this->apiKey;
        }
        $request['code'] = $currency['id'];
        $response = $this->publicGetDepositaddress ($request);
        $address = $this->safe_string($response[0], 'address');
        $tag = $this->safe_string($response[0], 'addressTag');
        $chainType = $this->safe_string($response[0], 'chainType');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'chainType' => $chainType,
            'info' => $response,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_required_dependencies();
        $this->check_address($address);
        $this->load_markets();
        if ($this->apiKey === null) {
            throw new ArgumentsRequired('withdraw requires $this->apiKey');
        }
        $addressResponse = $this->fetch_deposit_address ($code);
        $middleAddress = $this->safe_string($addressResponse, 'address');
        $chainTypeString = $this->safe_string($addressResponse, 'chainType');
        $chainType = 0;
        $operationId = 18;
        if ($chainTypeString === 'ethereum') {
            $chainType = 1;
        } else if ($chainTypeString === 'bitcoin') {
            $chainType = 2;
            $operationId = 26;
        } else if ($chainTypeString === 'cmt') {
            $chainType = 3;
        } else if ($chainTypeString === 'naka') {
            $chainType = 4;
        } else {
            throw new ExchangeError($this->id . ' ' . $code . ' is not supported.');
        }
        $now = $this->milliseconds ();
        $expiration = 0;
        $datetime = $this->iso8601 ($now);
        $datetime = explode('.', $datetime)[0];
        $expirationDatetime = $this->iso8601 ($expiration);
        $expirationDatetime = explode('.', $expirationDatetime)[0];
        $chainName = 'Sagittarius';
        $feeAmount = '300000000000000';
        $currency = $this->currency ($code);
        $coinId = $currency['id'];
        $amountTruncate = $this->decimal_to_precision($amount, TRUNCATE, $currency['info']['transferPrecision'], DECIMAL_PLACES, NO_PADDING);
        $amountChain = $this->toWei ($amountTruncate, 'ether', $currency['info']['externalPrecision']);
        $eightBytes = $this->integer_pow ('2', '64');
        $assetFee = 0;
        $byteStringArray = array();
        if ($chainTypeString === 'bitcoin') {
            $assetFee = $currency['info']['fee'];
            $byteStringArray = array (
                $this->numberToBE (1, 32),
                $this->numberToLE ((int) floor($now / 1000), 4),
                $this->numberToLE (1, 1),
                $this->numberToLE ((int) floor($expiration / 1000), 4),
                $this->numberToLE (1, 1),
                $this->numberToLE ($operationId, 1),
                $this->numberToLE (0, 8),
                $this->numberToLE ($feeAmount, 8),  // string for 32 bit php
                $this->numberToLE (strlen ($this->apiKey), 1),
                $this->encode ($this->apiKey),
                $this->numberToLE (strlen ($address), 1),
                $this->encode ($address),
                $this->numberToLE (intval ($coinId), 4),
                $this->numberToLE ((int) floor(intval (floatval ($this->integer_divide ($amountChain, $eightBytes)))), 8),
                $this->numberToLE ($this->integer_modulo ($amountChain, $eightBytes), 8),
                $this->numberToLE (1, 1),
                $this->numberToLE ($this->integer_divide ($assetFee, $eightBytes), 8),
                $this->numberToLE ($this->integer_modulo ($assetFee, $eightBytes), 8),
                $this->numberToLE (0, 1),
                $this->numberToLE (1, 1),
                $this->numberToLE (strlen ($chainName), 1),
                $this->encode ($chainName),
                $this->numberToLE (0, 1),
            );
        } else {
            $byteStringArray = array (
                $this->numberToBE (1, 32),
                $this->numberToLE ((int) floor($now / 1000), 4),
                $this->numberToLE (1, 1),
                $this->numberToLE ((int) floor($expiration / 1000), 4),
                $this->numberToLE (1, 1),
                $this->numberToLE ($operationId, 1),
                $this->numberToLE (0, 8),
                $this->numberToLE ($feeAmount, 8),  // string for 32 bit php
                $this->numberToLE (strlen ($this->apiKey), 1),
                $this->encode ($this->apiKey),
                $this->numberToLE ((int) floor($now / 1000), 4),
                $this->numberToLE (1, 1),
                $this->numberToLE (4, 1),
                $this->numberToLE (0, 8),
                $this->numberToLE ($feeAmount, 8),
                $this->numberToLE (strlen ($this->apiKey), 1),
                $this->encode ($this->apiKey),
                $this->numberToLE (strlen ($middleAddress), 1),
                $this->encode ($middleAddress),
                $this->numberToLE (intval ($coinId), 4),
                $this->numberToLE ((int) floor(intval (floatval ($this->integer_divide ($amountChain, $eightBytes)))), 8),
                $this->numberToLE ($this->integer_modulo ($amountChain, $eightBytes), 8),
                $this->numberToLE (0, 1),
                $this->numberToLE (1, 1),
                $this->numberToLE (strlen ($chainName), 1),
                $this->encode ($chainName),
                $this->numberToLE (0, 1),
            );
        }
        $bytestring = $this->binary_concat_array($byteStringArray);
        $hash = $this->hash ($bytestring, 'sha256', 'hex');
        $signature = $this->ecdsa ($hash, $this->secret, 'secp256k1', null, true);
        $recoveryParam = $this->decode (bin2hex($this->numberToLE ($this->sum ($signature['v'], 31), 1)));
        $mySignature = $recoveryParam . $signature['r'] . $signature['s'];
        $fatty = array( );
        $request = array( );
        $operation = array( );
        $chainContractAddress = $this->safe_string($currency['info'], 'chainContractAddress');
        if ($chainTypeString === 'bitcoin') {
            $operation = array (
                'fee' => $feeAmount,
                'from' => $this->apiKey,
                'to_external_address' => $address,
                'asset_type' => intval ($coinId),
                'amount' => $amountChain,
                'asset_fee' => $assetFee,
            );
            $fatty = array (
                'timestamp' => $datetime,
                'expiration' => $expirationDatetime,
                'operations' => array (
                    array (
                        $operationId,
                        $operation,
                    ),
                ),
                'validate_type' => 0,
                'dapp' => 'Sagittarius',
                'signatures' => array (
                    $mySignature,
                ),
            );
            $request = array (
                'chainType' => $chainType,
                'trObj' => $this->json ($fatty),
                'chainContractAddresss' => $chainContractAddress,
            );
        } else {
            $operation = array (
                'fee' => $feeAmount,
                'from' => $this->apiKey,
                'to_external_address' => $middleAddress,
                'asset_type' => intval ($coinId),
                'amount' => $amountChain,
                'asset_fee' => $assetFee,
            );
            $middle = array (
                'fee' => $feeAmount,
                'proposaler' => $this->apiKey,
                'expiration_time' => $datetime,
                'proposed_ops' => [array (
                    'op' => [4, $operation],
                )],
            );
            $fatty = array (
                'timestamp' => $datetime,
                'expiration' => $expirationDatetime,
                'operations' => array (
                    array (
                        $operationId,
                        $middle,
                    ),
                ),
                'validate_type' => 0,
                'dapp' => 'Sagittarius',
                'signatures' => array (
                    $mySignature,
                ),
            );
            $request = array (
                'chainType' => $chainType,
                'toExternalAddress' => $address,
                'trObj' => $this->json ($fatty),
                'chainContractAddresss' => $chainContractAddress,
            );
        }
        $response = $this->publicPostTransactionWithdraw ($request);
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'id'),
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'];
        $url .= '/' . $path;
        if ($params) {
            $url .= '?' . $this->urlencode ($params);
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if (($code === 503)) {
            throw new DDoSProtection($this->id . ' ' . (string) $code . ' ' . $reason . ' ' . $body);
        }
        if ($response === null) {
            return; // fallback to default error handler
        }
        if (is_array($response) && array_key_exists('code', $response)) {
            $status = $this->safe_string($response, 'code');
            if ($status === '1') {
                $msg = $this->safe_string($response, 'msg');
                $feedback = $this->id . ' ' . $this->json ($response);
                $exceptions = $this->exceptions;
                if (is_array($exceptions) && array_key_exists($msg, $exceptions)) {
                    throw new $exceptions[$msg]($feedback);
                }
                throw new ExchangeError($feedback);
            }
        }
    }
}
