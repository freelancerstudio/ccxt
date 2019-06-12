<?php

namespace ccxt;

use Exception as Exception; // a common import

class getbtc extends _1btcxe {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'getbtc',
            'name' => 'GetBTC',
            'countries' => array ( 'VC', 'RU' ), // Saint Vincent and the Grenadines, Russia, CIS
            'rateLimit' => 1000,
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/33801902-03c43462-dd7b-11e7-992e-077e4cd015b9.jpg',
                'api' => 'https://getbtc.org/api',
                'www' => 'https://getbtc.org',
                'doc' => 'https://getbtc.org/api-docs.php',
            ),
            'has' => array (
                'fetchTrades' => false,
                'fetchOHLCV' => false,
            ),
            'fees' => array (
                'trading' => array (
                    'taker' => 0.20 / 100,
                    'maker' => 0.20 / 100,
                ),
            ),
            'markets' => array (
                'BTC/USD' => array( 'symbol' => 'BTC/USD', 'quote' => 'USD', 'base' => 'BTC', 'precision' => array ( 'amount' => 8, 'price' => 8 ), 'id' => 'USD', 'limits' => array( 'amount' => array ( 'max' => null, 'min' => 1e-08 ), 'price' => array( 'max' => null, 'min' => 1e-08 ))),
                'BTC/EUR' => array( 'symbol' => 'BTC/EUR', 'quote' => 'EUR', 'base' => 'BTC', 'precision' => array ( 'amount' => 8, 'price' => 8 ), 'id' => 'EUR', 'limits' => array( 'amount' => array ( 'max' => null, 'min' => 1e-08 ), 'price' => array( 'max' => null, 'min' => 1e-08 ))),
                'BTC/RUB' => array( 'symbol' => 'BTC/RUB', 'quote' => 'RUB', 'base' => 'BTC', 'precision' => array ( 'amount' => 8, 'price' => 8 ), 'id' => 'RUB', 'limits' => array( 'amount' => array ( 'max' => null, 'min' => 1e-08 ), 'price' => array( 'max' => null, 'min' => 1e-08 ))),
            ),
        ));
    }
}
