<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\OrderNotFound;

class adara extends Exchange {

    public function describe () {
        return array_replace_recursive(parent::describe (), array(
            'id' => 'adara',
            'name' => 'Adara',
            'countries' => array( 'MT' ),
            'version' => 'v1',
            'rateLimit' => 1000,
            'certified' => false,
            // new metainfo interface
            'has' => array(
                'CORS' => false,
                'fetchCurrencies' => true,
                'fetchOrderBooks' => false,
                'createMarketOrder' => false,
                'fetchDepositAddress' => false,
                'fetchClosedOrders' => true,
                'fetchMyTrades' => false,
                'fetchOHLCV' => false,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchTickers' => true,
                'withdraw' => false,
                'fetchDeposits' => false,
                'fetchWithdrawals' => false,
                'fetchTransactions' => false,
            ),
            'requiredCredentials' => array(
                'apiKey' => true,
                'secret' => true,
                'token' => false,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/49189583-0466a780-f380-11e8-9248-57a631aad2d6.jpg',
                'api' => 'https://api.adara.io',
                'www' => 'https://adara.io',
                'doc' => 'https://api.adara.io/v1',
                'fees' => 'https://adara.io/fees',
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'currencies',
                        'limits',
                        'market',
                        'marketDepth',
                        'marketInfo',
                        'orderBook',
                        'quote/',
                        'quote/{id}',
                        'symbols',
                        'trade',
                    ),
                    'post' => array(
                        'confirmContactEmail',
                        'restorePassword',
                        'user', // sign up
                    ),
                ),
                'private' => array(
                    'get' => array(
                        'balance',
                        'order',
                        'order/{id}',
                        'currencyBalance',
                        'apiKey', // the list of apiKeys
                        'user/{id}',
                    ),
                    'post' => array(
                        'order',
                        'recovery',
                        'user',
                        'apiKey',  // sign in and optionally create an apiKey
                        'contact',
                    ),
                    'patch' => array(
                        'order/{id}',
                        'user/{id}', // change password
                        'customer', // update user info
                    ),
                    'delete' => array(
                        'apiKey',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.001,
                    'taker' => 0.001,
                ),
                'funding' => array(
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(),
                    'deposit' => array(),
                ),
            ),
            'exceptions' => array(
                'exact' => array(
                    'Insufficient funds' => '\\ccxt\\InsufficientFunds',
                    'Amount is too small' => '\\ccxt\\InvalidOrder',
                    'operation has invalid value' => '\\ccxt\\InvalidOrder',
                    "closed order can't be changed" => '\\ccxt\\InvalidOrder',
                    'Order is not found' => '\\ccxt\\OrderNotFound',
                    'AUTH' => '\\ccxt\\AuthenticationError',
                    'You are not authorized' => '\\ccxt\\AuthenticationError',
                    'Bad Request' => '\\ccxt\\BadRequest',
                    '500' => '\\ccxt\\ExchangeError',
                ),
                'broad' => array(),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $request = array(
            'include' => 'from,to',
        );
        $response = $this->publicGetSymbols (array_merge($request, $params));
        $included = $this->safe_value($response, 'included', array());
        $includedByType = $this->group_by($included, 'type');
        $currencies = $this->safe_value($includedByType, 'currency', array());
        $currenciesById = $this->index_by($currencies, 'id');
        //
        //     array(     meta => { total => 61 ),
        //           data => array( {            $id =>   "XRPUSD",
        //                              type =>   "$symbol",
        //                        $attributes => array( allowTrade =>  false,
        //                                       createdAt => "2018-10-23T09:31:06.830Z",
        //                                          digits =>  5,
        //                                        fullName => "XRPUSD",
        //                                        makerFee => "0.0250",
        //                                            name => "XRPUSD",
        //                                        takerFee => "0.0250",
        //                                       updatedAt => "2018-10-23T09:31:06.830Z"  ),
        //                     $relationships => array( from => array( data => array( $id => "XRP", type => "currency" ) ),
        //                                        to => array( data => array( $id => "USD", type => "currency" ) )  } ),
        //                   {            $id =>   "XRPETH",
        //                              type =>   "$symbol",
        //                        $attributes => array( allowTrade =>  true,
        //                                       createdAt => "2018-10-09T22:34:28.268Z",
        //                                          digits =>  8,
        //                                        fullName => "XRPETH",
        //                                        makerFee => "0.0025",
        //                                            name => "XRPETH",
        //                                        takerFee => "0.0025",
        //                                       updatedAt => "2018-10-09T22:34:28.268Z"  ),
        //                     $relationships => array( from => array( data => { $id => "XRP", type => "currency" ) ),
        //                                        to => array( data => array( $id => "ETH", type => "currency" ) )  } }  ),
        //       $included => array( array(            $id =>   "XRP",
        //                              type =>   "currency",
        //                        $attributes => array(               accuracy =>  4,
        //                                                      $active =>  true,
        //                                                allowDeposit =>  true,
        //                                                  allowTrade =>  false,
        //                                                 allowWallet =>  true,
        //                                               allowWithdraw =>  true,
        //                                                        name => "Ripple",
        //                                                   shortName => "XRP",
        //                                      transactionUriTemplate => "https://www.ripplescan.com/transactions/:txId",
        //                                           walletUriTemplate => "https://www.ripplescan.com/accounts/:address",
        //                                                 withdrawFee => "0.20000000",
        //                                           withdrawMinAmount => "22.00000000"                                    ),
        //                     $relationships => array(  )                                                                          ),
        //                   array(            $id =>   "ETH",
        //                              type =>   "currency",
        //                        $attributes => array(               accuracy =>  8,
        //                                                      $active =>  true,
        //                                                allowDeposit =>  true,
        //                                                  allowTrade =>  true,
        //                                                 allowWallet =>  true,
        //                                               allowWithdraw =>  true,
        //                                                        name => "Ethereum",
        //                                                   shortName => "ETH",
        //                                      transactionUriTemplate => "https://etherscan.io/tx/:txId",
        //                                           walletUriTemplate => "https://etherscan.io/address/:address",
        //                                                 withdrawFee => "0.00800000",
        //                                           withdrawMinAmount => "0.02000000"                             ),
        //                     $relationships => array(  )                                                                  ),
        //                   {            $id =>   "USD",
        //                              type =>   "currency",
        //                        $attributes => array(               accuracy =>  6,
        //                                                      $active =>  true,
        //                                                allowDeposit =>  false,
        //                                                  allowTrade =>  true,
        //                                                 allowWallet =>  false,
        //                                               allowWithdraw =>  false,
        //                                                        name => "USD",
        //                                                   shortName => "USD",
        //                                      transactionUriTemplate =>  null,
        //                                           walletUriTemplate =>  null,
        //                                                 withdrawFee => "0.00000000",
        //                                           withdrawMinAmount => "0.00000000"  ),
        //                     $relationships => array(  )                                       }                          ) }
        //
        $result = array();
        $markets = $response['data'];
        for ($i = 0; $i < count($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'id');
            $attributes = $this->safe_value($market, 'attributes', array());
            $relationships = $this->safe_value($market, 'relationships', array());
            $fromRelationship = $this->safe_value($relationships, 'from', array());
            $toRelationship = $this->safe_value($relationships, 'to', array());
            $fromRelationshipData = $this->safe_value($fromRelationship, 'data', array());
            $toRelationshipData = $this->safe_value($toRelationship, 'data', array());
            $baseId = $this->safe_string($fromRelationshipData, 'id');
            $quoteId = $this->safe_string($toRelationshipData, 'id');
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $baseCurrency = $this->safe_value($currenciesById, $baseId, array());
            $baseCurrencyAttributes = $this->safe_value($baseCurrency, 'attributes', array());
            $symbol = $base . '/' . $quote;
            $amountPrecision = $this->safe_integer($baseCurrencyAttributes, 'accuracy', 8);
            $pricePrecision = $this->safe_integer($attributes, 'digits', 8);
            $precision = array(
                'amount' => $amountPrecision,
                'price' => $pricePrecision,
            );
            $active = $this->safe_value($attributes, 'allowTrade');
            $maker = $this->safe_float($attributes, 'makerFee');
            $taker = $this->safe_float($attributes, 'takerFee');
            $result[] = array(
                'info' => $market,
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => $active,
                'maker' => $maker,
                'taker' => $taker,
                'precision' => $precision,
                'limits' => array(
                    'amount' => array(
                        'min' => pow(10, -$precision['amount']),
                        'max' => null,
                    ),
                    'price' => array(
                        'min' => pow(10, -$precision['price']),
                        'max' => null,
                    ),
                    'cost' => array(
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetCurrencies ($params);
        //
        //     array(     meta => { total => 22 ),
        //           data => array( array(            $id =>   "USD",
        //                              type =>   "$currency",
        //                        $attributes => array(               accuracy =>  6,
        //                                                      $active =>  true,
        //                                                $allowDeposit =>  false,
        //                                                  allowTrade =>  true,
        //                                                 allowWallet =>  false,
        //                                               $allowWithdraw =>  false,
        //                                                        name => "USD",
        //                                                   shortName => "USD",
        //                                      transactionUriTemplate =>  null,
        //                                           walletUriTemplate =>  null,
        //                                                 withdrawFee => "0.00000000",
        //                                           withdrawMinAmount => "0.00000000"  ),
        //                     relationships => array(  )                                       ),
        //                   {            $id =>   "BTC",
        //                              type =>   "$currency",
        //                        $attributes => array(               accuracy =>  8,
        //                                                      $active =>  true,
        //                                                $allowDeposit =>  true,
        //                                                  allowTrade =>  true,
        //                                                 allowWallet =>  true,
        //                                               $allowWithdraw =>  true,
        //                                                        name => "Bitcoin",
        //                                                   shortName => "BTC",
        //                                      transactionUriTemplate => "https://blockexplorer.com/tx/:txId",
        //                                           walletUriTemplate => "https://blockexplorer.com/address/:address",
        //                                                 withdrawFee => "0.00050000",
        //                                           withdrawMinAmount => "0.00200000"                                  ),
        //                     relationships => array(  )                                                                      }                           ),
        //       included => array()                                                                                                                          }
        //
        $currencies = $response['data'];
        $result = array();
        for ($i = 0; $i < count($currencies); $i++) {
            $currency = $currencies[$i];
            $id = $this->safe_string($currency, 'id');
            $attributes = $this->safe_value($currency, 'attributes', array());
            $code = $this->common_currency_code($id);
            $precision = $this->safe_integer($attributes, 'accuracy');
            $fee = $this->safe_float($attributes, 'withdrawFee');
            $active = $this->safe_value($attributes, 'active');
            $allowDeposit = $this->safe_value($attributes, 'allowDeposit');
            $allowWithdraw = $this->safe_value($attributes, 'allowWithdraw');
            $result[$code] = array(
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => $this->safe_string($attributes, 'name'),
                'active' => ($active && $allowDeposit && $allowWithdraw),
                'fee' => $fee,
                'precision' => $precision,
                'limits' => array(
                    'amount' => array(
                        'min' => pow(10, -$precision),
                        'max' => pow(10, $precision),
                    ),
                    'price' => array(
                        'min' => pow(10, -$precision),
                        'max' => pow(10, $precision),
                    ),
                    'cost' => array(
                        'min' => null,
                        'max' => null,
                    ),
                    'withdraw' => array(
                        'min' => $this->safe_float($attributes, 'withdrawMinAmount'),
                        'max' => pow(10, $precision),
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetBalance ($params);
        //
        //     {     $data => array( {          type =>   "$balance",
        //                                id =>   "U4f0f0940-39bf-45a8-90bc-12d2899db4f1_BALANCE_FOR_ETH",
        //                        $attributes => array(           totalBalance => 10000,
        //                                                    onOrders => 0,
        //                                      normalizedTotalBalance => 310,
        //                                          normalizedOnOrders => 0,
        //                                                  percentage => 3.004116443856034,
        //                                                serializedAt => 1543324487949      ),
        //                     $relationships => array(           currency => array( $data => { type => "currency", id => "ETH" ) ),
        //                                      normalizedCurrency => array( $data => array( type => "currency", id => "BTC" ) )  } }  ),
        //       included => array( array(          type =>   "currency",
        //                                id =>   "BTC",
        //                        $attributes => array(          name => "Bitcoin",
        //                                          shortName => "BTC",
        //                                             active =>  true,
        //                                           accuracy =>  8,
        //                                       allowDeposit =>  true,
        //                                      allowWithdraw =>  true,
        //                                        allowWallet =>  true,
        //                                         allowTrade =>  true,
        //                                       serializedAt =>  1543324487948 ),
        //                     $relationships => array(  )                               ),
        //                   {       type =>   "currency",
        //                             id =>   "ETH",
        //                     $attributes => {          name => "Ethereum",
        //                                       shortName => "ETH",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543324487948 } }      )                                  }
        //
        $result = array( 'info' => $response );
        $data = $this->safe_value($response, 'data');
        if ($data !== null) {
            for ($i = 0; $i < count($data); $i++) {
                $balance = $data[$i];
                $attributes = $this->safe_value($balance, 'attributes', array());
                $relationships = $this->safe_value($balance, 'relationships', array());
                $currencyRelationship = $this->safe_value($relationships, 'currency', array());
                $currencyRelationshipData = $this->safe_value($currencyRelationship, 'data');
                $currencyId = $this->safe_string($currencyRelationshipData, 'id');
                $code = $this->common_currency_code($currencyId);
                $account = $this->account ();
                $account['total'] = $this->safe_float($attributes, 'totalBalance');
                $account['used'] = $this->safe_float($attributes, 'onOrders');
                $result[$code] = $account;
            }
        }
        return $this->parse_balance($result);
    }

    public function get_symbol_from_market_id ($marketId, $market = null) {
        if ($marketId === null) {
            return null;
        }
        $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        if ($market !== null) {
            return $market['symbol'];
        }
        list($baseId, $quoteId) = explode('-', $marketId);
        $base = $this->common_currency_code($baseId);
        $quote = $this->common_currency_code($quoteId);
        return $base . '/' . $quote;
    }

    public function parse_order_book ($orderbook, $timestamp = null, $bidsKey = 'bids', $asksKey = 'asks', $priceKey = 'price', $amountKey = 'amount') {
        $bids = array();
        $asks = array();
        $numBidAsks = is_array($orderbook) ? count($orderbook) : 0;
        if ($numBidAsks > 0) {
            $timestamp = $this->safe_integer($orderbook[0]['attributes'], 'serializedAt');
        }
        for ($i = 0; $i < count($orderbook); $i++) {
            $bidask = $orderbook[$i];
            $attributes = $this->safe_value($bidask, 'attributes', array());
            $currenTimestamp = $this->safe_integer($attributes, 'serializedAt');
            $timestamp = max ($timestamp, $currenTimestamp);
            $id = $this->safe_string($bidask, 'id');
            if (mb_strpos($id, 'OBID') !== false) {
                $bids[] = $this->parse_bid_ask($bidask['attributes'], $priceKey, $amountKey);
            } else if (mb_strpos($id, 'OSID') !== false) {
                $asks[] = $this->parse_bid_ask($bidask['attributes'], $priceKey, $amountKey);
            } else {
                throw new ExchangeError($this->id . ' parseOrderBook encountered an unrecognized $bidask format => ' . $this->json ($bidask));
            }
        }
        return array(
            'bids' => $this->sort_by($bids, 0, true),
            'asks' => $this->sort_by($asks, 0),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'nonce' => null,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $filters = 'filters[$symbol]';
        $request = array();
        $request[$filters] = $market['id'];
        $response = $this->publicGetOrderBook (array_merge($request, $params));
        //
        //     { data => array( {       type =>   "orderBook",
        //                         id =>   "OBID0SLTCETHS0",
        //                 attributes => array(        price => 1,
        //                                     amount => 4,
        //                               serializedAt => 1543116143473 } ),
        //               {       type =>   "orderBook",
        //                         id =>   "OSID3SLTCETHS0",
        //                 attributes => {        price => 12,
        //                                     amount => 12,
        //                               serializedAt => 1543116143474 } }  ) }
        //
        return $this->parse_order_book($response['data'], null, 'bids', 'asks', 'price', 'amount');
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {          type =>   "quote",
        //                  id =>   "XRPETH",
        //          $attributes => array(  currentPrice => 1,
        //                                  low => 1,
        //                                 high => 1,
        //                           baseVolume => 0,
        //                          quoteVolume => 0,
        //                               $change => 0,
        //                        percentChange => 0,
        //                         serializedAt => 1543109275996 ),
        //       relationships => array( $symbol => array( data => array( type => "$symbol", id => "ETHBTC" ) ) ) }
        //
        $symbol = $this->get_symbol_from_market_id ($this->safe_string($ticker, 'id'), $market);
        $attributes = $this->safe_value($ticker, 'attributes', array());
        $timestamp = $this->safe_integer($attributes, 'serializedAt');
        $last = $this->safe_float($attributes, 'currentPrice');
        $change = $this->safe_float($attributes, 'change');
        $open = null;
        if ($change !== null) {
            if ($last !== null) {
                $open = $last - $change;
            }
        }
        $percentage = $this->safe_float($attributes, 'percentChange');
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($attributes, 'high'),
            'low' => $this->safe_float($attributes, 'low'),
            'bid' => null,
            'bidVolume' => null,
            'ask' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'baseVolume'),
            'quoteVolume' => $this->safe_float($ticker, 'quoteVolume'),
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetQuote ($params);
        $data = $this->safe_value($response, 'data', array());
        //
        //     {     $data => array( {          type =>   "quote",
        //                                id =>   "XRPETH",
        //                        attributes => array(  currentPrice => 1,
        //                                                low => 1,
        //                                               high => 1,
        //                                         baseVolume => 0,
        //                                        quoteVolume => 0,
        //                                             change => 0,
        //                                      percentChange => 0,
        //                                       serializedAt => 1543109275996 ),
        //                     relationships => array( $symbol => array( $data => array( type => "$symbol", id => "ETHBTC" ) ) ) }  ),
        //       included => array( array(          type =>   "currency",
        //                                id =>   "XRP",
        //                        attributes => array(          name => "Ripple",
        //                                          shortName => "XRP",
        //                                             active =>  true,
        //                                           accuracy =>  4,
        //                                       allowDeposit =>  true,
        //                                      allowWithdraw =>  true,
        //                                        allowWallet =>  true,
        //                                         allowTrade =>  false,
        //                                       serializedAt =>  1543109275996 ),
        //                     relationships => array(  )                               ),
        //                   {          type =>   "$symbol",
        //                                id =>   "XRPETH",
        //                        attributes => array(     fullName => "XRPETH",
        //                                            digits =>  8,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543109275996 ),
        //                     relationships => array( from => array( $data => { type => "currency", id => "XRP" ) ),
        //                                        to => array( $data => array( type => "currency", id => "ETH" ) )  } }  )    }
        //
        $result = array();
        for ($t = 0; $t < count($data); $t++) {
            $ticker = $this->parse_ticker($data[$t]);
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'id' => $market['id'],
        );
        $response = $this->publicGetQuoteId (array_merge($request, $params));
        //
        //     { included => array( {       type =>   "currency",
        //                             id =>   "ETH",
        //                     attributes => array(          name => "Ethereum",
        //                                       shortName => "ETH",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543111444033 } ),
        //                   {       type =>   "currency",
        //                             id =>   "BTC",
        //                     attributes => array(          name => "Bitcoin",
        //                                       shortName => "BTC",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543111444033 } ),
        //                   {          type =>   "$symbol",
        //                                id =>   "ETHBTC",
        //                        attributes => array(     fullName => "ETHBTC",
        //                                            digits =>  6,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543111444033 ),
        //                     relationships => array( from => array( data => { type => "currency", id => "ETH" ) ),
        //                                        to => array( data => array( type => "currency", id => "BTC" ) )  } } ),
        //           data => {          type =>   "quote",
        //                              id =>   "ETHBTC",
        //                      attributes => array(  currentPrice => 34,
        //                                              low => 34,
        //                                             high => 34,
        //                                       baseVolume => 0,
        //                                      quoteVolume => 0,
        //                                           change => 0,
        //                                    percentChange => 0,
        //                                     serializedAt => 1543111444033 ),
        //                   relationships => array( $symbol => array( data => array( type => "$symbol", id => "ETHBTC" ) ) ) }    } (fetchTicker @ adara.js:546)
        //
        return $this->parse_ticker($response['data']);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades
        //
        //
        //       {          type =>   "$trade",
        //                    $id =>   "1542988964359136846136847",
        //            $attributes => array(        $price =>  34,
        //                                $amount =>  4,
        //                                 total =>  136,
        //                             operation => "buy",
        //                             createdAt => "2018-11-23T16:02:44.359Z",
        //                          serializedAt =>  1543112364995              ),
        //         $relationships => array( $symbol => array( data => array( type => "$symbol", $id => "ETHBTC" ) ) ) } ],
        //
        $id = $this->safe_string($trade, 'id', 'uuid');
        $attributes = $this->safe_value($trade, 'attributes', array());
        $relationships = $this->safe_value($trade, 'relationships', array());
        $symbolRelationship = $this->safe_value($relationships, 'symbol', array());
        $symbolRelationshipData = $this->safe_value($symbolRelationship, 'data', array());
        $marketId = $this->safe_string($symbolRelationshipData, 'id');
        $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        $symbol = null;
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['quote'];
        } else if ($marketId !== null) {
            $baseIdLength = strlen($marketId) - 3;
            $baseId = mb_substr($marketId, 0, $baseIdLength - 0);
            $quoteId = mb_substr($marketId, $baseIdLength);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $feeCurrency = $quote;
        }
        $orderId = null;
        $timestamp = $this->parse8601 ($this->safe_string($attributes, 'createdAt'));
        $side = $this->safe_string($attributes, 'operation');
        $price = $this->safe_float($attributes, 'price');
        $amount = $this->safe_float($attributes, 'amount');
        $cost = $this->safe_float($attributes, 'total');
        if ($cost === null) {
            if ($amount !== null) {
                if ($price !== null) {
                    $cost = floatval ($this->cost_to_precision($symbol, $price * $amount));
                }
            }
        }
        $feeCost = $this->safe_float($attributes, 'fee');
        $fee = null;
        if ($feeCost !== null) {
            $fee = array(
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        return array(
            'id' => $id,
            'info' => $trade,
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $request = array(
            // 'id' => $market['id'],
        );
        $response = $this->publicGetTrade (array_merge($request, $params));
        //
        //     {     data => array( {          type =>   "trade",
        //                                id =>   "1542988964359136846136847",
        //                        attributes => array(        price =>  34,
        //                                            amount =>  4,
        //                                             total =>  136,
        //                                         operation => "buy",
        //                                         createdAt => "2018-11-23T16:02:44.359Z",
        //                                      serializedAt =>  1543112364995              ),
        //                     relationships => array( $symbol => array( data => array( type => "$symbol", id => "ETHBTC" ) ) ) } ),
        //       included => [ array(          type =>   "currency",
        //                                id =>   "ETH",
        //                        attributes => array(          name => "Ethereum",
        //                                          shortName => "ETH",
        //                                             active =>  true,
        //                                           accuracy =>  8,
        //                                       allowDeposit =>  true,
        //                                      allowWithdraw =>  true,
        //                                        allowWallet =>  true,
        //                                         allowTrade =>  true,
        //                                       serializedAt =>  1543112364995 ),
        //                     relationships => array(  )                               ),
        //                   array(          type =>   "currency",
        //                                id =>   "BTC",
        //                        attributes => array(          name => "Bitcoin",
        //                                                   ...
        //                                       serializedAt =>  1543112364995 ),
        //                     relationships => array(  )                               ),
        //                   {          type =>   "$symbol",
        //                                id =>   "ETHBTC",
        //                        attributes => array(     fullName => "ETHBTC",
        //                                            digits =>  6,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543112364995 ),
        //                     relationships => array( from => array( data => { type => "currency", id => "ETH" ) ),
        //                                        to => array( data => array( type => "currency", id => "BTC" ) )  } } }
        //
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'data' => array(
                'type' => 'order',
                'attributes' => array(
                    'amount' => floatval ($this->amount_to_precision($symbol, $amount)),
                    'operation' => $side,
                    'orderType' => $type,
                    'price' => floatval ($this->price_to_precision($symbol, $price)),
                ),
                'relationships' => array(
                    'symbol' => array(
                        'data' => array(
                            'id' => $market['id'],
                            'type' => 'symbol',
                        ),
                    ),
                ),
            ),
            'included' => [
                array(
                    'id' => $market['id'],
                    'type' => 'symbol',
                ),
            ],
        );
        $response = $this->privatePostOrder (array_merge($request, $params));
        //
        //     { included => array( {       $type =>   "currency",
        //                             id =>   "XLM",
        //                     attributes => array(          name => "Stellar",
        //                                       shortName => "XLM",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  false,
        //                                    serializedAt =>  1543434477449 } ),
        //                   {       $type =>   "currency",
        //                             id =>   "BTC",
        //                     attributes => array(          name => "Bitcoin",
        //                                       shortName => "BTC",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543434477449 } ),
        //                   {          $type =>   "$symbol",
        //                                id =>   "XLMBTC",
        //                        attributes => array(     fullName => "XLMBTC",
        //                                            digits =>  6,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543434477449 ),
        //                     relationships => array( from => array( data => { $type => "currency", id => "XLM" ) ),
        //                                        to => array( data => array( $type => "currency", id => "BTC" ) )  } } ),
        //           data => {          $type =>   "order",
        //                              id =>   "34793",
        //                      attributes => array( serializedAt =>    1543434477449,
        //                                       operation =>   "buy",
        //                                       orderType =>   "limit",
        //                                        clientId =>   "4733ea40-7d5c-4ddc-aec5-eb41baf90555",
        //                                          $amount =>    220,
        //                                           $price =>    0.000035,
        //                                    averagePrice =>    0,
        //                                             fee =>    0,
        //                                        timeOpen =>   "2018-11-28T19:47:57.435Z",
        //                                       timeClose =>    null,
        //                                          status =>   "open",
        //                                          filled =>    0,
        //                                           flags => array()                                        ),
        //                   relationships => array( $symbol => array( data => array( $type => "$symbol", id => "XLMBTC" ) ) )       } }
        //
        return $this->parse_order($response['data']);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $id,
            'data' => array(
                'attributes' => array(
                    'status' => 'canceled',
                ),
            ),
        );
        $response = $this->privatePatchOrderId (array_merge($request, $params));
        //
        //     { included => array( {       type =>   "currency",
        //                             $id =>   "XLM",
        //                     attributes => array(          name => "Stellar",
        //                                       shortName => "XLM",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  false,
        //                                    serializedAt =>  1543437874742 } ),
        //                   {       type =>   "currency",
        //                             $id =>   "BTC",
        //                     attributes => array(          name => "Bitcoin",
        //                                       shortName => "BTC",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543437874742 } ),
        //                   {          type =>   "$symbol",
        //                                $id =>   "XLMBTC",
        //                        attributes => array(     fullName => "XLMBTC",
        //                                            digits =>  6,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543437874742 ),
        //                     relationships => array( from => array( data => { type => "currency", $id => "XLM" ) ),
        //                                        to => array( data => array( type => "currency", $id => "BTC" ) )  } } ),
        //           data => {          type =>   "order",
        //                              $id =>   "34794",
        //                      attributes => array( serializedAt =>    1543437874742,
        //                                       operation =>   "buy",
        //                                       orderType =>   "limit",
        //                                        clientId =>   "4733ea40-7d5c-4ddc-aec5-eb41baf90555",
        //                                          amount =>    110,
        //                                           price =>    0.000034,
        //                                    averagePrice =>    0,
        //                                             fee =>    0,
        //                                        timeOpen =>   "2018-11-28T20:42:35.486Z",
        //                                       timeClose =>    null,
        //                                          status =>   "canceled",
        //                                          filled =>    0,
        //                                           flags => array()                                        ),
        //                   relationships => array( $symbol => array( data => array( type => "$symbol", $id => "XLMBTC" ) ) )       } }
        //
        return $this->parse_order($response['data']);
    }

    public function parse_order_status ($status) {
        $statuses = array(
            'open' => 'open',
            'closed' => 'closed',
            'canceled' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        //
        //         {          $type =>   "$order",
        //                      $id =>   "34793",
        //              $attributes => array( serializedAt =>  1543435013349,
        //                               operation => "buy",
        //                               orderType => "limit",
        //                                clientId => "4733ea40-7d5c-4ddc-aec5-eb41baf90555",
        //                                  $amount =>  220,
        //                                   $price =>  0.000035,
        //                            averagePrice =>  0.000035,
        //                                     $fee =>  0.0001925,
        //                                timeOpen => "2018-11-28T19:47:57.435Z",
        //                               timeClose => "2018-11-28T19:47:57.452Z",
        //                                  $status => "closed",
        //                                  $filled =>  220,
        //                                   flags =>  null                                   ),
        //           $relationships => array( $symbol => array( data => array( $type => "$symbol", $id => "XLMBTC" ) ) ) }
        //
        $id = $this->safe_string($order, 'id');
        $attributes = $this->safe_value($order, 'attributes', array());
        $relationships = $this->safe_value($order, 'relationships', array());
        $symbolRelationship = $this->safe_value($relationships, 'symbol', array());
        $symbolRelationshipData = $this->safe_value($symbolRelationship, 'data', array());
        $tradesRelationship = $this->safe_value($relationships, 'trades', array());
        $tradesRelationshipData = $this->safe_value($tradesRelationship, 'data');
        $marketId = $this->safe_string($symbolRelationshipData, 'id');
        $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        $feeCurrency = null;
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['quote'];
        } else if ($marketId !== null) {
            $baseIdLength = strlen($marketId) - 3;
            $baseId = mb_substr($marketId, 0, $baseIdLength - 0);
            $quoteId = mb_substr($marketId, $baseIdLength);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $feeCurrency = $quote;
        }
        $timestamp = $this->parse8601 ($this->safe_string($attributes, 'timeOpen'));
        $side = $this->safe_string($attributes, 'operation');
        $type = $this->safe_string($attributes, 'orderType');
        $status = $this->parse_order_status($this->safe_string($attributes, 'status'));
        $lastTradeTimestamp = $this->parse8601 ($this->safe_string($attributes, 'timeClose'));
        $price = $this->safe_float($attributes, 'price');
        $amount = $this->safe_float($attributes, 'amount');
        $filled = $this->safe_float($attributes, 'filled');
        $remaining = null;
        if ($amount !== null) {
            if ($filled !== null) {
                $remaining = max (0, $amount - $filled);
            }
        }
        $cost = null;
        $average = $this->safe_float($attributes, 'averagePrice');
        if ($cost === null) {
            if (($average !== null) && ($filled !== null)) {
                $cost = floatval ($this->cost_to_precision($symbol, $average * $filled));
            }
        }
        $fee = null;
        $feeCost = $this->safe_float($attributes, 'fee');
        if ($feeCost !== null) {
            $fee = array(
                'currency' => $feeCurrency,
                'cost' => $feeCost,
            );
        }
        $trades = null;
        if ($tradesRelationshipData !== null) {
            $numTrades = is_array($tradesRelationshipData) ? count($tradesRelationshipData) : 0;
            if ($numTrades > 0) {
                $trades = $this->parse_trades($tradesRelationshipData, $market);
            }
        }
        $result = array(
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => $type,
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

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'include' => 'trades',
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $filters = 'filters[$symbol]';
            $request[$filters] = $market['id'];
        }
        $response = $this->privateGetOrder (array_merge($request, $params));
        //
        //     {     data => [ {          type =>   "order",
        //                                id =>   "34793",
        //                        attributes => array( serializedAt =>  1543436770259,
        //                                         operation => "buy",
        //                                         orderType => "$limit",
        //                                            amount =>  220,
        //                                             price =>  0.000035,
        //                                      averagePrice =>  0.000035,
        //                                               fee =>  0.0001925,
        //                                          timeOpen => "2018-11-28T19:47:57.435Z",
        //                                         timeClose => "2018-11-28T19:47:57.452Z",
        //                                            status => "closed",
        //                                            filled =>  220,
        //                                             flags =>  null                       ),
        //                     relationships => array( $symbol => array( data => { type => "$symbol", id => "XLMBTC" ) ),
        //                                      trades => array( data => [array( type => "trade", id => "34789_34793" )] ) } } ],
        //       included => array( {       type =>   "currency",
        //                             id =>   "XLM",
        //                     attributes => array(          name => "Stellar",
        //                                       shortName => "XLM",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  false,
        //                                    serializedAt =>  1543436770259 } ),
        //                   {       type =>   "currency",
        //                             id =>   "BTC",
        //                     attributes => array(          name => "Bitcoin",
        //                                       shortName => "BTC",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543436770259 } ),
        //                   {          type =>   "$symbol",
        //                                id =>   "XLMBTC",
        //                        attributes => array(     fullName => "XLMBTC",
        //                                            digits =>  6,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543436770259 ),
        //                     relationships => array( from => array( data => array( type => "currency", id => "XLM" ) ),
        //                                        to => array( data => array( type => "currency", id => "BTC" ) )  } ),
        //                   {       type =>   "trade",
        //                             id =>   "34789_34793",
        //                     attributes => {       fee =>  0.0001925,
        //                                       price =>  0.000035,
        //                                      amount =>  220,
        //                                       total =>  0.0077,
        //                                   operation => "buy",
        //                                   createdAt => "2018-11-28T19:47:57.451Z" } }                ),
        //           meta => array( total => 1 )                                                                         }
        //
        return $this->parse_orders_response ($response, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $filters = 'filters[status]array()';
        $request = array();
        $request[$filters] = 'open';
        return $this->fetch_orders($symbol, $since, $limit, array_merge($request, $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $filters = 'filters[status]array()';
        $request = array();
        $request[$filters] = 'closed';
        return $this->fetch_orders($symbol, $since, $limit, array_merge($request, $params));
    }

    public function parse_orders_response ($response, $market = null, $since = null, $limit = null) {
        $included = $this->safe_value($response, 'included', array());
        $includedByType = $this->group_by($included, 'type');
        $unparsedTrades = $this->safe_value($includedByType, 'trade', array());
        $trades = $this->parse_trades($unparsedTrades, $market);
        $tradesById = $this->index_by($trades, 'id');
        $orders = $this->parse_orders($this->safe_value($response, 'data', array()), $market, $since, $limit);
        $result = array();
        for ($i = 0; $i < count($orders); $i++) {
            $order = $orders[$i];
            $orderTrades = array();
            $orderFee = $this->safe_value($order, 'fee', array());
            $orderFeeCurrency = $this->safe_string($orderFee, 'currency');
            if ($order['trades'] !== null) {
                for ($j = 0; $j < count($order['trades']); $j++) {
                    $orderTrade = $order['trades'][$j];
                    $orderTradeId = $orderTrade['id'];
                    if (is_array($tradesById) && array_key_exists($orderTradeId, $tradesById)) {
                        $orderTrades[] = array_replace_recursive($tradesById[$orderTradeId], array(
                            'order' => $order['id'],
                            'type' => $order['type'],
                            'symbol' => $order['symbol'],
                            'fee' => array(
                                'currency' => $orderFeeCurrency,
                            ),
                        ));
                    }
                }
            }
            $numOrderTrades = is_array($orderTrades) ? count($orderTrades) : 0;
            if ($numOrderTrades > 0) {
                $order['trades'] = $orderTrades;
            }
            $result[] = $order;
        }
        return $result;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $id,
            'include' => 'trades',
        );
        $response = $this->privateGetOrderId (array_merge($request, $params));
        //
        //     { included => array( {       type =>   "currency",
        //                             $id =>   "XLM",
        //                     attributes => array(          name => "Stellar",
        //                                       shortName => "XLM",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  false,
        //                                    serializedAt =>  1543436451996 } ),
        //                   {       type =>   "currency",
        //                             $id =>   "BTC",
        //                     attributes => array(          name => "Bitcoin",
        //                                       shortName => "BTC",
        //                                          active =>  true,
        //                                        accuracy =>  8,
        //                                    allowDeposit =>  true,
        //                                   allowWithdraw =>  true,
        //                                     allowWallet =>  true,
        //                                      allowTrade =>  true,
        //                                    serializedAt =>  1543436451996 } ),
        //                   {          type =>   "$symbol",
        //                                $id =>   "XLMBTC",
        //                        attributes => array(     fullName => "XLMBTC",
        //                                            digits =>  6,
        //                                        allowTrade =>  true,
        //                                      serializedAt =>  1543436451996 ),
        //                     relationships => array( from => array( $data => array( type => "currency", $id => "XLM" ) ),
        //                                        to => array( $data => array( type => "currency", $id => "BTC" ) )  } ),
        //                   {       type =>   "trade",
        //                             $id =>   "34789_34793",
        //                     attributes => {       fee =>  0.0001925,
        //                                       price =>  0.000035,
        //                                      amount =>  220,
        //                                       total =>  0.0077,
        //                                   operation => "buy",
        //                                   createdAt => "2018-11-28T19:47:57.451Z" } }                ),
        //           $data => {          type =>   "order",
        //                              $id =>   "34793",
        //                      attributes => array( serializedAt =>  1543436451996,
        //                                       operation => "buy",
        //                                       orderType => "limit",
        //                                        clientId => "4733ea40-7d5c-4ddc-aec5-eb41baf90555",
        //                                          amount =>  220,
        //                                           price =>  0.000035,
        //                                    averagePrice =>  0.000035,
        //                                             fee =>  0.0001925,
        //                                        timeOpen => "2018-11-28T19:47:57.435Z",
        //                                       timeClose => "2018-11-28T19:47:57.452Z",
        //                                          status => "closed",
        //                                          filled =>  220,
        //                                           flags =>  null                                   ),
        //                   relationships => array( $symbol => array( $data => { type => "$symbol", $id => "XLMBTC" ) ),
        //                                    trades => array( $data => [array( type => "trade", $id => "34789_34793" )] ) } } }
        //
        $data = $this->safe_value($response, 'data');
        $response['data'] = array();
        $response['data'][] = $data;
        $orders = $this->parse_orders_response ($response);
        $ordersById = $this->index_by($orders, 'id');
        if (is_array($ordersById) && array_key_exists($id, $ordersById)) {
            return $ordersById[$id];
        }
        throw new OrderNotFound($this->id . ' fetchOrder could not find order $id ' . (string) $id);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $payload = '/' . $this->implode_params($path, $params);
        $url = $this->urls['api'] . '/' . $this->version . $payload;
        $query = $this->omit ($params, $this->extract_params($path));
        if ($method === 'GET') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        }
        if ($api === 'private') {
            $nonce = $this->nonce ();
            $expiredAt = $this->sum ($nonce, $this->safe_integer($this->options, 'expiredAt', 10000));
            $expiredAt = (string) $expiredAt;
            if (($method === 'POST') || ($method === 'PATCH')) {
                $body = $this->json ($query);
                $payload = $body;
            }
            if ($this->token) {
                $headers = array(
                    'Cookie' => 'token=' . $this->token,
                );
            } else {
                $this->check_required_credentials();
                $auth = $method . $payload . 'expiredAt=' . $expiredAt;
                $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha512', 'base64');
                $headers = array(
                    'X-ADX-EXPIRE' => $expiredAt,
                    'X-ADX-APIKEY' => $this->apiKey,
                    'X-ADX-SIGNATURE' => $signature,
                );
            }
            if ($method !== 'GET') {
                $headers['Content-Type'] = 'application/json';
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return; // fallback to default $error handler
        }
        $errors = $this->safe_value($response, 'errors', array());
        $numErrors = is_array($errors) ? count($errors) : 0;
        if ($numErrors > 0) {
            $error = $errors[0];
            $code = $this->safe_string($error, 'code');
            $status = $this->safe_string($error, 'status');
            $title = $this->safe_string($error, 'title');
            $detail = $this->safe_string($error, 'detail');
            $feedback = $this->id . ' ' . $this->json ($response);
            $this->throw_exactly_matched_exception($this->exceptions['exact'], $code, $feedback);
            $this->throw_exactly_matched_exception($this->exceptions['exact'], $status, $feedback);
            $this->throw_exactly_matched_exception($this->exceptions['exact'], $title, $feedback);
            $this->throw_exactly_matched_exception($this->exceptions['exact'], $detail, $feedback);
            $this->throw_broadly_matched_exception($this->exceptions['broad'], $body, $feedback);
            throw new ExchangeError($feedback); // unknown message
        }
    }
}
