<?php

namespace ccxt;

use Exception as Exception; // a common import

class liqui extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'liqui',
            'name' => 'Liqui',
            'countries' => array ( 'UA' ),
            'rateLimit' => 3000,
            'version' => '3',
            'userAgent' => $this->userAgents['chrome'],
            'has' => array (
                'CORS' => false,
                'createMarketOrder' => false,
                'fetchOrderBooks' => true,
                'fetchOrder' => true,
                'fetchOrders' => 'emulated',
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => 'emulated',
                'fetchTickers' => true,
                'fetchMyTrades' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27982022-75aea828-63a0-11e7-9511-ca584a8edd74.jpg',
                'api' => array (
                    'public' => 'https://api.liqui.io/api',
                    'private' => 'https://api.liqui.io/tapi',
                ),
                'www' => 'https://liqui.io',
                'doc' => 'https://liqui.io/api',
                'fees' => 'https://liqui.io/fee',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'info',
                        'ticker/{pair}',
                        'depth/{pair}',
                        'trades/{pair}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'getInfo',
                        'Trade',
                        'ActiveOrders',
                        'OrderInfo',
                        'CancelOrder',
                        'TradeHistory',
                        'CoinDepositAddress',
                        'WithdrawCoin',
                        'CreateCoupon',
                        'RedeemCoupon',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.001,
                    'taker' => 0.0025,
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(),
                    'deposit' => array(),
                ),
            ),
            'commonCurrencies' => array (
                'DSH' => 'DASH',
            ),
            'exceptions' => array (
                'exact' => array (
                    '803' => '\\ccxt\\InvalidOrder', // "Count could not be less than 0.001." (selling below minAmount)
                    '804' => '\\ccxt\\InvalidOrder', // "Count could not be more than 10000." (buying above maxAmount)
                    '805' => '\\ccxt\\InvalidOrder', // "price could not be less than X." (minPrice violation on buy & sell)
                    '806' => '\\ccxt\\InvalidOrder', // "price could not be more than X." (maxPrice violation on buy & sell)
                    '807' => '\\ccxt\\InvalidOrder', // "cost could not be less than X." (minCost violation on buy & sell)
                    '831' => '\\ccxt\\InsufficientFunds', // "Not enougth X to create buy order." (buying with balance.quote < order.cost)
                    '832' => '\\ccxt\\InsufficientFunds', // "Not enougth X to create sell order." (selling with balance.base < order.amount)
                    '833' => '\\ccxt\\OrderNotFound', // "Order with id X was not found." (cancelling non-existent, closed and cancelled order)
                ),
                'broad' => array (
                    'Invalid pair name' => '\\ccxt\\ExchangeError', // array("success":0,"error":"Invalid pair name => btc_eth")
                    'invalid api key' => '\\ccxt\\AuthenticationError',
                    'invalid sign' => '\\ccxt\\AuthenticationError',
                    'api key dont have trade permission' => '\\ccxt\\AuthenticationError',
                    'invalid parameter' => '\\ccxt\\InvalidOrder',
                    'invalid order' => '\\ccxt\\InvalidOrder',
                    'Requests too often' => '\\ccxt\\DDoSProtection',
                    'not available' => '\\ccxt\\ExchangeNotAvailable',
                    'data unavailable' => '\\ccxt\\ExchangeNotAvailable',
                    'external service unavailable' => '\\ccxt\\ExchangeNotAvailable',
                ),
            ),
            'options' => array (
                'fetchOrderMethod' => 'privatePostOrderInfo',
                'fetchMyTradesMethod' => 'privatePostTradeHistory',
                'cancelOrderMethod' => 'privatePostCancelOrder',
                'fetchTickersMaxLength' => 2048,
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
            'cost' => $cost,
        );
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetInfo ($params);
        $markets = $response['pairs'];
        $keys = is_array($markets) ? array_keys($markets) : array();
        $result = array();
        for ($i = 0; $i < count ($keys); $i++) {
            $id = $keys[$i];
            $market = $markets[$id];
            list($baseId, $quoteId) = explode('_', $id);
            $base = strtoupper($baseId);
            $quote = strtoupper($quoteId);
            $base = $this->common_currency_code($base);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $this->safe_integer($market, 'decimal_places'),
                'price' => $this->safe_integer($market, 'decimal_places'),
            );
            $amountLimits = array (
                'min' => $this->safe_float($market, 'min_amount'),
                'max' => $this->safe_float($market, 'max_amount'),
            );
            $priceLimits = array (
                'min' => $this->safe_float($market, 'min_price'),
                'max' => $this->safe_float($market, 'max_price'),
            );
            $costLimits = array (
                'min' => $this->safe_float($market, 'min_total'),
            );
            $limits = array (
                'amount' => $amountLimits,
                'price' => $priceLimits,
                'cost' => $costLimits,
            );
            $hidden = $this->safe_integer($market, 'hidden');
            $active = ($hidden === 0);
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => $active,
                'taker' => $market['fee'] / 100,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ($params);
        $balances = $this->safe_value($response, 'return');
        $result = array( 'info' => $balances );
        $funds = $this->safe_value($balances, 'funds');
        $currencies = is_array($funds) ? array_keys($funds) : array();
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $uppercase = strtoupper($currency);
            $uppercase = $this->common_currency_code($uppercase);
            $total = null;
            $used = null;
            if ($balances['open_orders'] === 0) {
                $total = $funds[$currency];
                $used = 0.0;
            }
            $account = array (
                'free' => $funds[$currency],
                'used' => $used,
                'total' => $total,
            );
            $result[$uppercase] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair' => $market['id'],
        );
        if ($limit !== null) {
            $request['limit'] = $limit; // default = 150, max = 2000
        }
        $response = $this->publicGetDepthPair (array_merge ($request, $params));
        $market_id_in_reponse = (is_array($response) && array_key_exists($market['id'], $response));
        if (!$market_id_in_reponse) {
            throw new ExchangeError($this->id . ' ' . $market['symbol'] . ' order book is empty or not available');
        }
        $orderbook = $response[$market['id']];
        return $this->parse_order_book($orderbook);
    }

    public function fetch_order_books ($symbols = null, $params = array ()) {
        $this->load_markets();
        $ids = null;
        if ($symbols === null) {
            $ids = implode('-', $this->ids);
            // max URL length is 2083 $symbols, including http schema, hostname, tld, etc...
            if (strlen ($ids) > 2048) {
                $numIds = is_array ($this->ids) ? count ($this->ids) : 0;
                throw new ExchangeError($this->id . ' has ' . (string) $numIds . ' $symbols exceeding max URL length, you are required to specify a list of $symbols in the first argument to fetchOrderBooks');
            }
        } else {
            $ids = $this->market_ids($symbols);
            $ids = implode('-', $ids);
        }
        $request = array (
            'pair' => $ids,
        );
        $response = $this->publicGetDepthPair (array_merge ($request, $params));
        $result = array();
        $ids = is_array($response) ? array_keys($response) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $symbol = $id;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            }
            $result[$symbol] = $this->parse_order_book($response[$id]);
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //   {    high => 0.03497582,
        //         low => 0.03248474,
        //         avg => 0.03373028,
        //         vol => 120.11485715062999,
        //     vol_cur => 3572.24914074,
        //        $last => 0.0337611,
        //         buy => 0.0337442,
        //        sell => 0.03377798,
        //     updated => 1537522009          }
        //
        $timestamp = $this->safe_integer($ticker, 'updated');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'last');
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
            'percentage' => null,
            'average' => $this->safe_float($ticker, 'avg'),
            'baseVolume' => $this->safe_float($ticker, 'vol_cur'),
            'quoteVolume' => $this->safe_float($ticker, 'vol'),
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $ids = $this->ids;
        if ($symbols === null) {
            $numIds = is_array ($ids) ? count ($ids) : 0;
            $ids = implode('-', $ids);
            $maxLength = $this->safe_integer($this->options, 'fetchTickersMaxLength', 2048);
            // max URL length is 2048 $symbols, including http schema, hostname, tld, etc...
            if (strlen ($ids) > $this->options['fetchTickersMaxLength']) {
                throw new ArgumentsRequired($this->id . ' has ' . (string) $numIds . ' markets exceeding max URL length for this endpoint (' . (string) $maxLength . ' characters), please, specify a list of $symbols of interest in the first argument to fetchTickers');
            }
        } else {
            $ids = $this->market_ids($symbols);
            $ids = implode('-', $ids);
        }
        $request = array (
            'pair' => $ids,
        );
        $tickers = $this->publicGetTickerPair (array_merge ($request, $params));
        $result = array();
        $keys = is_array($tickers) ? array_keys($tickers) : array();
        for ($k = 0; $k < count ($keys); $k++) {
            $id = $keys[$k];
            $ticker = $tickers[$id];
            $symbol = $id;
            $market = null;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            }
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $tickers = $this->fetch_tickers(array ( $symbol ), $params);
        return $tickers[$symbol];
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_integer($trade, 'timestamp');
        if ($timestamp !== null) {
            $timestamp = $timestamp * 1000;
        }
        $side = $this->safe_string($trade, 'type');
        if ($side === 'ask') {
            $side = 'sell';
        } else if ($side === 'bid') {
            $side = 'buy';
        }
        $price = $this->safe_float_2($trade, 'rate', 'price');
        $id = $this->safe_string_2($trade, 'trade_id', 'tid');
        $order = $this->safe_string($trade, $this->get_order_id_key ());
        if (is_array($trade) && array_key_exists('pair', $trade)) {
            $marketId = $this->safe_string($trade, 'pair');
            $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        }
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $amount = $this->safe_float($trade, 'amount');
        $type = 'limit'; // all trades are still limit trades
        $takerOrMaker = null;
        $fee = null;
        $feeCost = $this->safe_float($trade, 'commission');
        if ($feeCost !== null) {
            $feeCurrencyId = $this->safe_string($trade, 'commissionCurrency');
            $feeCurrencyId = strtoupper($feeCurrencyId);
            $feeCurrency = $this->safe_value($this->currencies_by_id, $feeCurrencyId);
            $feeCurrencyCode = null;
            if ($feeCurrency !== null) {
                $feeCurrencyCode = $feeCurrency['code'];
            } else {
                $feeCurrencyCode = $this->common_currency_code($feeCurrencyId);
            }
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrencyCode,
            );
        }
        $isYourOrder = $this->safe_value($trade, 'is_your_order');
        if ($isYourOrder !== null) {
            $takerOrMaker = 'taker';
            if ($isYourOrder) {
                $takerOrMaker = 'maker';
            }
            if ($fee === null) {
                $fee = $this->calculate_fee($symbol, $type, $side, $amount, $price, $takerOrMaker);
            }
        }
        return array (
            'id' => $id,
            'order' => $order,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'takerOrMaker' => $takerOrMaker,
            'price' => $price,
            'amount' => $amount,
            'fee' => $fee,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair' => $market['id'],
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->publicGetTradesPair (array_merge ($request, $params));
        if (gettype ($response) === 'array' && count (array_filter (array_keys ($response), 'is_string')) == 0) {
            $numElements = is_array ($response) ? count ($response) : 0;
            if ($numElements === 0) {
                return array();
            }
        }
        return $this->parse_trades($response[$market['id']], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type === 'market') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair' => $market['id'],
            'type' => $side,
            'amount' => $this->amount_to_precision($symbol, $amount),
            'rate' => $this->price_to_precision($symbol, $price),
        );
        $price = floatval ($price);
        $amount = floatval ($amount);
        $response = $this->privatePostTrade (array_merge ($request, $params));
        $id = null;
        $status = 'open';
        $filled = 0.0;
        $remaining = $amount;
        if (is_array($response) && array_key_exists('return', $response)) {
            $id = $this->safe_string($response['return'], $this->get_order_id_key ());
            if ($id === '0') {
                $id = $this->safe_string($response['return'], 'init_order_id');
                $status = 'closed';
            }
            $filled = $this->safe_float($response['return'], 'received', 0.0);
            $remaining = $this->safe_float($response['return'], 'remains', $amount);
        }
        $timestamp = $this->milliseconds ();
        $order = array (
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $price * $filled,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'fee' => null,
            // 'trades' => $this->parse_trades($order['trades'], $market),
        );
        $this->orders[$id] = $order;
        return array_merge (array( 'info' => $response ), $order);
    }

    public function get_order_id_key () {
        return 'order_id';
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $idKey = $this->get_order_id_key ();
        $request[$idKey] = $id;
        $method = $this->options['cancelOrderMethod'];
        $response = $this->$method (array_merge ($request, $params));
        if (is_array($this->orders) && array_key_exists($id, $this->orders)) {
            $this->orders[$id]['status'] = 'canceled';
        }
        return $response;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '0' => 'open',
            '1' => 'closed',
            '2' => 'canceled',
            '3' => 'canceled', // or partially-filled and still open? https://github.com/ccxt/ccxt/issues/1594
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        $id = $this->safe_string($order, 'id');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $timestamp = $this->safe_integer($order, 'timestamp_created');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'pair');
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $remaining = $this->safe_float($order, 'amount');
        $amount = null;
        $price = $this->safe_float($order, 'rate');
        $filled = null;
        $cost = null;
        if (is_array($order) && array_key_exists('start_amount', $order)) {
            $amount = $this->safe_float($order, 'start_amount');
        } else {
            if (is_array($this->orders) && array_key_exists($id, $this->orders)) {
                $amount = $this->orders[$id]['amount'];
            }
        }
        if ($amount !== null) {
            if ($remaining !== null) {
                $filled = $amount - $remaining;
                $cost = $price * $filled;
            }
        }
        $fee = null;
        $type = 'limit';
        $side = $this->safe_string($order, 'type');
        $result = array (
            'info' => $order,
            'id' => $id,
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'status' => $status,
            'fee' => $fee,
        );
        return $result;
    }

    public function parse_orders ($orders, $market = null, $since = null, $limit = null, $params = array ()) {
        $result = array();
        $ids = is_array($orders) ? array_keys($orders) : array();
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $order = array_merge (array( 'id' => $id ), $orders[$id]);
            $result[] = array_merge ($this->parse_order($order, $market), $params);
        }
        return $this->filter_by_symbol_since_limit($result, $symbol, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $idKey = $this->get_order_id_key ();
        $request[$idKey] = intval ($id);
        $method = $this->options['fetchOrderMethod'];
        $response = $this->$method (array_merge ($request, $params));
        $id = (string) $id;
        $newOrder = $this->parse_order(array_merge (array( 'id' => $id ), $response['return'][$id]));
        $oldOrder = (is_array($this->orders) && array_key_exists($id, $this->orders)) ? $this->orders[$id] : array();
        $this->orders[$id] = array_merge ($oldOrder, $newOrder);
        return $this->orders[$id];
    }

    public function update_cached_orders ($openOrders, $symbol) {
        // update local cache with open orders
        // this will add unseen orders and overwrite existing ones
        for ($j = 0; $j < count ($openOrders); $j++) {
            $id = $openOrders[$j]['id'];
            $this->orders[$id] = $openOrders[$j];
        }
        $openOrdersIndexedById = $this->index_by($openOrders, 'id');
        $cachedOrderIds = is_array($this->orders) ? array_keys($this->orders) : array();
        for ($k = 0; $k < count ($cachedOrderIds); $k++) {
            // match each cached order to an order in the open orders array
            // possible reasons why a cached order may be missing in the open orders array:
            // - order was closed or canceled -> update cache
            // - $symbol mismatch (e.g. cached BTC/USDT, fetched ETH/USDT) -> skip
            $cachedOrderId = $cachedOrderIds[$k];
            $cachedOrder = $this->orders[$cachedOrderId];
            if (!(is_array($openOrdersIndexedById) && array_key_exists($cachedOrderId, $openOrdersIndexedById))) {
                // cached order is not in open orders array
                // if we fetched orders by $symbol and it doesn't match the cached order -> won't update the cached order
                if ($symbol !== null && $symbol !== $cachedOrder['symbol']) {
                    continue;
                }
                // cached order is absent from the list of open orders -> mark the cached order as closed
                if ($cachedOrder['status'] === 'open') {
                    $cachedOrder = array_merge ($cachedOrder, array (
                        'status' => 'closed', // likewise it might have been canceled externally (unnoticed by "us")
                        'cost' => null,
                        'filled' => $cachedOrder['amount'],
                        'remaining' => 0.0,
                    ));
                    if ($cachedOrder['cost'] === null) {
                        if ($cachedOrder['filled'] !== null) {
                            $cachedOrder['cost'] = $cachedOrder['filled'] * $cachedOrder['price'];
                        }
                    }
                    $this->orders[$cachedOrderId] = $cachedOrder;
                }
            }
        }
        return $this->to_array($this->orders);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (is_array($this->options) && array_key_exists('fetchOrdersRequiresSymbol', $this->options)) {
            if ($this->options['fetchOrdersRequiresSymbol']) {
                if ($symbol === null) {
                    throw new ArgumentsRequired($this->id . ' fetchOrders requires a $symbol argument');
                }
            }
        }
        $this->load_markets();
        $request = array();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
        }
        $response = $this->privatePostActiveOrders (array_merge ($request, $params));
        // can only return 'open' orders (i.e. no way to fetch 'closed' orders)
        $openOrders = array();
        if (is_array($response) && array_key_exists('return', $response)) {
            $openOrders = $this->parse_orders($response['return'], $market);
        }
        $allOrders = $this->update_cached_orders ($openOrders, $symbol);
        $result = $this->filter_by_symbol($allOrders, $symbol);
        return $this->filter_by_since_limit($result, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, $params);
        return $this->filter_by($orders, 'status', 'open');
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, $params);
        return $this->filter_by($orders, 'status', 'closed');
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        // some derived classes use camelcase notation for $request fields
        $request = array (
            // 'from' => 123456789, // trade ID, from which the display starts numerical 0 (test result => liqui ignores this field)
            // 'count' => 1000, // the number of $trades for display numerical, default = 1000
            // 'from_id' => trade ID, from which the display starts numerical 0
            // 'end_id' => trade ID on which the display ends numerical ∞
            // 'order' => 'ASC', // sorting, default = DESC (test result => liqui ignores this field, most recent trade always goes last)
            // 'since' => 1234567890, // UTC start time, default = 0 (test result => liqui ignores this field)
            // 'end' => 1234567890, // UTC end time, default = ∞ (test result => liqui ignores this field)
            // 'pair' => 'eth_btc', // default = all markets
        );
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
        }
        if ($limit !== null) {
            $request['count'] = intval ($limit);
        }
        if ($since !== null) {
            $request['since'] = intval ($since / 1000);
        }
        $method = $this->options['fetchMyTradesMethod'];
        $response = $this->$method (array_merge ($request, $params));
        $trades = array();
        if (is_array($response) && array_key_exists('return', $response)) {
            $trades = $response['return'];
        }
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'coinName' => $currency['id'],
            'amount' => floatval ($amount),
            'address' => $address,
        );
        // no docs on the $tag, yet...
        if ($tag !== null) {
            throw new ExchangeError($this->id . ' withdraw() does not support the $tag argument yet due to a lack of docs on withdrawing with tag/memo on behalf of the exchange.');
        }
        $response = $this->privatePostWithdrawCoin (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['return']['tId'],
        );
    }

    public function sign_body_with_secret ($body) {
        return $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512');
    }

    public function get_version_string () {
        return '/' . $this->version;
    }

    public function get_private_path ($path, $params) {
        return '';
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api];
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'private') {
            $url .= $this->get_private_path ($path, $params);
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $body = $this->urlencode (array_merge (array (
                'nonce' => $nonce,
                'method' => $path,
            ), $query));
            $signature = $this->sign_body_with_secret($body);
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Key' => $this->apiKey,
                'Sign' => $signature,
            );
        } else if ($api === 'public') {
            $url .= $this->get_version_string() . '/' . $this->implode_params($path, $params);
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else {
            $url .= '/' . $this->implode_params($path, $params);
            if ($method === 'GET') {
                if ($query) {
                    $url .= '?' . $this->urlencode ($query);
                }
            } else {
                if ($query) {
                    $body = $this->json ($query);
                    $headers = array (
                        'Content-Type' => 'application/json',
                    );
                }
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response) {
        if ($response === null) {
            return; // fallback to default error handler
        }
        if (is_array($response) && array_key_exists('success', $response)) {
            //
            // 1 - Liqui only returns the integer 'success' key from their private API
            //
            //     array( "$success" => 1, ... ) $httpCode === 200
            //     array( "$success" => 0, ... ) $httpCode === 200
            //
            // 2 - However, exchanges derived from Liqui, can return non-integers
            //
            //     It can be a numeric string
            //     array( "sucesss" => "1", ... )
            //     array( "sucesss" => "0", ... ), $httpCode >= 200 (can be 403, 502, etc)
            //
            //     Or just a string
            //     array( "$success" => "true", ... )
            //     array( "$success" => "false", ... ), $httpCode >= 200
            //
            //     Or a boolean
            //     array( "$success" => true, ... )
            //     array( "$success" => false, ... ), $httpCode >= 200
            //
            // 3 - Oversimplified, Python PEP8 forbids comparison operator (===) of different types
            //
            // 4 - We do not want to copy-paste and duplicate the $code of this handler to other exchanges derived from Liqui
            //
            // To cover points 1, 2, 3 and 4 combined this handler should work like this:
            //
            $success = $this->safe_value($response, 'success', false);
            if (gettype ($success) === 'string') {
                if (($success === 'true') || ($success === '1')) {
                    $success = true;
                } else {
                    $success = false;
                }
            }
            if (!$success) {
                $code = $this->safe_string($response, 'code');
                $message = $this->safe_string($response, 'error');
                $feedback = $this->id . ' ' . $this->json ($response);
                $exact = $this->exceptions['exact'];
                if (is_array($exact) && array_key_exists($code, $exact)) {
                    throw new $exact[$code]($feedback);
                } else if (is_array($exact) && array_key_exists($message, $exact)) {
                    throw new $exact[$message]($feedback);
                }
                $broad = $this->exceptions['broad'];
                $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
                if ($broadKey !== null) {
                    throw new $broad[$broadKey]($feedback);
                }
                throw new ExchangeError($feedback); // unknown $message
            }
        }
    }
}
