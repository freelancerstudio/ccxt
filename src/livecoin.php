<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\ArgumentsRequired;
use \ccxt\InsufficientFunds;
use \ccxt\InvalidOrder;
use \ccxt\OrderNotFound;

class livecoin extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'livecoin',
            'name' => 'LiveCoin',
            'countries' => array( 'US', 'UK', 'RU' ),
            'rateLimit' => 1000,
            'userAgent' => $this->userAgents['chrome'],
            'has' => array(
                'cancelOrder' => true,
                'CORS' => false,
                'createOrder' => true,
                'fetchBalance' => true,
                'fetchClosedOrders' => true,
                'fetchCurrencies' => true,
                'fetchDepositAddress' => true,
                'fetchDeposits' => true,
                'fetchMarkets' => true,
                'fetchMyTrades' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchOrderBook' => true,
                'fetchOrders' => true,
                'fetchTicker' => true,
                'fetchTickers' => true,
                'fetchTrades' => true,
                'fetchTradingFee' => true,
                'fetchTradingFees' => true,
                'fetchWithdrawals' => true,
                'withdraw' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/27980768-f22fc424-638a-11e7-89c9-6010a54ff9be.jpg',
                'api' => 'https://api.livecoin.net',
                'www' => 'https://www.livecoin.net',
                'doc' => 'https://www.livecoin.net/api?lang=en',
                'referral' => 'https://livecoin.net/?from=Livecoin-CQ1hfx44',
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'exchange/all/order_book',
                        'exchange/last_trades',
                        'exchange/maxbid_minask',
                        'exchange/order_book',
                        'exchange/restrictions',
                        'exchange/ticker', // omit params to get all tickers at once
                        'info/coinInfo',
                    ),
                ),
                'private' => array(
                    'get' => array(
                        'exchange/client_orders',
                        'exchange/order',
                        'exchange/trades',
                        'exchange/commission',
                        'exchange/commissionCommonInfo',
                        'payment/balances',
                        'payment/balance',
                        'payment/get/address',
                        'payment/history/size',
                        'payment/history/transactions',
                    ),
                    'post' => array(
                        'exchange/buylimit',
                        'exchange/buymarket',
                        'exchange/cancellimit',
                        'exchange/selllimit',
                        'exchange/sellmarket',
                        'payment/out/capitalist',
                        'payment/out/card',
                        'payment/out/coin',
                        'payment/out/okpay',
                        'payment/out/payeer',
                        'payment/out/perfectmoney',
                        'payment/voucher/amount',
                        'payment/voucher/make',
                        'payment/voucher/redeem',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.18 / 100,
                    'taker' => 0.18 / 100,
                ),
            ),
            'commonCurrencies' => array(
                'BTCH' => 'Bithash',
                'CPC' => 'Capricoin',
                'CBC' => 'CryptoBossCoin', // conflict with CBC (CashBet Coin)
                'CPT' => 'Cryptos', // conflict with CPT = Contents Protocol https://github.com/ccxt/ccxt/issues/4920 and https://github.com/ccxt/ccxt/issues/6081
                'EDR' => 'E-Dinar Coin', // conflicts with EDR for Endor Protocol and EDRCoin
                'eETT' => 'EETT',
                'FirstBlood' => '1ST',
                'FORTYTWO' => '42',
                'LEO' => 'LeoCoin',
                'ORE' => 'Orectic',
                'PLN' => 'Plutaneum', // conflict with Polish Zloty
                'RUR' => 'RUB',
                'SCT' => 'SpaceCoin',
                'TPI' => 'ThaneCoin',
                'WAX' => 'WAXP',
                'wETT' => 'WETT',
                'XBT' => 'Bricktox',
            ),
            'exceptions' => array(
                'exact' => array(
                    '1' => '\\ccxt\\ExchangeError',
                    '10' => '\\ccxt\\AuthenticationError',
                    '100' => '\\ccxt\\ExchangeError', // invalid parameters
                    '101' => '\\ccxt\\AuthenticationError',
                    '102' => '\\ccxt\\AuthenticationError',
                    '103' => '\\ccxt\\InvalidOrder', // invalid currency
                    '104' => '\\ccxt\\InvalidOrder', // invalid amount
                    '105' => '\\ccxt\\InvalidOrder', // unable to block funds
                    '11' => '\\ccxt\\AuthenticationError',
                    '12' => '\\ccxt\\AuthenticationError',
                    '2' => '\\ccxt\\AuthenticationError', // "User not found"
                    '20' => '\\ccxt\\AuthenticationError',
                    '30' => '\\ccxt\\AuthenticationError',
                    '31' => '\\ccxt\\NotSupported',
                    '32' => '\\ccxt\\ExchangeError',
                    '429' => '\\ccxt\\RateLimitExceeded',
                    '503' => '\\ccxt\\ExchangeNotAvailable',
                ),
                'broad' => array(
                    'insufficient funds' => '\\ccxt\\InsufficientFunds', // https://github.com/ccxt/ccxt/issues/5749
                    'NOT FOUND' => '\\ccxt\\OrderNotFound',
                    'Cannot find order' => '\\ccxt\\OrderNotFound',
                    'Minimal amount is' => '\\ccxt\\InvalidOrder',
                ),
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        $response = $this->publicGetExchangeTicker ($params);
        $restrictions = $this->publicGetExchangeRestrictions ();
        $restrictionsById = $this->index_by($restrictions['restrictions'], 'currencyPair');
        $result = array();
        for ($i = 0; $i < count($response); $i++) {
            $market = $response[$i];
            $id = $this->safe_string($market, 'symbol');
            list($baseId, $quoteId) = explode('/', $id);
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $coinRestrictions = $this->safe_value($restrictionsById, $symbol);
            $precision = array(
                'price' => 5,
                'amount' => 8,
                'cost' => 8,
            );
            $limits = array(
                'amount' => array(
                    'min' => pow(10, -$precision['amount']),
                    'max' => pow(10, $precision['amount']),
                ),
            );
            if ($coinRestrictions) {
                $precision['price'] = $this->safe_integer($coinRestrictions, 'priceScale', 5);
                $limits['amount']['min'] = $this->safe_float($coinRestrictions, 'minLimitQuantity', $limits['amount']['min']);
            }
            $limits['price'] = array(
                'min' => pow(10, -$precision['price']),
                'max' => pow(10, $precision['price']),
            );
            $result[] = array(
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

    public function fetch_currencies($params = array ()) {
        $response = $this->publicGetInfoCoinInfo ($params);
        $currencies = $this->safe_value($response, 'info');
        $result = array();
        for ($i = 0; $i < count($currencies); $i++) {
            $currency = $currencies[$i];
            $id = $this->safe_string($currency, 'symbol');
            // todo => will need to rethink the fees
            // to add support for multiple withdrawal/deposit methods and
            // differentiated fees for each particular method
            $code = $this->safe_currency_code($id);
            $precision = 8; // default $precision, todo => fix "magic constants"
            $walletStatus = $this->safe_string($currency, 'walletStatus');
            $active = ($walletStatus === 'normal');
            $name = $this->safe_string($currency, 'name');
            $fee = $this->safe_float($currency, 'withdrawFee');
            $result[$code] = array(
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => $name,
                'active' => $active,
                'fee' => $fee,
                'precision' => $precision,
                'limits' => array(
                    'amount' => array(
                        'min' => $this->safe_float($currency, 'minOrderAmount'),
                        'max' => pow(10, $precision),
                    ),
                    'price' => array(
                        'min' => pow(10, -$precision),
                        'max' => pow(10, $precision),
                    ),
                    'cost' => array(
                        'min' => $this->safe_float($currency, 'minOrderAmount'),
                        'max' => null,
                    ),
                    'withdraw' => array(
                        'min' => $this->safe_float($currency, 'minWithdrawAmount'),
                        'max' => pow(10, $precision),
                    ),
                    'deposit' => array(
                        'min' => $this->safe_float($currency, 'minDepositAmount'),
                        'max' => null,
                    ),
                ),
            );
        }
        $result = $this->append_fiat_currencies($result);
        return $result;
    }

    public function append_fiat_currencies($result) {
        $precision = 8;
        $defaults = array(
            'info' => null,
            'active' => true,
            'fee' => null,
            'precision' => $precision,
            'limits' => array(
                'withdraw' => array( 'min' => null, 'max' => null ),
                'deposit' => array( 'min' => null, 'max' => null ),
                'amount' => array( 'min' => null, 'max' => null ),
                'cost' => array( 'min' => null, 'max' => null ),
                'price' => array(
                    'min' => pow(10, -$precision),
                    'max' => pow(10, $precision),
                ),
            ),
            'id' => null,
            'code' => null,
            'name' => null,
        );
        $currencies = array(
            array( 'id' => 'USD', 'code' => 'USD', 'name' => 'US Dollar' ),
            array( 'id' => 'EUR', 'code' => 'EUR', 'name' => 'Euro' ),
            // array( 'id' => 'RUR', 'code' => 'RUB', 'name' => 'Russian ruble' ),
        );
        $currencies[] = array(
            'id' => 'RUR',
            'code' => $this->safe_currency_code('RUR'),
            'name' => 'Russian ruble',
        );
        for ($i = 0; $i < count($currencies); $i++) {
            $currency = $currencies[$i];
            $code = $currency['code'];
            $result[$code] = array_merge($defaults, $currency);
        }
        return $result;
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetPaymentBalances ($params);
        $result = array( 'info' => $response );
        for ($i = 0; $i < count($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'currency');
            $code = $this->safe_currency_code($currencyId);
            $account = null;
            if (is_array($result) && array_key_exists($code, $result)) {
                $account = $result[$code];
            } else {
                $account = $this->account();
            }
            if ($balance['type'] === 'total') {
                $account['total'] = $this->safe_float($balance, 'value');
            }
            if ($balance['type'] === 'available') {
                $account['free'] = $this->safe_float($balance, 'value');
            }
            if ($balance['type'] === 'trade') {
                $account['used'] = $this->safe_float($balance, 'value');
            }
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_trading_fees($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetExchangeCommissionCommonInfo ($params);
        $commission = $this->safe_float($response, 'commission');
        return array(
            'info' => $response,
            'maker' => $commission,
            'taker' => $commission,
        );
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'currencyPair' => $this->market_id($symbol),
            'groupByPrice' => 'false',
        );
        if ($limit !== null) {
            $request['depth'] = $limit; // 100
        }
        $response = $this->publicGetExchangeOrderBook (array_merge($request, $params));
        $timestamp = $this->safe_integer($response, 'timestamp');
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ticker($ticker, $market = null) {
        $timestamp = $this->milliseconds();
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
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
            'bid' => $this->safe_float($ticker, 'best_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'best_ask'),
            'askVolume' => null,
            'vwap' => $this->safe_float($ticker, 'vwap'),
            'open' => null,
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

    public function fetch_tickers($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetExchangeTicker ($params);
        $tickers = $this->index_by($response, 'symbol');
        $ids = is_array($tickers) ? array_keys($tickers) : array();
        $result = array();
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            $market = $this->markets_by_id[$id];
            $symbol = $market['symbol'];
            $ticker = $tickers[$id];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currencyPair' => $market['id'],
        );
        $ticker = $this->publicGetExchangeTicker (array_merge($request, $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     {
        //         "time" => 1409935047,
        //         "$id" => 99451,
        //         "$price" => 350,
        //         "quantity" => 2.85714285,
        //         "type" => "BUY"
        //     }
        //
        // fetchMyTrades (private)
        //
        //     {
        //         "datetime" => 1435844369,
        //         "$id" => 30651619,
        //         "type" => "sell",
        //         "$symbol" => "BTC/EUR",
        //         "$price" => 230,
        //         "quantity" => 0.1,
        //         "commission" => 0,
        //         "clientorderid" => 1472837650
        //     }
        $timestamp = $this->safe_timestamp_2($trade, 'time', 'datetime');
        $fee = null;
        $feeCost = $this->safe_float($trade, 'commission');
        if ($feeCost !== null) {
            $feeCurrency = $market ? $market['quote'] : null;
            $fee = array(
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        $orderId = $this->safe_string($trade, 'clientorderid');
        $id = $this->safe_string($trade, 'id');
        $side = $this->safe_string_lower($trade, 'type');
        $amount = $this->safe_float($trade, 'quantity');
        $price = $this->safe_float($trade, 'price');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $symbol = null;
        $marketId = $this->safe_string($trade, 'symbol');
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                list($baseId, $quoteId) = explode('/', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if (($symbol === null) && ($market !== null)) {
            $symbol = $market['symbol'];
        }
        return array(
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => null,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_my_trades($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            // 'currencyPair' => $market['id'],
            // 'orderDesc' => 'true', // or 'false', if true then new orders will be first, otherwise old orders will be first.
            // 'offset' => 0, // page offset, position of the first item on the page
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['currencyPair'] = $market['id'];
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privateGetExchangeTrades (array_merge($request, $params));
        //
        //     array(
        //         array(
        //             "datetime" => 1435844369,
        //             "id" => 30651619,
        //             "type" => "sell",
        //             "$symbol" => "BTC/EUR",
        //             "price" => 230,
        //             "quantity" => 0.1,
        //             "commission" => 0,
        //             "clientorderid" => 1472837650
        //         ),
        //         {
        //             "datetime" => 1435844356,
        //             "id" => 30651618,
        //             "type" => "sell",
        //             "$symbol" => "BTC/EUR",
        //             "price" => 230,
        //             "quantity" => 0.2,
        //             "commission" => 0.092,
        //             "clientorderid" => 1472837651
        //         }
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currencyPair' => $market['id'],
        );
        $response = $this->publicGetExchangeLastTrades (array_merge($request, $params));
        //
        //     array(
        //         array(
        //             "time" => 1409935047,
        //             "id" => 99451,
        //             "price" => 350,
        //             "quantity" => 2.85714285,
        //             "type" => "BUY"
        //         ),
        //         {
        //             "time" => 1409934792,
        //             "id" => 99450,
        //             "price" => 350,
        //             "quantity" => 0.57142857,
        //             "type" => "SELL"
        //         }
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'orderId' => $id,
        );
        $response = $this->privateGetExchangeOrder (array_merge($request, $params));
        return $this->parse_order($response);
    }

    public function parse_order_status($status) {
        $statuses = array(
            'OPEN' => 'open',
            'PARTIALLY_FILLED' => 'open',
            'EXECUTED' => 'closed',
            'CANCELLED' => 'canceled',
            'PARTIALLY_FILLED_AND_CANCELLED' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order($order, $market = null) {
        $timestamp = null;
        if (is_array($order) && array_key_exists('lastModificationTime', $order)) {
            $timestamp = $this->safe_string($order, 'lastModificationTime');
            if ($timestamp !== null) {
                if (mb_strpos($timestamp, 'T') !== false) {
                    $timestamp = $this->parse8601($timestamp);
                } else {
                    $timestamp = $this->safe_integer($order, 'lastModificationTime');
                }
            }
        }
        // TODO currently not supported by livecoin
        // $trades = $this->parse_trades($order['trades'], $market, since, limit);
        $trades = null;
        $status = $this->parse_order_status($this->safe_string_2($order, 'status', 'orderStatus'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'currencyPair');
            $marketId = $this->safe_string($order, 'symbol', $marketId);
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        $type = $this->safe_string_lower($order, 'type');
        $side = null;
        if ($type !== null) {
            $orderType = explode('_', $type);
            $type = $orderType[0];
            $side = $orderType[1];
        }
        $price = $this->safe_float($order, 'price');
        // of the next two lines the latter overrides the former, if present in the $order structure
        $remaining = $this->safe_float($order, 'remainingQuantity');
        $remaining = $this->safe_float($order, 'remaining_quantity', $remaining);
        $amount = $this->safe_float($order, 'quantity', $remaining);
        $filled = null;
        if ($remaining !== null) {
            $filled = $amount - $remaining;
        }
        $cost = null;
        if ($filled !== null && $price !== null) {
            $cost = $filled * $price;
        }
        $feeRate = $this->safe_float($order, 'commission_rate');
        $feeCost = null;
        if ($cost !== null && $feeRate !== null) {
            $feeCost = $cost * $feeRate;
        }
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['quote'];
        }
        return array(
            'info' => $order,
            'id' => $order['id'],
            'clientOrderId' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => $trades,
            'fee' => array(
                'cost' => $feeCost,
                'currency' => $feeCurrency,
                'rate' => $feeRate,
            ),
            'average' => null,
        );
    }

    public function fetch_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array();
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['currencyPair'] = $market['id'];
        }
        if ($since !== null) {
            $request['issuedFrom'] = intval ($since);
        }
        if ($limit !== null) {
            $request['endRow'] = $limit - 1;
        }
        $response = $this->privateGetExchangeClientOrders (array_merge($request, $params));
        $result = array();
        $rawOrders = array();
        if ($response['data']) {
            $rawOrders = $response['data'];
        }
        for ($i = 0; $i < count($rawOrders); $i++) {
            $order = $rawOrders[$i];
            $result[] = $this->parse_order($order, $market);
        }
        return $this->sort_by($result, 'timestamp');
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $request = array(
            'openClosed' => 'OPEN',
        );
        return $this->fetch_orders($symbol, $since, $limit, array_merge($request, $params));
    }

    public function fetch_closed_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $request = array(
            'openClosed' => 'CLOSED',
        );
        return $this->fetch_orders($symbol, $since, $limit, array_merge($request, $params));
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $method = 'privatePostExchange' . $this->capitalize($side) . $type;
        $market = $this->market($symbol);
        $request = array(
            'quantity' => $this->amount_to_precision($symbol, $amount),
            'currencyPair' => $market['id'],
        );
        if ($type === 'limit') {
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $response = $this->$method (array_merge($request, $params));
        $result = array(
            'info' => $response,
            'id' => (string) $response['orderId'],
        );
        $success = $this->safe_value($response, 'success');
        if ($success) {
            $result['status'] = 'open';
        }
        return $result;
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'orderId' => $id,
            'currencyPair' => $market['id'],
        );
        $response = $this->privatePostExchangeCancellimit (array_merge($request, $params));
        $message = $this->safe_string($response, 'message', $this->json($response));
        if (is_array($response) && array_key_exists('success', $response)) {
            if (!$response['success']) {
                throw new InvalidOrder($message);
            } else if (is_array($response) && array_key_exists('cancelled', $response)) {
                if ($response['cancelled']) {
                    return array(
                        'status' => 'canceled',
                        'info' => $response,
                    );
                } else {
                    throw new OrderNotFound($message);
                }
            }
        }
        throw new ExchangeError($this->id . ' cancelOrder() failed => ' . $this->json($response));
    }

    public function withdraw($code, $amount, $address, $tag = null, $params = array ()) {
        // Sometimes the $response with be array( key => null ) for all keys.
        // An example is if you attempt to withdraw more than is allowed when withdrawal fees are considered.
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency($code);
        $wallet = $address;
        if ($tag !== null) {
            $wallet .= '::' . $tag;
        }
        $request = array(
            'amount' => $this->decimal_to_precision($amount, TRUNCATE, $currency['precision'], DECIMAL_PLACES),
            'currency' => $currency['id'],
            'wallet' => $wallet,
        );
        $response = $this->privatePostPaymentOutCoin (array_merge($request, $params));
        $id = $this->safe_integer($response, 'id');
        if ($id === null) {
            throw new InsufficientFunds($this->id . ' insufficient funds to cover requested withdrawal $amount post fees ' . $this->json($response));
        }
        return array(
            'info' => $response,
            'id' => $id,
        );
    }

    public function parse_transaction($transaction, $currency = null) {
        //    array(
        //        "$id" => "c853093d5aa06df1c92d79c2...", (tx on deposits, $address on withdrawals)
        //        "$type" => "DEPOSIT",
        //        "date" => 1553186482676,
        //        "$amount" => 712.61266,
        //        "fee" => 0,
        //        "fixedCurrency" => "XVG",
        //        "taxCurrency" => "XVG",
        //        "variableAmount" => null,
        //        "variableCurrency" => null,
        //        "external" => "Coin",
        //        "login" => "USERNAME",
        //        "externalKey" => "....87diPBy......3hTtuwUT78Yi", ($address on deposits, tx on withdrawals)
        //        "documentId" => 1110662453
        //    ),
        $txid = null;
        $address = null;
        $id = $this->safe_string($transaction, 'documentId');
        $amount = $this->safe_float($transaction, 'amount');
        $timestamp = $this->safe_integer($transaction, 'date');
        $type = $this->safe_string_lower($transaction, 'type');
        $currencyId = $this->safe_string($transaction, 'fixedCurrency');
        $feeCost = $this->safe_float($transaction, 'fee');
        $code = $this->safe_currency_code($currencyId, $currency);
        if ($type === 'withdrawal') {
            $txid = $this->safe_string($transaction, 'externalKey');
            $address = $this->safe_string($transaction, 'id');
        } else if ($type === 'deposit') {
            $address = $this->safe_string($transaction, 'externalKey');
            $txid = $this->safe_string($transaction, 'id');
        }
        $status = null;
        if ($type === 'deposit') {
            $status = 'ok'; // Deposits is not registered until they are in account. Withdrawals are left as null, not entirely sure about theyre $status->
        }
        return array(
            'info' => $transaction,
            'id' => $id,
            'currency' => $code,
            'amount' => $amount,
            'address' => $address,
            'tag' => null,
            'status' => $status,
            'type' => $type,
            'updated' => null,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'fee' => array(
                'currency' => $code,
                'cost' => $feeCost,
            ),
        );
    }

    public function fetch_deposits($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $endtime = 2505600000; // 29 days - exchange has maximum 30 days.
        $now = $this->milliseconds();
        $request = array(
            'types' => 'DEPOSIT',
            'end' => $now,
            'start' => ($since !== null) ? intval ($since) : $now - $endtime,
        );
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency($code);
        }
        if ($limit !== null) {
            $request['limit'] = $limit; // default is 100
        }
        $response = $this->privateGetPaymentHistoryTransactions (array_merge($request, $params));
        return $this->parse_transactions($response, $currency, $since, $limit);
    }

    public function fetch_withdrawals($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $endtime = 2505600000; // 29 days - exchange has maximum 30 days.
        $now = $this->milliseconds();
        $request = array(
            'types' => 'WITHDRAWAL',
            'end' => $now,
            'start' => ($since !== null) ? intval ($since) : $now - $endtime,
        );
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency($code);
        }
        if ($limit !== null) {
            $request['limit'] = $limit; // default is 100
        }
        if ($since !== null) {
            $request['start'] = $since;
        }
        $response = $this->privateGetPaymentHistoryTransactions (array_merge($request, $params));
        return $this->parse_transactions($response, $currency, $since, $limit);
    }

    public function fetch_deposit_address($currency, $params = array ()) {
        $request = array(
            'currency' => $currency,
        );
        $response = $this->privateGetPaymentGetAddress (array_merge($request, $params));
        $address = $this->safe_string($response, 'wallet');
        $tag = null;
        if (mb_strpos($address, ':') !== false) {
            $parts = explode(':', $address);
            $address = $parts[0];
            $tag = $parts[2];
        }
        $this->check_address($address);
        return array(
            'currency' => $currency,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $path;
        $query = $this->urlencode($this->keysort($params));
        if ($method === 'GET') {
            if ($params) {
                $url .= '?' . $query;
            }
        }
        if ($api === 'private') {
            $this->check_required_credentials();
            if ($method === 'POST') {
                $body = $query;
            }
            $signature = $this->hmac($this->encode($query), $this->encode($this->secret), 'sha256');
            $headers = array(
                'Api-Key' => $this->apiKey,
                'Sign' => strtoupper($signature),
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return; // fallback to default error handler
        }
        if ($code >= 300) {
            $feedback = $this->id . ' ' . $body;
            $errorCode = $this->safe_string($response, 'errorCode');
            $this->throw_exactly_matched_exception($this->exceptions['exact'], $errorCode, $feedback);
            throw new ExchangeError($feedback);
        }
        // returns status $code 200 even if $success === false
        $success = $this->safe_value($response, 'success', true);
        if (!$success) {
            $feedback = $this->id . ' ' . $body;
            $message = $this->safe_string_2($response, 'message', 'exception');
            if ($message !== null) {
                $this->throw_broadly_matched_exception($this->exceptions['broad'], $message, $feedback);
            }
            throw new ExchangeError($feedback);
        }
    }
}
