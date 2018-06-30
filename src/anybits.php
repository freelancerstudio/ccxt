<?php

namespace ccxt;

use Exception as Exception; // a common import

class anybits extends bitsane {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'anybits',
            'name' => 'Anybits',
            'countries' => array ( 'IE' ), // Ireland
            'has' => array (
                'fetchCurrencies' => true,
                'fetchTickers' => true,
                'fetchOpenOrders' => true,
                'fetchDepositAddress' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/41388454-ae227544-6f94-11e8-82a4-127d51d34903.jpg',
                'api' => 'https://anybits.com/api',
                'www' => 'https://anybits.com',
                'doc' => 'https://anybits.com/help/api',
                'fees' => 'https://anybits.com/help/fees',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'assets/currencies',
                        'assets/pairs',
                        'ticker',
                        'orderbook',
                        'trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balances',
                        'order/cancel',
                        'order/new',
                        'order/status',
                        'orders',
                        'orders/history',
                        'deposit/address',
                        'withdraw',
                        'withdrawal/status',
                        'transactions/history',
                        'vouchers',
                        'vouchers/create',
                        'vouchers/redeem',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.15 / 100,
                    'taker' => 0.25 / 100,
                ),
            ),
        ));
    }
}
