<?php

namespace ccxt;

use Exception as Exception; // a common import

class zaif extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'zaif',
            'name' => 'Zaif',
            'countries' => array ( 'JP' ),
            'rateLimit' => 2000,
            'version' => '1',
            'has' => array (
                'CORS' => false,
                'createMarketOrder' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766927-39ca2ada-5eeb-11e7-972f-1b4199518ca6.jpg',
                'api' => 'https://api.zaif.jp',
                'www' => 'https://zaif.jp',
                'doc' => array (
                    'http://techbureau-api-document.readthedocs.io/ja/latest/index.html',
                    'https://corp.zaif.jp/api-docs',
                    'https://corp.zaif.jp/api-docs/api_links',
                    'https://www.npmjs.com/package/zaif.jp',
                    'https://github.com/you21979/node-zaif',
                ),
                'fees' => 'https://zaif.jp/fee?lang=en',
            ),
            'fees' => array (
                'trading' => array (
                    'percentage' => true,
                    'taker' => -0.0001,
                    'maker' => -0.0005,
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'depth/{pair}',
                        'currencies/{pair}',
                        'currencies/all',
                        'currency_pairs/{pair}',
                        'currency_pairs/all',
                        'last_price/{pair}',
                        'ticker/{pair}',
                        'trades/{pair}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'active_orders',
                        'cancel_order',
                        'deposit_history',
                        'get_id_info',
                        'get_info',
                        'get_info2',
                        'get_personal_info',
                        'trade',
                        'trade_history',
                        'withdraw',
                        'withdraw_history',
                    ),
                ),
                'ecapi' => array (
                    'post' => array (
                        'createInvoice',
                        'getInvoice',
                        'getInvoiceIdsByOrderNumber',
                        'cancelInvoice',
                    ),
                ),
                'tlapi' => array (
                    'post' => array (
                        'get_positions',
                        'position_history',
                        'active_positions',
                        'create_position',
                        'change_position',
                        'cancel_position',
                    ),
                ),
                'fapi' => array (
                    'get' => array (
                        'groups/{group_id}',
                        'last_price/{group_id}/{pair}',
                        'ticker/{group_id}/{pair}',
                        'trades/{group_id}/{pair}',
                        'depth/{group_id}/{pair}',
                    ),
                ),
            ),
            'exceptions' => array (
                'exact' => array (
                    'unsupported currency_pair' => '\\ccxt\\BadRequest', // array ("error" => "unsupported currency_pair")
                ),
                'broad' => array (
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->publicGetCurrencyPairsAll ();
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = $market['currency_pair'];
            $symbol = $market['name'];
            list ($base, $quote) = explode ('/', $symbol);
            $precision = array (
                'amount' => -log10 ($market['item_unit_step']),
                'price' => $market['aux_unit_point'],
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'active' => true, // can trade or not
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($market, 'item_unit_min'),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => $this->safe_float($market, 'aux_unit_min'),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ();
        $balances = $response['return'];
        $result = array ( 'info' => $balances );
        $currencies = is_array ($balances['funds']) ? array_keys ($balances['funds']) : array ();
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $balance = $balances['funds'][$currency];
            $uppercase = strtoupper ($currency);
            $account = array (
                'free' => $balance,
                'used' => 0.0,
                'total' => $balance,
            );
            if (is_array ($balances) && array_key_exists ('deposit', $balances)) {
                if (is_array ($balances['deposit']) && array_key_exists ($currency, $balances['deposit'])) {
                    $account['total'] = $balances['deposit'][$currency];
                    $account['used'] = $account['total'] - $account['free'];
                }
            }
            $result[$uppercase] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetDepthPair (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $ticker = $this->publicGetTickerPair (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        $timestamp = $this->milliseconds ();
        $vwap = $ticker['vwap'];
        $baseVolume = $ticker['volume'];
        $quoteVolume = null;
        if ($baseVolume !== null && $vwap !== null)
            $quoteVolume = $baseVolume * $vwap;
        $last = $ticker['last'];
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $ticker['high'],
            'low' => $ticker['low'],
            'bid' => $ticker['bid'],
            'bidVolume' => null,
            'ask' => $ticker['ask'],
            'askVolume' => null,
            'vwap' => $vwap,
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

    public function parse_trade ($trade, $market = null) {
        $side = ($trade['trade_type'] === 'bid') ? 'buy' : 'sell';
        $timestamp = $trade['date'] * 1000;
        $id = $this->safe_string($trade, 'id');
        $id = $this->safe_string($trade, 'tid', $id);
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        if (!$market)
            $market = $this->markets_by_id[$trade['currency_pair']];
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

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetTradesPair (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        $numTrades = is_array ($response) ? count ($response) : 0;
        if ($numTrades === 1) {
            $firstTrade = $response[0];
            if (!$firstTrade) {
                $response = array ();
            }
        }
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        if ($type === 'market')
            throw new ExchangeError ($this->id . ' allows limit orders only');
        $response = $this->privatePostTrade (array_merge (array (
            'currency_pair' => $this->market_id($symbol),
            'action' => ($side === 'buy') ? 'bid' : 'ask',
            'amount' => $amount,
            'price' => $price,
        ), $params));
        return array (
            'info' => $response,
            'id' => (string) $response['return']['order_id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostCancelOrder (array_merge (array (
            'order_id' => $id,
        ), $params));
    }

    public function parse_order ($order, $market = null) {
        $side = ($order['action'] === 'bid') ? 'buy' : 'sell';
        $timestamp = intval ($order['timestamp']) * 1000;
        if (!$market)
            $market = $this->markets_by_id[$order['currency_pair']];
        $price = $order['price'];
        $amount = $order['amount'];
        return array (
            'id' => (string) $order['id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => 'open',
            'symbol' => $market['symbol'],
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => $price * $amount,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'trades' => null,
            'fee' => null,
        );
    }

    public function parse_orders ($orders, $market = null, $since = null, $limit = null) {
        $ids = is_array ($orders) ? array_keys ($orders) : array ();
        $result = array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $order = $orders[$id];
            $extended = array_merge ($order, array ( 'id' => $id ));
            $result[] = $this->parse_order($extended, $market);
        }
        return $this->filter_by_since_limit($result, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array (
            // 'is_token' => false,
            // 'is_token_both' => false,
        );
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['currency_pair'] = $market['id'];
        }
        $response = $this->privatePostActiveOrders (array_merge ($request, $params));
        return $this->parse_orders($response['return'], $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array (
            // 'from' => 0,
            // 'count' => 1000,
            // 'from_id' => 0,
            // 'end_id' => 1000,
            // 'order' => 'DESC',
            // 'since' => 1503821051,
            // 'end' => 1503821051,
            // 'is_token' => false,
        );
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['currency_pair'] = $market['id'];
        }
        $response = $this->privatePostTradeHistory (array_merge ($request, $params));
        return $this->parse_orders($response['return'], $market, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        if ($code === 'JPY') {
            throw new ExchangeError ($this->id . ' withdraw() does not allow ' . $code . ' withdrawals');
        }
        $request = array (
            'currency' => $currency['id'],
            'amount' => $amount,
            'address' => $address,
            // 'message' => 'Hi!', // XEM and others
            // 'opt_fee' => 0.003, // BTC and MONA only
        );
        if ($tag !== null) {
            $request['message'] = $tag;
        }
        $result = $this->privatePostWithdraw (array_merge ($request, $params));
        return array (
            'info' => $result,
            'id' => $result['return']['txid'],
            'fee' => $result['return']['fee'],
        );
    }

    public function nonce () {
        $nonce = floatval ($this->milliseconds () / 1000);
        return sprintf ('%.8f', $nonce);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/';
        if ($api === 'public') {
            $url .= 'api/' . $this->version . '/' . $this->implode_params($path, $params);
        } else if ($api === 'fapi') {
            $url .= 'fapi/' . $this->version . '/' . $this->implode_params($path, $params);
        } else {
            $this->check_required_credentials();
            if ($api === 'ecapi') {
                $url .= 'ecapi';
            } else if ($api === 'tlapi') {
                $url .= 'tlapi';
            } else {
                $url .= 'tapi';
            }
            $nonce = $this->nonce ();
            $body = $this->urlencode (array_merge (array (
                'method' => $path,
                'nonce' => $nonce,
            ), $params));
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Key' => $this->apiKey,
                'Sign' => $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512'),
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response = null) {
        if (!$this->is_json_encoded_object($body))
            return; // fallback to default $error handler
        $response = json_decode ($body, $as_associative_array = true);
        //
        //     array ("$error" => "unsupported currency_pair")
        //
        $feedback = $this->id . ' ' . $body;
        $error = $this->safe_string($response, 'error');
        if ($error !== null) {
            $exact = $this->exceptions['exact'];
            if (is_array ($exact) && array_key_exists ($error, $exact)) {
                throw new $exact[$error] ($feedback);
            }
            $broad = $this->exceptions['broad'];
            $broadKey = $this->findBroadlyMatchedKey ($broad, $error);
            if ($broadKey !== null) {
                throw new $broad[$broadKey] ($feedback);
            }
            throw new ExchangeError ($feedback); // unknown message
        }
        $success = $this->safe_value($response, 'success', true);
        if (!$success) {
            throw new ExchangeError ($feedback);
        }
    }
}
