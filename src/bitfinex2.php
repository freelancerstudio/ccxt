<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitfinex2 extends bitfinex {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitfinex2',
            'name' => 'Bitfinex v2',
            'countries' => 'VG',
            'version' => 'v2',
            // new metainfo interface
            'has' => array (
                'CORS' => true,
                'createLimitOrder' => false,
                'createMarketOrder' => false,
                'createOrder' => false,
                'deposit' => false,
                'editOrder' => false,
                'fetchDepositAddress' => false,
                'fetchClosedOrders' => false,
                'fetchFundingFees' => false,
                'fetchMyTrades' => false,
                'fetchOHLCV' => true,
                'fetchOpenOrders' => false,
                'fetchOrder' => true,
                'fetchTickers' => true,
                'fetchTradingFees' => false,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
                '5m' => '5m',
                '15m' => '15m',
                '30m' => '30m',
                '1h' => '1h',
                '3h' => '3h',
                '6h' => '6h',
                '12h' => '12h',
                '1d' => '1D',
                '1w' => '7D',
                '2w' => '14D',
                '1M' => '1M',
            ),
            'rateLimit' => 1500,
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766244-e328a50c-5ed2-11e7-947b-041416579bb3.jpg',
                'api' => 'https://api.bitfinex.com',
                'www' => 'https://www.bitfinex.com',
                'doc' => array (
                    'https://bitfinex.readme.io/v2/docs',
                    'https://github.com/bitfinexcom/bitfinex-api-node',
                ),
                'fees' => 'https://www.bitfinex.com/fees',
            ),
            'api' => array (
                'v1' => array (
                    'get' => array (
                        'symbols',
                        'symbols_details',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'platform/status',
                        'tickers',
                        'ticker/{symbol}',
                        'trades/{symbol}/hist',
                        'book/{symbol}/{precision}',
                        'book/{symbol}/P0',
                        'book/{symbol}/P1',
                        'book/{symbol}/P2',
                        'book/{symbol}/P3',
                        'book/{symbol}/R0',
                        'stats1/{key}:{size}:{symbol}/{side}/{section}',
                        'stats1/{key}:{size}:{symbol}/long/last',
                        'stats1/{key}:{size}:{symbol}/long/hist',
                        'stats1/{key}:{size}:{symbol}/short/last',
                        'stats1/{key}:{size}:{symbol}/short/hist',
                        'candles/trade:{timeframe}:{symbol}/{section}',
                        'candles/trade:{timeframe}:{symbol}/last',
                        'candles/trade:{timeframe}:{symbol}/hist',
                    ),
                    'post' => array (
                        'calc/trade/avg',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'auth/r/wallets',
                        'auth/r/orders/{symbol}',
                        'auth/r/orders/{symbol}/new',
                        'auth/r/orders/{symbol}/hist',
                        'auth/r/order/{symbol}:{id}/trades',
                        'auth/r/trades/{symbol}/hist',
                        'auth/r/positions',
                        'auth/r/funding/offers/{symbol}',
                        'auth/r/funding/offers/{symbol}/hist',
                        'auth/r/funding/loans/{symbol}',
                        'auth/r/funding/loans/{symbol}/hist',
                        'auth/r/funding/credits/{symbol}',
                        'auth/r/funding/credits/{symbol}/hist',
                        'auth/r/funding/trades/{symbol}/hist',
                        'auth/r/info/margin/{key}',
                        'auth/r/info/funding/{key}',
                        'auth/r/movements/{currency}/hist',
                        'auth/r/stats/perf:{timeframe}/hist',
                        'auth/r/alerts',
                        'auth/w/alert/set',
                        'auth/w/alert/{type}:{symbol}:{price}/del',
                        'auth/calc/order/avail',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.1 / 100,
                    'taker' => 0.2 / 100,
                ),
                'funding' => array (
                    'withdraw' => array (
                        'BTC' => 0.0005,
                        'BCH' => 0.0005,
                        'ETH' => 0.01,
                        'EOS' => 0.1,
                        'LTC' => 0.001,
                        'OMG' => 0.1,
                        'IOT' => 0.0,
                        'NEO' => 0.0,
                        'ETC' => 0.01,
                        'XRP' => 0.02,
                        'ETP' => 0.01,
                        'ZEC' => 0.001,
                        'BTG' => 0.0,
                        'DASH' => 0.01,
                        'XMR' => 0.04,
                        'QTM' => 0.01,
                        'EDO' => 0.5,
                        'DAT' => 1.0,
                        'AVT' => 0.5,
                        'SAN' => 0.1,
                        'USDT' => 5.0,
                        'SPK' => 9.2784,
                        'BAT' => 9.0883,
                        'GNT' => 8.2881,
                        'SNT' => 14.303,
                        'QASH' => 3.2428,
                        'YYW' => 18.055,
                    ),
                ),
            ),
        ));
    }

    public function is_fiat ($code) {
        $fiat = array (
            'USD' => 'USD',
            'EUR' => 'EUR',
        );
        return (is_array ($fiat) && array_key_exists ($code, $fiat));
    }

    public function get_currency_id ($code) {
        $isFiat = $this->is_fiat ($code);
        $prefix = $isFiat ? 'f' : 't';
        return $prefix . $code;
    }

    public function fetch_markets () {
        $markets = $this->v1GetSymbolsDetails ();
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = strtoupper ($market['pair']);
            $baseId = mb_substr ($id, 0, 3);
            $quoteId = mb_substr ($id, 3, 6);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $id = 't' . $id;
            $baseId = $this->get_currency_id ($baseId);
            $quoteId = $this->get_currency_id ($quoteId);
            $precision = array (
                'price' => $market['price_precision'],
                'amount' => $market['price_precision'],
            );
            $limits = array (
                'amount' => array (
                    'min' => floatval ($market['minimum_order_size']),
                    'max' => floatval ($market['maximum_order_size']),
                ),
                'price' => array (
                    'min' => pow (10, -$precision['price']),
                    'max' => pow (10, $precision['price']),
                ),
            );
            $limits['cost'] = array (
                'min' => $limits['amount']['min'] * $limits['price']['min'],
                'max' => null,
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => $limits,
                'lot' => pow (10, -$precision['amount']),
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostAuthRWallets ();
        $balanceType = $this->safe_string($params, 'type', 'exchange');
        $result = array ( 'info' => $response );
        for ($b = 0; $b < count ($response); $b++) {
            $balance = $response[$b];
            $accountType = $balance[0];
            $currency = $balance[1];
            $total = $balance[2];
            $available = $balance[4];
            if ($accountType === $balanceType) {
                if ($currency[0] === 't')
                    $currency = mb_substr ($currency, 1);
                $uppercase = strtoupper ($currency);
                $uppercase = $this->common_currency_code($uppercase);
                $account = $this->account ();
                $account['free'] = $available;
                $account['total'] = $total;
                if ($account['free'])
                    $account['used'] = $account['total'] - $account['free'];
                $result[$uppercase] = $account;
            }
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetBookSymbolPrecision (array_merge (array (
            'symbol' => $this->market_id($symbol),
            'precision' => 'R0',
        ), $params));
        $timestamp = $this->milliseconds ();
        $result = array (
            'bids' => array (),
            'asks' => array (),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'nonce' => null,
        );
        for ($i = 0; $i < count ($orderbook); $i++) {
            $order = $orderbook[$i];
            $price = $order[1];
            $amount = $order[2];
            $side = ($amount > 0) ? 'bids' : 'asks';
            $amount = abs ($amount);
            $result[$side][] = array ( $price, $amount );
        }
        $result['bids'] = $this->sort_by($result['bids'], 0, true);
        $result['asks'] = $this->sort_by($result['asks'], 0);
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $length = is_array ($ticker) ? count ($ticker) : 0;
        $last = $ticker[$length - 4];
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $ticker[$length - 2],
            'low' => $ticker[$length - 1],
            'bid' => $ticker[$length - 10],
            'bidVolume' => null,
            'ask' => $ticker[$length - 8],
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $ticker[$length - 6],
            'percentage' => $ticker[$length - 5],
            'average' => null,
            'baseVolume' => $ticker[$length - 3],
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetTickers (array_merge (array (
            'symbols' => implode (',', $this->ids),
        ), $params));
        $result = array ();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $tickers[$i];
            $id = $ticker[0];
            $market = $this->markets_by_id[$id];
            $symbol = $market['symbol'];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->markets[$symbol];
        $ticker = $this->publicGetTickerSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market) {
        list ($id, $timestamp, $amount, $price) = $trade;
        $side = ($amount < 0) ? 'sell' : 'buy';
        if ($amount < 0) {
            $amount = -$amount;
        }
        return array (
            'id' => (string) $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = 120, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'sort' => '-1',
            'limit' => $limit, // default = max = 120
        );
        if ($since !== null)
            $request['start'] = $since;
        $response = $this->publicGetTradesSymbolHist (array_merge ($request, $params));
        $trades = $this->sort_by($response, 1);
        return $this->parse_trades($trades, $market, null, $limit);
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = 100, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($since === null)
            $since = $this->milliseconds () - $this->parse_timeframe($timeframe) * $limit * 1000;
        $request = array (
            'symbol' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
            'sort' => 1,
            'limit' => $limit,
            'start' => $since,
        );
        $response = $this->publicGetCandlesTradeTimeframeSymbolHist (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        throw new NotSupported ($this->id . ' createOrder not implemented yet');
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        throw new NotSupported ($this->id . ' cancelOrder not implemented yet');
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        throw new NotSupported ($this->id . ' fetchOrder not implemented yet');
    }

    public function fetch_deposit_address ($currency, $params = array ()) {
        throw new NotSupported ($this->id . ' fetchDepositAddress() not implemented yet.');
    }

    public function withdraw ($currency, $amount, $address, $tag = null, $params = array ()) {
        throw new NotSupported ($this->id . ' withdraw not implemented yet');
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = 25, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'limit' => $limit,
            'end' => $this->seconds (),
        );
        if ($since !== null)
            $request['start'] = intval ($since / 1000);
        $response = $this->privatePostAuthRTradesSymbolHist (array_merge ($request, $params));
        // return $this->parse_trades($response, $market, $since, $limit); // not implemented yet for bitfinex v2
        return $response;
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'v1')
            $request = $api . $request;
        else
            $request = $this->version . $request;
        $url = $this->urls['api'] . '/' . $request;
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        }
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $body = $this->json ($query);
            $auth = '/api' . '/' . $request . $nonce . $body;
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha384');
            $headers = array (
                'bfx-nonce' => $nonce,
                'bfx-apikey' => $this->apiKey,
                'bfx-signature' => $signature,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if ($response) {
            if (is_array ($response) && array_key_exists ('message', $response)) {
                if (mb_strpos ($response['message'], 'not enough exchange balance') !== false)
                    throw new InsufficientFunds ($this->id . ' ' . $this->json ($response));
                throw new ExchangeError ($this->id . ' ' . $this->json ($response));
            }
            return $response;
        } else if ($response === '') {
            throw new ExchangeError ($this->id . ' returned empty response');
        }
        return $response;
    }
}
