<?php

namespace ccxt;

use Exception as Exception; // a common import

class allcoin extends okcoinusd {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'allcoin',
            'name' => 'Allcoin',
            'countries' => array ( 'CA' ),
            'has' => array (
                'CORS' => false,
            ),
            'extension' => '',
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/31561809-c316b37c-b061-11e7-8d5a-b547b4d730eb.jpg',
                'api' => array (
                    'web' => 'https://www.allcoin.com',
                    'public' => 'https://api.allcoin.com/api',
                    'private' => 'https://api.allcoin.com/api',
                ),
                'www' => 'https://www.allcoin.com',
                'doc' => 'https://www.allcoin.com/api_market/market',
            ),
            'api' => array (
                'web' => array (
                    'get' => array (
                        'Home/MarketOverViewDetail/',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'depth',
                        'kline',
                        'ticker',
                        'trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'batch_trade',
                        'cancel_order',
                        'order_history',
                        'order_info',
                        'orders_info',
                        'repayment',
                        'trade',
                        'trade_history',
                        'userinfo',
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $result = array ();
        $response = $this->webGetHomeMarketOverViewDetail ();
        $coins = $response['marketCoins'];
        for ($j = 0; $j < count ($coins); $j++) {
            $markets = $coins[$j]['Markets'];
            for ($k = 0; $k < count ($markets); $k++) {
                $market = $markets[$k]['Market'];
                $base = $market['Primary'];
                $quote = $market['Secondary'];
                $baseId = strtolower ($base);
                $quoteId = strtolower ($quote);
                $id = $baseId . '_' . $quoteId;
                $symbol = $base . '/' . $quote;
                $active = $market['TradeEnabled'] && $market['BuyEnabled'] && $market['SellEnabled'];
                $result[] = array (
                    'id' => $id,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'active' => $active,
                    'type' => 'spot',
                    'spot' => true,
                    'future' => false,
                    'maker' => $market['AskFeeRate'], // BidFeeRate 0, AskFeeRate 0.002, we use just the AskFeeRate here
                    'taker' => $market['AskFeeRate'], // BidFeeRate 0, AskFeeRate 0.002, we use just the AskFeeRate here
                    'precision' => array (
                        'amount' => $market['PrimaryDigits'],
                        'price' => $market['SecondaryDigits'],
                    ),
                    'limits' => array (
                        'amount' => array (
                            'min' => $market['MinTradeAmount'],
                            'max' => $market['MaxTradeAmount'],
                        ),
                        'price' => array (
                            'min' => $market['MinOrderPrice'],
                            'max' => $market['MaxOrderPrice'],
                        ),
                        'cost' => array (
                            'min' => null,
                            'max' => null,
                        ),
                    ),
                    'info' => $market,
                );
            }
        }
        return $result;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '-1' => 'canceled',
            '0' => 'open',
            '1' => 'open',
            '2' => 'closed',
            '10' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function get_create_date_field () {
        // allcoin typo create_data instead of create_date
        return 'create_data';
    }

    public function get_orders_field () {
        // allcoin typo order instead of orders (expected based on their API docs)
        return 'order';
    }
}
