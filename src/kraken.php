<?php

namespace ccxt;

class kraken extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'kraken',
            'name' => 'Kraken',
            'countries' => 'US',
            'version' => '0',
            'rateLimit' => 3000,
            'has' => array (
                'createDepositAddress' => true,
                'fetchDepositAddress' => true,
                'CORS' => false,
                'fetchCurrencies' => true,
                'fetchTickers' => true,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchMyTrades' => true,
                'withdraw' => true,
            ),
            'marketsByAltname' => array (),
            'timeframes' => array (
                '1m' => '1',
                '5m' => '5',
                '15m' => '15',
                '30m' => '30',
                '1h' => '60',
                '4h' => '240',
                '1d' => '1440',
                '1w' => '10080',
                '2w' => '21600',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766599-22709304-5ede-11e7-9de1-9f33732e1509.jpg',
                'api' => array (
                    'public' => 'https://api.kraken.com',
                    'private' => 'https://api.kraken.com',
                    'zendesk' => 'https://support.kraken.com/hc/en-us/articles',
                ),
                'www' => 'https://www.kraken.com',
                'doc' => array (
                    'https://www.kraken.com/en-us/help/api',
                    'https://github.com/nothingisdead/npm-kraken-api',
                ),
                'fees' => 'https://www.kraken.com/en-us/help/fees',
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => true,
                    'percentage' => true,
                    'taker' => 0.26 / 100,
                    'maker' => 0.16 / 100,
                    'tiers' => array (
                        'taker' => [
                            [0, 0.0026],
                            [50000, 0.0024],
                            [100000, 0.0022],
                            [250000, 0.0020],
                            [500000, 0.0018],
                            [1000000, 0.0016],
                            [2500000, 0.0014],
                            [5000000, 0.0012],
                            [10000000, 0.0001],
                        ],
                        'maker' => [
                            [0, 0.0016],
                            [50000, 0.0014],
                            [100000, 0.0012],
                            [250000, 0.0010],
                            [500000, 0.0008],
                            [1000000, 0.0006],
                            [2500000, 0.0004],
                            [5000000, 0.0002],
                            [10000000, 0.0],
                        ],
                    ),
                ),
                // this is a bad way of hardcoding fees that change on daily basis
                // hardcoding is now considered obsolete, we will remove all of it eventually
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array (
                        'BTC' => 0.001,
                        'ETH' => 0.005,
                        'XRP' => 0.02,
                        'XLM' => 0.00002,
                        'LTC' => 0.02,
                        'DOGE' => 2,
                        'ZEC' => 0.00010,
                        'ICN' => 0.02,
                        'REP' => 0.01,
                        'ETC' => 0.005,
                        'MLN' => 0.003,
                        'XMR' => 0.05,
                        'DASH' => 0.005,
                        'GNO' => 0.01,
                        'EOS' => 0.5,
                        'BCH' => 0.001,
                        'USD' => 5, // if domestic wire
                        'EUR' => 5, // if domestic wire
                        'CAD' => 10, // CAD EFT Withdrawal
                        'JPY' => 300, // if domestic wire
                    ),
                    'deposit' => array (
                        'BTC' => 0,
                        'ETH' => 0,
                        'XRP' => 0,
                        'XLM' => 0,
                        'LTC' => 0,
                        'DOGE' => 0,
                        'ZEC' => 0,
                        'ICN' => 0,
                        'REP' => 0,
                        'ETC' => 0,
                        'MLN' => 0,
                        'XMR' => 0,
                        'DASH' => 0,
                        'GNO' => 0,
                        'EOS' => 0,
                        'BCH' => 0,
                        'USD' => 5, // if domestic wire
                        'EUR' => 0, // free deposit if EUR SEPA Deposit
                        'CAD' => 5, // if domestic wire
                        'JPY' => 0, // Domestic Deposit (Free, ¥5,000 deposit minimum)
                    ),
                ),
            ),
            'api' => array (
                'zendesk' => array (
                    'get' => array (
                        // we should really refrain from putting fixed fee numbers and stop hardcoding
                        // we will be using their web APIs to scrape all numbers from these articles
                        '205893708-What-is-the-minimum-order-size-',
                        '201396777-What-are-the-deposit-fees-',
                        '201893608-What-are-the-withdrawal-fees-',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'Assets',
                        'AssetPairs',
                        'Depth',
                        'OHLC',
                        'Spread',
                        'Ticker',
                        'Time',
                        'Trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'AddOrder',
                        'Balance',
                        'CancelOrder',
                        'ClosedOrders',
                        'DepositAddresses',
                        'DepositMethods',
                        'DepositStatus',
                        'Ledgers',
                        'OpenOrders',
                        'OpenPositions',
                        'QueryLedgers',
                        'QueryOrders',
                        'QueryTrades',
                        'TradeBalance',
                        'TradesHistory',
                        'TradeVolume',
                        'Withdraw',
                        'WithdrawCancel',
                        'WithdrawInfo',
                        'WithdrawStatus',
                    ),
                ),
            ),
            'options' => array (
                'cacheDepositMethodsOnFetchDepositAddress' => true, // will issue up to two calls in fetchDepositAddress
                'depositMethods' => array (),
            ),
        ));
    }

    public function cost_to_precision ($symbol, $cost) {
        return $this->truncate (floatval ($cost), $this->markets[$symbol]['precision']['price']);
    }

    public function fee_to_precision ($symbol, $fee) {
        return $this->truncate (floatval ($fee), $this->markets[$symbol]['precision']['amount']);
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (mb_strpos ($body, 'Invalid order') !== false)
            throw new InvalidOrder ($this->id . ' ' . $body);
        if (mb_strpos ($body, 'Invalid nonce') !== false)
            throw new InvalidNonce ($this->id . ' ' . $body);
        if (mb_strpos ($body, 'Insufficient funds') !== false)
            throw new InsufficientFunds ($this->id . ' ' . $body);
        if (mb_strpos ($body, 'Cancel pending') !== false)
            throw new CancelPending ($this->id . ' ' . $body);
        if (mb_strpos ($body, 'Invalid arguments:volume') !== false)
            throw new InvalidOrder ($this->id . ' ' . $body);
    }

    public function fetch_min_order_sizes () {
        $html = null;
        try {
            $this->parseJsonResponse = false;
            $html = $this->zendeskGet205893708WhatIsTheMinimumOrderSize ();
            $this->parseJsonResponse = true;
        } catch (Exception $e) {
            // ensure parseJsonResponse is restored no matter what
            $this->parseJsonResponse = true;
            throw $e;
        }
        $parts = explode ('ul>', $html);
        $ul = $parts[1];
        $listItems = explode ('</li', $ul);
        $result = array ();
        $separator = '):' . ' ';
        for ($l = 0; $l < count ($listItems); $l++) {
            $listItem = $listItems[$l];
            $chunks = explode ($separator, $listItem);
            $numChunks = is_array ($chunks) ? count ($chunks) : 0;
            if ($numChunks > 1) {
                $limit = floatval ($chunks[1]);
                $name = $chunks[0];
                $chunks = explode ('(', $name);
                $currency = $chunks[1];
                $result[$currency] = $limit;
            }
        }
        return $result;
    }

    public function fetch_markets () {
        $markets = $this->publicGetAssetPairs ();
        $limits = $this->fetch_min_order_sizes ();
        $keys = is_array ($markets['result']) ? array_keys ($markets['result']) : array ();
        $result = array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $id = $keys[$i];
            $market = $markets['result'][$id];
            $base = $market['base'];
            $quote = $market['quote'];
            if (($base[0] === 'X') || ($base[0] === 'Z'))
                $base = mb_substr ($base, 1);
            if (($quote[0] === 'X') || ($quote[0] === 'Z'))
                $quote = mb_substr ($quote, 1);
            $base = $this->common_currency_code($base);
            $quote = $this->common_currency_code($quote);
            $darkpool = mb_strpos ($id, '.d') !== false;
            $symbol = $darkpool ? $market['altname'] : ($base . '/' . $quote);
            $maker = null;
            if (is_array ($market) && array_key_exists ('fees_maker', $market)) {
                $maker = floatval ($market['fees_maker'][0][1]) / 100;
            }
            $precision = array (
                'amount' => $market['lot_decimals'],
                'price' => $market['pair_decimals'],
            );
            $minAmount = pow (10, -$precision['amount']);
            if (is_array ($limits) && array_key_exists ($base, $limits))
                $minAmount = $limits[$base];
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'darkpool' => $darkpool,
                'info' => $market,
                'altname' => $market['altname'],
                'maker' => $maker,
                'taker' => floatval ($market['fees'][0][1]) / 100,
                'active' => true,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $minAmount,
                        'max' => pow (10, $precision['amount']),
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision['price']),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => 0,
                        'max' => null,
                    ),
                ),
            );
        }
        $result = $this->append_inactive_markets($result);
        $this->marketsByAltname = $this->index_by($result, 'altname');
        return $result;
    }

    public function append_inactive_markets ($result = []) {
        $precision = array ( 'amount' => 8, 'price' => 8 );
        $costLimits = array ( 'min' => 0, 'max' => null );
        $priceLimits = array ( 'min' => pow (10, -$precision['price']), 'max' => null );
        $amountLimits = array ( 'min' => pow (10, -$precision['amount']), 'max' => pow (10, $precision['amount']) );
        $limits = array ( 'amount' => $amountLimits, 'price' => $priceLimits, 'cost' => $costLimits );
        $defaults = array (
            'darkpool' => false,
            'info' => null,
            'maker' => null,
            'taker' => null,
            'active' => false,
            'precision' => $precision,
            'limits' => $limits,
        );
        $markets = array (
            // array ( 'id' => 'XXLMZEUR', 'symbol' => 'XLM/EUR', 'base' => 'XLM', 'quote' => 'EUR', 'altname' => 'XLMEUR' ),
        );
        for ($i = 0; $i < count ($markets); $i++) {
            $result[] = array_merge ($defaults, $markets[$i]);
        }
        return $result;
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetAssets ($params);
        $currencies = $response['result'];
        $ids = is_array ($currencies) ? array_keys ($currencies) : array ();
        $result = array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $currency = $currencies[$id];
            // todo => will need to rethink the fees
            // to add support for multiple withdrawal/deposit methods and
            // differentiated fees for each particular method
            $code = $this->common_currency_code($currency['altname']);
            $precision = $currency['decimals'];
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => $code,
                'active' => true,
                'status' => 'ok',
                'fee' => null,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => pow (10, -$precision),
                        'max' => pow (10, $precision),
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision),
                        'max' => pow (10, $precision),
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'withdraw' => array (
                        'min' => null,
                        'max' => pow (10, $precision),
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_trading_fees ($params = array ()) {
        $this->load_markets();
        $this->check_required_credentials();
        $response = $this->privatePostTradeVolume ($params);
        $tradedVolume = $this->safe_float($response['result'], 'volume');
        $tiers = $this->fees['trading']['tiers'];
        $taker = $tiers['taker'][1];
        $maker = $tiers['maker'][1];
        for ($i = 0; $i < count ($tiers['taker']); $i++) {
            if ($tradedVolume >= $tiers['taker'][$i][0])
                $taker = $tiers['taker'][$i][1];
        }
        for ($i = 0; $i < count ($tiers['maker']); $i++) {
            if ($tradedVolume >= $tiers['maker'][$i][0])
                $maker = $tiers['maker'][$i][1];
        }
        return array (
            'info' => $response,
            'maker' => $maker,
            'taker' => $taker,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($market['darkpool'])
            throw new ExchangeError ($this->id . ' does not provide an order book for darkpool $symbol ' . $symbol);
        $request = array (
            'pair' => $market['id'],
        );
        if ($limit !== null)
            $request['count'] = $limit; // 100
        $response = $this->publicGetDepth (array_merge ($request, $params));
        $orderbook = $response['result'][$market['id']];
        return $this->parse_order_book($orderbook);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $baseVolume = floatval ($ticker['v'][1]);
        $vwap = floatval ($ticker['p'][1]);
        $quoteVolume = $baseVolume * $vwap;
        $last = floatval ($ticker['c'][0]);
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => floatval ($ticker['h'][1]),
            'low' => floatval ($ticker['l'][1]),
            'bid' => floatval ($ticker['b'][0]),
            'bidVolume' => null,
            'ask' => floatval ($ticker['a'][0]),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => floatval ($ticker['o']),
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
        $pairs = array ();
        for ($s = 0; $s < count ($this->symbols); $s++) {
            $symbol = $this->symbols[$s];
            $market = $this->markets[$symbol];
            if ($market['active'])
                if (!$market['darkpool'])
                    $pairs[] = $market['id'];
        }
        $filter = implode (',', $pairs);
        $response = $this->publicGetTicker (array_merge (array (
            'pair' => $filter,
        ), $params));
        $tickers = $response['result'];
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
        $darkpool = mb_strpos ($symbol, '.d') !== false;
        if ($darkpool)
            throw new ExchangeError ($this->id . ' does not provide a $ticker for $darkpool $symbol ' . $symbol);
        $market = $this->market ($symbol);
        $response = $this->publicGetTicker (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        $ticker = $response['result'][$market['id']];
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv[0] * 1000,
            floatval ($ohlcv[1]),
            floatval ($ohlcv[2]),
            floatval ($ohlcv[3]),
            floatval ($ohlcv[4]),
            floatval ($ohlcv[6]),
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair' => $market['id'],
            'interval' => $this->timeframes[$timeframe],
        );
        if ($since !== null)
            $request['since'] = intval ($since / 1000);
        $response = $this->publicGetOHLC (array_merge ($request, $params));
        $ohlcvs = $response['result'][$market['id']];
        return $this->parse_ohlcvs($ohlcvs, $market, $timeframe, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = null;
        $side = null;
        $type = null;
        $price = null;
        $amount = null;
        $id = null;
        $order = null;
        $fee = null;
        if (!$market)
            $market = $this->find_market_by_altname_or_id ($trade['pair']);
        if (is_array ($trade) && array_key_exists ('ordertxid', $trade)) {
            $order = $trade['ordertxid'];
            $id = $trade['id'];
            $timestamp = intval ($trade['time'] * 1000);
            $side = $trade['type'];
            $type = $trade['ordertype'];
            $price = floatval ($trade['price']);
            $amount = floatval ($trade['vol']);
            if (is_array ($trade) && array_key_exists ('fee', $trade)) {
                $currency = null;
                if ($market)
                    $currency = $market['quote'];
                $fee = array (
                    'cost' => floatval ($trade['fee']),
                    'currency' => $currency,
                );
            }
        } else {
            $timestamp = intval ($trade[2] * 1000);
            $side = ($trade[3] === 's') ? 'sell' : 'buy';
            $type = ($trade[4] === 'l') ? 'limit' : 'market';
            $price = floatval ($trade[0]);
            $amount = floatval ($trade[1]);
            $tradeLength = is_array ($trade) ? count ($trade) : 0;
            if ($tradeLength > 6)
                $id = $trade[6]; // artificially added as per #1794
        }
        $symbol = ($market) ? $market['symbol'] : null;
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
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $id = $market['id'];
        $response = $this->publicGetTrades (array_merge (array (
            'pair' => $id,
        ), $params));
        // array ( $result => { marketid => array ( ... $trades ) ), last => "last_trade_id"}
        $result = $response['result'];
        $trades = $result[$id];
        // $trades is a sorted array => last (most recent trade) goes last
        $length = is_array ($trades) ? count ($trades) : 0;
        if ($length <= 0)
            return array ();
        $lastTrade = $trades[$length - 1];
        $lastTradeId = $this->safe_string($result, 'last');
        $lastTrade[] = $lastTradeId;
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostBalance ();
        $balances = $response['result'];
        $result = array ( 'info' => $balances );
        $currencies = is_array ($balances) ? array_keys ($balances) : array ();
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $code = $currency;
            // X-ISO4217-A3 standard $currency codes
            if ($code[0] === 'X') {
                $code = mb_substr ($code, 1);
            } else if ($code[0] === 'Z') {
                $code = mb_substr ($code, 1);
            }
            $code = $this->common_currency_code($code);
            $balance = floatval ($balances[$currency]);
            $account = array (
                'free' => $balance,
                'used' => 0.0,
                'total' => $balance,
            );
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $order = array (
            'pair' => $market['id'],
            'type' => $side,
            'ordertype' => $type,
            'volume' => $this->amount_to_precision($symbol, $amount),
        );
        if ($type === 'limit')
            $order['price'] = $this->price_to_precision($symbol, $price);
        $response = $this->privatePostAddOrder (array_merge ($order, $params));
        $id = $this->safe_value($response['result'], 'txid');
        if ($id !== null) {
            if (gettype ($id) === 'array' && count (array_filter (array_keys ($id), 'is_string')) == 0) {
                $length = is_array ($id) ? count ($id) : 0;
                $id = ($length > 1) ? $id : $id[0];
            }
        }
        return array (
            'info' => $response,
            'id' => $id,
        );
    }

    public function find_market_by_altname_or_id ($id) {
        if (is_array ($this->marketsByAltname) && array_key_exists ($id, $this->marketsByAltname)) {
            return $this->marketsByAltname[$id];
        } else if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
            return $this->markets_by_id[$id];
        }
        return null;
    }

    public function parse_order ($order, $market = null) {
        $description = $order['descr'];
        $side = $description['type'];
        $type = $description['ordertype'];
        $symbol = null;
        if (!$market)
            $market = $this->find_market_by_altname_or_id ($description['pair']);
        $timestamp = intval ($order['opentm'] * 1000);
        $amount = floatval ($order['vol']);
        $filled = floatval ($order['vol_exec']);
        $remaining = $amount - $filled;
        $fee = null;
        $cost = $this->safe_float($order, 'cost');
        $price = $this->safe_float($description, 'price');
        if (!$price)
            $price = $this->safe_float($order, 'price');
        if ($market) {
            $symbol = $market['symbol'];
            if (is_array ($order) && array_key_exists ('fee', $order)) {
                $flags = $order['oflags'];
                $feeCost = $this->safe_float($order, 'fee');
                $fee = array (
                    'cost' => $feeCost,
                    'rate' => null,
                );
                if (mb_strpos ($flags, 'fciq') !== false) {
                    $fee['currency'] = $market['quote'];
                } else if (mb_strpos ($flags, 'fcib') !== false) {
                    $fee['currency'] = $market['base'];
                }
            }
        }
        return array (
            'id' => $order['id'],
            'info' => $order,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'status' => $order['status'],
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'fee' => $fee,
            // 'trades' => $this->parse_trades($order['trades'], $market),
        );
    }

    public function parse_orders ($orders, $market = null, $since = null, $limit = null) {
        $result = array ();
        $ids = is_array ($orders) ? array_keys ($orders) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $order = array_merge (array ( 'id' => $id ), $orders[$id]);
            $result[] = $this->parse_order($order, $market);
        }
        return $this->filter_by_since_limit($result, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePostQueryOrders (array_merge (array (
            'trades' => true, // whether or not to include trades in output (optional, default false)
            'txid' => $id, // comma delimited list of transaction ids to query info about (20 maximum)
            // 'userref' => 'optional', // restrict results to given user reference $id (optional)
        ), $params));
        $orders = $response['result'];
        $order = $this->parse_order(array_merge (array ( 'id' => $id ), $orders[$id]));
        return array_merge (array ( 'info' => $response ), $order);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            // 'type' => 'all', // any position, closed position, closing position, no position
            // 'trades' => false, // whether or not to include $trades related to position in output
            // 'start' => 1234567890, // starting unix timestamp or trade tx id of results (exclusive)
            // 'end' => 1234567890, // ending unix timestamp or trade tx id of results (inclusive)
            // 'ofs' = result offset
        );
        if ($since !== null)
            $request['start'] = intval ($since / 1000);
        $response = $this->privatePostTradesHistory (array_merge ($request, $params));
        $trades = $response['result']['trades'];
        $ids = is_array ($trades) ? array_keys ($trades) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $trades[$ids[$i]]['id'] = $ids[$i];
        }
        return $this->parse_trades($trades, null, $since, $limit);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = null;
        try {
            $response = $this->privatePostCancelOrder (array_merge (array (
                'txid' => $id,
            ), $params));
        } catch (Exception $e) {
            if ($this->last_http_response)
                if (mb_strpos ($this->last_http_response, 'EOrder:Unknown order') !== false)
                    throw new OrderNotFound ($this->id . ' cancelOrder() error ' . $this->last_http_response);
            throw $e;
        }
        return $response;
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        if ($since !== null)
            $request['start'] = intval ($since / 1000);
        $response = $this->privatePostOpenOrders (array_merge ($request, $params));
        $orders = $this->parse_orders($response['result']['open'], null, $since, $limit);
        return $this->filter_by_symbol($orders, $symbol);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        if ($since !== null)
            $request['start'] = intval ($since / 1000);
        $response = $this->privatePostClosedOrders (array_merge ($request, $params));
        $orders = $this->parse_orders($response['result']['closed'], null, $since, $limit);
        return $this->filter_by_symbol($orders, $symbol);
    }

    public function fetch_deposit_methods ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privatePostDepositMethods (array_merge (array (
            'asset' => $currency['id'],
        ), $params));
        return $response['result'];
    }

    public function create_deposit_address ($code, $params = array ()) {
        $request = array (
            'new' => 'true',
        );
        $response = $this->fetch_deposit_address ($code, array_merge ($request, $params));
        $address = $this->safe_string($response, 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'status' => 'ok',
            'info' => $response,
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        // eslint-disable-next-line quotes
        $method = $this->safe_string($params, 'method');
        if ($method === null) {
            if ($this->options['cacheDepositMethodsOnFetchDepositAddress']) {
                // cache depositMethods
                if (!(is_array ($this->options['depositMethods']) && array_key_exists ($code, $this->options['depositMethods'])))
                    $this->options['depositMethods'][$code] = $this->fetch_deposit_methods ($code);
                $method = $this->options['depositMethods'][$code][0]['method'];
            } else {
                throw new ExchangeError ($this->id . ' fetchDepositAddress() requires an extra `$method` parameter. Use fetchDepositMethods ("' . $code . '") to get a list of available deposit methods or enable the exchange property .options["cacheDepositMethodsOnFetchDepositAddress"] = true');
            }
        }
        $request = array (
            'asset' => $currency['id'],
            'method' => $method,
        );
        $response = $this->privatePostDepositAddresses (array_merge ($request, $params)); // overwrite methods
        $result = $response['result'];
        $numResults = is_array ($result) ? count ($result) : 0;
        if ($numResults < 1)
            throw new InvalidAddress ($this->id . ' privatePostDepositAddresses() returned no addresses');
        $address = $this->safe_string($result[0], 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'status' => 'ok',
            'info' => $response,
        );
    }

    public function withdraw ($currency, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        if (is_array ($params) && array_key_exists ('key', $params)) {
            $this->load_markets();
            $response = $this->privatePostWithdraw (array_merge (array (
                'asset' => $currency,
                'amount' => $amount,
                // 'address' => $address, // they don't allow withdrawals to direct addresses
            ), $params));
            return array (
                'info' => $response,
                'id' => $response['result'],
            );
        }
        throw new ExchangeError ($this->id . " withdraw requires a 'key' parameter (withdrawal key name, as set up on your account)");
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = '/' . $this->version . '/' . $api . '/' . $path;
        if ($api === 'public') {
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        } else if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $body = $this->urlencode (array_merge (array ( 'nonce' => $nonce ), $params));
            $auth = $this->encode ($nonce . $body);
            $hash = $this->hash ($auth, 'sha256', 'binary');
            $binary = $this->encode ($url);
            $binhash = $this->binary_concat($binary, $hash);
            $secret = base64_decode ($this->secret);
            $signature = $this->hmac ($binhash, $secret, 'sha512', 'base64');
            $headers = array (
                'API-Key' => $this->apiKey,
                'API-Sign' => $this->decode ($signature),
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        } else {
            $url = '/' . $path;
        }
        $url = $this->urls['api'][$api] . $url;
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (gettype ($response) != 'string')
            if (is_array ($response) && array_key_exists ('error', $response)) {
                $numErrors = is_array ($response['error']) ? count ($response['error']) : 0;
                if ($numErrors) {
                    $message = $this->id . ' ' . $this->json ($response);
                    for ($i = 0; $i < count ($response['error']); $i++) {
                        if ($response['error'][$i] === 'EFunding:Unknown withdraw key')
                            throw new ExchangeError ($message);
                        if ($response['error'][$i] === 'EService:Unavailable')
                            throw new ExchangeNotAvailable ($message);
                        if ($response['error'][$i] === 'EDatabase:Internal error')
                            throw new ExchangeNotAvailable ($message);
                        if ($response['error'][$i] === 'EService:Busy')
                            throw new DDoSProtection ($message);
                    }
                    throw new ExchangeError ($message);
                }
            }
        return $response;
    }
}
