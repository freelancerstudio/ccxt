<?php

namespace ccxt;

use Exception as Exception; // a common import

class cointiger extends huobipro {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'cointiger',
            'name' => 'CoinTiger',
            'countries' => 'CN',
            'hostname' => 'api.cointiger.pro',
            'has' => array (
                'fetchCurrencies' => false,
                'fetchTickers' => true,
                'fetchTradingLimits' => false,
                'fetchOrder' => false,
            ),
            'headers' => array (
                'Language' => 'en_US',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/39797261-d58df196-5363-11e8-9880-2ec78ec5bd25.jpg',
                'api' => array (
                    'public' => 'https://api.cointiger.pro/exchange/trading/api/market',
                    'private' => 'https://api.cointiger.pro/exchange/trading/api',
                    'exchange' => 'https://www.cointiger.pro/exchange',
                ),
                'www' => 'https://www.cointiger.pro',
                'referral' => 'https://www.cointiger.pro/exchange/register.html?refCode=FfvDtt',
                'doc' => 'https://github.com/cointiger/api-docs-en/wiki',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'history/kline', // 获取K线数据
                        'detail/merged', // 获取聚合行情(Ticker)
                        'depth', // 获取 Market Depth 数据
                        'trade', // 获取 Trade Detail 数据
                        'history/trade', // 批量获取最近的交易记录
                        'detail', // 获取 Market Detail 24小时成交量数据
                    ),
                ),
                'exchange' => array (
                    'get' => array (
                        'footer/tradingrule.html',
                        'api/public/market/detail',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'user/balance',
                        'order/new',
                        'order/history',
                        'order/trade',
                    ),
                    'post' => array (
                        'order',
                    ),
                    'delete' => array (
                        'order',
                    ),
                ),
            ),
            'exceptions' => array (
                '1' => '\\ccxt\\InsufficientFunds',
                '2' => '\\ccxt\\ExchangeError',
                '5' => '\\ccxt\\InvalidOrder',
                '6' => '\\ccxt\\InvalidOrder',
                '8' => '\\ccxt\\OrderNotFound',
                '16' => '\\ccxt\\AuthenticationError', // funding password not set
                '100001' => '\\ccxt\\ExchangeError',
                '100002' => '\\ccxt\\ExchangeNotAvailable',
                '100003' => '\\ccxt\\ExchangeError',
                '100005' => '\\ccxt\\AuthenticationError',
            ),
        ));
    }

    public function fetch_markets () {
        $result = array (
            array ( 'precision' => array ( 'amount' => 1, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'aacbtc', 'uppercaseId' => 'AACBTC', 'symbol' => 'AAC/BTC', 'base' => 'AAC', 'quote' => 'BTC', 'baseId' => 'aac', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'afcbtc', 'uppercaseId' => 'AFCBTC', 'symbol' => 'AFC/BTC', 'base' => 'AFC', 'quote' => 'BTC', 'baseId' => 'afc', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'avhbtc', 'uppercaseId' => 'AVHBTC', 'symbol' => 'AVH/BTC', 'base' => 'AVH', 'quote' => 'BTC', 'baseId' => 'avh', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'baieth', 'uppercaseId' => 'BAIETH', 'symbol' => 'BAI/ETH', 'base' => 'BAI', 'quote' => 'ETH', 'baseId' => 'bai', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 3, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'bchbtc', 'uppercaseId' => 'BCHBTC', 'symbol' => 'BCH/BTC', 'base' => 'BCH', 'quote' => 'BTC', 'baseId' => 'bch', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.001, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'bkbtbtc', 'uppercaseId' => 'BKBTBTC', 'symbol' => 'BKBT/BTC', 'base' => 'BKBT', 'quote' => 'BTC', 'baseId' => 'bkbt', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'bkbteth', 'uppercaseId' => 'BKBTETH', 'symbol' => 'BKBT/ETH', 'base' => 'BKBT', 'quote' => 'ETH', 'baseId' => 'bkbt', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'bptnbtc', 'uppercaseId' => 'BPTNBTC', 'symbol' => 'BPTN/BTC', 'base' => 'BPTN', 'quote' => 'BTC', 'baseId' => 'bptn', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 100, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 4, 'price' => 2 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'btcbitcny', 'uppercaseId' => 'BTCBITCNY', 'symbol' => 'BTC/BitCNY', 'base' => 'BTC', 'quote' => 'BitCNY', 'baseId' => 'btc', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.0001, 'max' => null ), 'price' => array ( 'min' => 0.01, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'btmbtc', 'uppercaseId' => 'BTMBTC', 'symbol' => 'BTM/BTC', 'base' => 'BTM', 'quote' => 'BTC', 'baseId' => 'btm', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 6 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'btmeth', 'uppercaseId' => 'BTMETH', 'symbol' => 'BTM/ETH', 'base' => 'BTM', 'quote' => 'ETH', 'baseId' => 'btm', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 0.000001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 3 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'btsbitcny', 'uppercaseId' => 'BTSBITCNY', 'symbol' => 'BTS/BitCNY', 'base' => 'BTS', 'quote' => 'BitCNY', 'baseId' => 'bts', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 0.001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'btsbtc', 'uppercaseId' => 'BTSBTC', 'symbol' => 'BTS/BTC', 'base' => 'BTS', 'quote' => 'BTC', 'baseId' => 'bts', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 6 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'btseth', 'uppercaseId' => 'BTSETH', 'symbol' => 'BTS/ETH', 'base' => 'BTS', 'quote' => 'ETH', 'baseId' => 'bts', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 0.000001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ctxcbtc', 'uppercaseId' => 'CTXCBTC', 'symbol' => 'CTXC/BTC', 'base' => 'CTXC', 'quote' => 'BTC', 'baseId' => 'ctxc', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ctxceth', 'uppercaseId' => 'CTXCETH', 'symbol' => 'CTXC/ETH', 'base' => 'CTXC', 'quote' => 'ETH', 'baseId' => 'ctxc', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'elfbtc', 'uppercaseId' => 'ELFBTC', 'symbol' => 'ELF/BTC', 'base' => 'ELF', 'quote' => 'BTC', 'baseId' => 'elf', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'eosbtc', 'uppercaseId' => 'EOSBTC', 'symbol' => 'EOS/BTC', 'base' => 'EOS', 'quote' => 'BTC', 'baseId' => 'eos', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 6 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'eoseth', 'uppercaseId' => 'EOSETH', 'symbol' => 'EOS/ETH', 'base' => 'EOS', 'quote' => 'ETH', 'baseId' => 'eos', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 0.000001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'etcbtc', 'uppercaseId' => 'ETCBTC', 'symbol' => 'ETC/BTC', 'base' => 'ETC', 'quote' => 'BTC', 'baseId' => 'etc', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 3, 'price' => 2 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ethbitcny', 'uppercaseId' => 'ETHBITCNY', 'symbol' => 'ETH/BitCNY', 'base' => 'ETH', 'quote' => 'BitCNY', 'baseId' => 'eth', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.001, 'max' => null ), 'price' => array ( 'min' => 0.01, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 3, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ethbtc', 'uppercaseId' => 'ETHBTC', 'symbol' => 'ETH/BTC', 'base' => 'ETH', 'quote' => 'BTC', 'baseId' => 'eth', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.001, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 2 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'gtobitcny', 'uppercaseId' => 'GTOBITCNY', 'symbol' => 'GTO/BitCNY', 'base' => 'GTO', 'quote' => 'BitCNY', 'baseId' => 'gto', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 0.01, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'gusbtc', 'uppercaseId' => 'GUSBTC', 'symbol' => 'GUS/BTC', 'base' => 'GUS', 'quote' => 'BTC', 'baseId' => 'gus', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 4, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'icxbtc', 'uppercaseId' => 'ICXBTC', 'symbol' => 'ICX/BTC', 'base' => 'ICX', 'quote' => 'BTC', 'baseId' => 'icx', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.0001, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'incbtc', 'uppercaseId' => 'INCBTC', 'symbol' => 'INC/BTC', 'base' => 'INC', 'quote' => 'BTC', 'baseId' => 'inc', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 5, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 6 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'inceth', 'uppercaseId' => 'INCETH', 'symbol' => 'INC/ETH', 'base' => 'INC', 'quote' => 'ETH', 'baseId' => 'inc', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 5, 'max' => null ), 'price' => array ( 'min' => 0.000001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'kkgbtc', 'uppercaseId' => 'KKGBTC', 'symbol' => 'KKG/BTC', 'base' => 'KKG', 'quote' => 'BTC', 'baseId' => 'kkg', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 6 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'kkgeth', 'uppercaseId' => 'KKGETH', 'symbol' => 'KKG/ETH', 'base' => 'KKG', 'quote' => 'ETH', 'baseId' => 'kkg', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 0.000001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ltcbtc', 'uppercaseId' => 'LTCBTC', 'symbol' => 'LTC/BTC', 'base' => 'LTC', 'quote' => 'BTC', 'baseId' => 'ltc', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'mexbtc', 'uppercaseId' => 'MEXBTC', 'symbol' => 'MEX/BTC', 'base' => 'MEX', 'quote' => 'BTC', 'baseId' => 'mex', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 100, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'mtbtc', 'uppercaseId' => 'MTBTC', 'symbol' => 'MT/BTC', 'base' => 'MT', 'quote' => 'BTC', 'baseId' => 'mt', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'mteth', 'uppercaseId' => 'MTETH', 'symbol' => 'MT/ETH', 'base' => 'MT', 'quote' => 'ETH', 'baseId' => 'mt', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 3 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ocnbitcny', 'uppercaseId' => 'OCNBITCNY', 'symbol' => 'OCN/BitCNY', 'base' => 'OCN', 'quote' => 'BitCNY', 'baseId' => 'ocn', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 0.001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'ocnbtc', 'uppercaseId' => 'OCNBTC', 'symbol' => 'OCN/BTC', 'base' => 'OCN', 'quote' => 'BTC', 'baseId' => 'ocn', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'olebtc', 'uppercaseId' => 'OLEBTC', 'symbol' => 'OLE/BTC', 'base' => 'OLE', 'quote' => 'BTC', 'baseId' => 'ole', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'oleeth', 'uppercaseId' => 'OLEETH', 'symbol' => 'OLE/ETH', 'base' => 'OLE', 'quote' => 'ETH', 'baseId' => 'ole', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'omgbtc', 'uppercaseId' => 'OMGBTC', 'symbol' => 'OMG/BTC', 'base' => 'OMG', 'quote' => 'BTC', 'baseId' => 'omg', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'repbtc', 'uppercaseId' => 'REPBTC', 'symbol' => 'REP/BTC', 'base' => 'REP', 'quote' => 'BTC', 'baseId' => 'rep', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'sdabtc', 'uppercaseId' => 'SDABTC', 'symbol' => 'SDA/BTC', 'base' => 'SDA', 'quote' => 'BTC', 'baseId' => 'sda', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'sdaeth', 'uppercaseId' => 'SDAETH', 'symbol' => 'SDA/ETH', 'base' => 'SDA', 'quote' => 'ETH', 'baseId' => 'sda', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'sntbtc', 'uppercaseId' => 'SNTBTC', 'symbol' => 'SNT/BTC', 'base' => 'SNT', 'quote' => 'BTC', 'baseId' => 'snt', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 1, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'socbtc', 'uppercaseId' => 'SOCBTC', 'symbol' => 'SOC/BTC', 'base' => 'SOC', 'quote' => 'BTC', 'baseId' => 'soc', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'sphbtc', 'uppercaseId' => 'SPHBTC', 'symbol' => 'SPH/BTC', 'base' => 'SPH', 'quote' => 'BTC', 'baseId' => 'sph', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 100, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'storjbtc', 'uppercaseId' => 'STORJBTC', 'symbol' => 'STORJ/BTC', 'base' => 'STORJ', 'quote' => 'BTC', 'baseId' => 'storj', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 1, 'price' => 3 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'tchbitcny', 'uppercaseId' => 'TCHBITCNY', 'symbol' => 'TCH/BitCNY', 'base' => 'TCH', 'quote' => 'BitCNY', 'baseId' => 'tch', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.1, 'max' => null ), 'price' => array ( 'min' => 0.001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 1, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'tchbtc', 'uppercaseId' => 'TCHBTC', 'symbol' => 'TCH/BTC', 'base' => 'TCH', 'quote' => 'BTC', 'baseId' => 'tch', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 3 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'trxbitcny', 'uppercaseId' => 'TRXBITCNY', 'symbol' => 'TRX/BitCNY', 'base' => 'TRX', 'quote' => 'BitCNY', 'baseId' => 'trx', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 0.001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'trxbtc', 'uppercaseId' => 'TRXBTC', 'symbol' => 'TRX/BTC', 'base' => 'TRX', 'quote' => 'BTC', 'baseId' => 'trx', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'trxeth', 'uppercaseId' => 'TRXETH', 'symbol' => 'TRX/ETH', 'base' => 'TRX', 'quote' => 'ETH', 'baseId' => 'trx', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'tusdbtc', 'uppercaseId' => 'TUSDBTC', 'symbol' => 'TUSD/BTC', 'base' => 'TUSD', 'quote' => 'BTC', 'baseId' => 'tusd', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 2, 'price' => 6 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'tusdeth', 'uppercaseId' => 'TUSDETH', 'symbol' => 'TUSD/ETH', 'base' => 'TUSD', 'quote' => 'ETH', 'baseId' => 'tusd', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.01, 'max' => null ), 'price' => array ( 'min' => 0.000001, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 1, 'price' => 2 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'xembitcny', 'uppercaseId' => 'XEMBITCNY', 'symbol' => 'XEM/BitCNY', 'base' => 'XEM', 'quote' => 'BitCNY', 'baseId' => 'xem', 'quoteId' => 'bitcny', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.1, 'max' => null ), 'price' => array ( 'min' => 0.01, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'yeebtc', 'uppercaseId' => 'YEEBTC', 'symbol' => 'YEE/BTC', 'base' => 'YEE', 'quote' => 'BTC', 'baseId' => 'yee', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 0, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'yeeeth', 'uppercaseId' => 'YEEETH', 'symbol' => 'YEE/ETH', 'base' => 'YEE', 'quote' => 'ETH', 'baseId' => 'yee', 'quoteId' => 'eth', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 1, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
            array ( 'precision' => array ( 'amount' => 4, 'price' => 8 ), 'tierBased' => false, 'percentage' => true, 'taker' => 0.001, 'maker' => 0.001, 'id' => 'zrxbtc', 'uppercaseId' => 'ZRXBTC', 'symbol' => 'ZRX/BTC', 'base' => 'ZRX', 'quote' => 'BTC', 'baseId' => 'zrx', 'quoteId' => 'btc', 'active' => true, 'info' => null, 'limits' => array ( 'amount' => array ( 'min' => 0.0001, 'max' => null ), 'price' => array ( 'min' => 1e-8, 'max' => null ), 'cost' => array ( 'min' => 0, 'max' => null ))),
        );
        $this->options['marketsByUppercaseId'] = $this->index_by($result, 'uppercaseId');
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $timestamp = $this->safe_integer($ticker, 'id');
        $close = $this->safe_float($ticker, 'last');
        $percentage = $this->safe_float($ticker, 'percentChange');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high24hr'),
            'low' => $this->safe_float($ticker, 'low24hr'),
            'bid' => $this->safe_float($ticker, 'highestBid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'lowestAsk'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $close,
            'last' => $close,
            'previousClose' => null,
            'change' => null,
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'baseVolume'),
            'quoteVolume' => $this->safe_float($ticker, 'quoteVolume'),
            'info' => $ticker,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetDepth (array_merge (array (
            'symbol' => $market['id'], // this endpoint requires a lowercase $market id
            'type' => 'step0',
        ), $params));
        $data = $response['data']['depth_data'];
        if (is_array ($data) && array_key_exists ('tick', $data)) {
            if (!$data['tick']) {
                throw new ExchangeError ($this->id . ' fetchOrderBook() returned empty $response => ' . $this->json ($response));
            }
            $orderbook = $data['tick'];
            $timestamp = $data['ts'];
            return $this->parse_order_book($orderbook, $timestamp, 'buys');
        }
        throw new ExchangeError ($this->id . ' fetchOrderBook() returned unrecognized $response => ' . $this->json ($response));
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $marketId = $market['uppercaseId'];
        $response = $this->exchangeGetApiPublicMarketDetail ($params);
        if (!(is_array ($response) && array_key_exists ($marketId, $response)))
            throw new ExchangeError ($this->id . ' fetchTicker $symbol ' . $symbol . ' (' . $marketId . ') not found');
        return $this->parse_ticker($response[$marketId], $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->exchangeGetApiPublicMarketDetail ($params);
        $result = array ();
        $ids = is_array ($response) ? array_keys ($response) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = null;
            $symbol = $id;
            if (is_array ($this->options['marketsByUppercaseId']) && array_key_exists ($id, $this->options['marketsByUppercaseId'])) {
                // this endpoint returns uppercase $ids
                $symbol = $this->options['marketsByUppercaseId'][$id]['symbol'];
                $market = $this->options['marketsByUppercaseId'][$id];
            }
            $result[$symbol] = $this->parse_ticker($response[$id], $market);
        }
        return $result;
    }

    public function parse_trade ($trade, $market = null) {
        //
        //     {
        //         "volume" => array (
        //             "$amount" => "1.000",
        //             "icon" => "",
        //             "title" => "成交量"
        //                   ),
        //         "$price" => array (
        //             "$amount" => "0.04978883",
        //             "icon" => "",
        //             "title" => "委托价格"
        //                  ),
        //         "created_at" => 1513245134000,
        //         "deal_price" => array (
        //             "$amount" => 0.04978883000000000000000000000000,
        //             "icon" => "",
        //             "title" => "成交价格"
        //                       ),
        //         "id" => 138
        //     }
        //
        $side = $this->safe_string($trade, 'side');
        $amount = null;
        $price = null;
        $cost = null;
        if ($side !== null) {
            $side = strtolower ($side);
            $price = $this->safe_float($trade, 'price');
            $amount = $this->safe_float($trade, 'amount');
        } else {
            $price = $this->safe_float($trade['price'], 'amount');
            $amount = $this->safe_float($trade['volume'], 'amount');
            $cost = $this->safe_float($trade['deal_price'], 'amount');
        }
        if ($amount !== null)
            if ($price !== null)
                if ($cost === null)
                    $cost = $amount * $price;
        $timestamp = $this->safe_value($trade, 'created_at');
        if ($timestamp === null)
            $timestamp = $this->safe_value($trade, 'ts');
        $iso8601 = ($timestamp !== null) ? $this->iso8601 ($timestamp) : null;
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        return array (
            'info' => $trade,
            'id' => (string) $trade['id'],
            'order' => null,
            'timestamp' => $timestamp,
            'datetime' => $iso8601,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = 1000, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        if ($limit !== null)
            $request['size'] = $limit;
        $response = $this->publicGetHistoryTrade (array_merge ($request, $params));
        return $this->parse_trades($response['data']['trade_data'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchOrders requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null)
            $limit = 100;
        $response = $this->privateGetOrderTrade (array_merge (array (
            'symbol' => $market['id'],
            'offset' => 1,
            'limit' => $limit,
        ), $params));
        return $this->parse_trades($response['data']['list'], $market, $since, $limit);
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = 1000, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'period' => $this->timeframes[$timeframe],
        );
        if ($limit !== null) {
            $request['size'] = $limit;
        }
        $response = $this->publicGetHistoryKline (array_merge ($request, $params));
        return $this->parse_ohlcvs($response['data']['kline_data'], $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetUserBalance ($params);
        //
        //     {
        //         "$code" => "0",
        //         "msg" => "suc",
        //         "data" => [array (
        //             "normal" => "1813.01144179",
        //             "lock" => "1325.42036785",
        //             "coin" => "btc"
        //         ), array (
        //             "normal" => "9551.96692244",
        //             "lock" => "547.06506717",
        //             "coin" => "eth"
        //         )]
        //     }
        //
        $balances = $response['data'];
        $result = array ( 'info' => $response );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $id = $balance['coin'];
            $code = strtoupper ($id);
            $code = $this->common_currency_code($code);
            if (is_array ($this->currencies_by_id) && array_key_exists ($id, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$id]['code'];
            }
            $account = $this->account ();
            $account['used'] = floatval ($balance['lock']);
            $account['free'] = floatval ($balance['normal']);
            $account['total'] = $this->sum ($account['used'], $account['free']);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_orders_by_status ($status = null, $symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchOrders requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null)
            $limit = 100;
        $method = ($status === 'open') ? 'privateGetOrderNew' : 'privateGetOrderHistory';
        $response = $this->$method (array_merge (array (
            'symbol' => $market['id'],
            'offset' => 1,
            'limit' => $limit,
        ), $params));
        return $this->parse_orders($response['data']['list'], $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_status ('open', $symbol, $since, $limit, $params);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_status ('closed', $symbol, $since, $limit, $params);
    }

    public function parse_order ($order, $market = null) {
        $side = $this->safe_string($order, 'side');
        $side = strtolower ($side);
        //
        //      {
        //            volume => array ( "$amount" => "0.054", "icon" => "", "title" => "volume" ),
        //         age_price => array ( "$amount" => "0.08377697", "icon" => "", "title" => "Avg $price" ),
        //              $side => "BUY",
        //             $price => array ( "$amount" => "0.00000000", "icon" => "", "title" => "$price" ),
        //        created_at => 1525569480000,
        //       deal_volume => array ( "$amount" => "0.64593598", "icon" => "", "title" => "Deal volume" ),
        //   "remain_volume" => array ( "$amount" => "1.00000000", "icon" => "", "title" => "尚未成交"
        //                id => 26834207,
        //             label => array ( go => "trade", title => "Traded", click => 1 ),
        //          side_msg => "Buy"
        //      ),
        //
        $type = null;
        $status = null;
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        $timestamp = $order['created_at'];
        $amount = $this->safe_float($order['volume'], 'amount');
        $remaining = (is_array ($order) && array_key_exists ('remain_volume', $order)) ? $this->safe_float($order['remain_volume'], 'amount') : null;
        $filled = (is_array ($order) && array_key_exists ('deal_volume', $order)) ? $this->safe_float($order['deal_volume'], 'amount') : null;
        $price = (is_array ($order) && array_key_exists ('age_price', $order)) ? $this->safe_float($order['age_price'], 'amount') : null;
        if ($price === null)
            $price = (is_array ($order) && array_key_exists ('price', $order)) ? $this->safe_float($order['price'], 'amount') : null;
        $cost = null;
        $average = null;
        if ($amount !== null) {
            if ($remaining !== null) {
                if ($filled === null)
                    $filled = $amount - $remaining;
            } else if ($filled !== null) {
                $cost = $filled * $price;
                $average = floatval ($cost / $filled);
                if ($remaining === null)
                    $remaining = $amount - $filled;
            }
        }
        if (($remaining !== null) && ($remaining > 0))
            $status = 'open';
        $result = array (
            'info' => $order,
            'id' => (string) $order['id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'average' => $average,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
        );
        return $result;
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        if (!$this->password)
            throw new AuthenticationError ($this->id . ' createOrder requires exchange.password to be set to user trading password (not login password!)');
        $this->check_required_credentials();
        $market = $this->market ($symbol);
        $orderType = ($type === 'limit') ? 1 : 2;
        $order = array (
            'symbol' => $market['id'],
            'side' => strtoupper ($side),
            'type' => $orderType,
            'volume' => $this->amount_to_precision($symbol, $amount),
            'capital_password' => $this->password,
        );
        if (($type === 'market') && ($side === 'buy')) {
            if ($price === null) {
                throw new InvalidOrder ($this->id . ' createOrder requires $price argument for $market buy orders to calculate total cost according to exchange rules');
            }
            $order['volume'] = $this->amount_to_precision($symbol, $amount * $price);
        }
        if ($type === 'limit') {
            $order['price'] = $this->price_to_precision($symbol, $price);
        } else {
            if ($price === null) {
                $order['price'] = $this->price_to_precision($symbol, 0);
            } else {
                $order['price'] = $this->price_to_precision($symbol, $price);
            }
        }
        $response = $this->privatePostOrder (array_merge ($order, $params));
        //
        //     array ( "order_id":34343 )
        //
        $timestamp = $this->milliseconds ();
        return array (
            'info' => $response,
            'id' => (string) $response['data']['order_id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'cost' => null,
            'trades' => null,
            'fee' => null,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' cancelOrder requires a $symbol argument');
        $market = $this->market ($symbol);
        $response = $this->privateDeleteOrder (array_merge (array (
            'symbol' => $market['id'],
            'order_id' => $id,
        ), $params));
        return array (
            'id' => $id,
            'symbol' => $symbol,
            'info' => $response,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $this->check_required_credentials();
        $url = $this->urls['api'][$api] . '/' . $this->implode_params($path, $params);
        if ($api === 'private') {
            $timestamp = (string) $this->milliseconds ();
            $query = $this->keysort (array_merge (array (
                'time' => $timestamp,
            ), $params));
            $keys = is_array ($query) ? array_keys ($query) : array ();
            $auth = '';
            for ($i = 0; $i < count ($keys); $i++) {
                $auth .= $keys[$i] . $query[$keys[$i]];
            }
            $auth .= $this->secret;
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha512');
            $isCreateOrderMethod = ($path === 'order') && ($method === 'POST');
            $urlParams = $isCreateOrderMethod ? array () : $query;
            $url .= '?' . $this->urlencode ($this->keysort (array_merge (array (
                'api_key' => $this->apiKey,
                'time' => $timestamp,
            ), $urlParams)));
            $url .= '&sign=' . $signature;
            if ($method === 'POST') {
                $body = $this->urlencode ($query);
                $headers = array (
                    'Content-Type' => 'application/x-www-form-urlencoded',
                );
            }
        } else if ($api === 'public') {
            $url .= '?' . $this->urlencode (array_merge (array (
                'api_key' => $this->apiKey,
            ), $params));
        } else {
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            if (is_array ($response) && array_key_exists ('code', $response)) {
                //
                //     array ( "$code" => "100005", "msg" => "request sign illegal", "data" => null )
                //
                $code = $this->safe_string($response, 'code');
                if (($code !== null) && ($code !== '0')) {
                    $message = $this->safe_string($response, 'msg');
                    $feedback = $this->id . ' ' . $this->json ($response);
                    $exceptions = $this->exceptions;
                    if (is_array ($exceptions) && array_key_exists ($code, $exceptions)) {
                        if ($code === 2) {
                            if ($message === 'offsetNot Null') {
                                throw new ExchangeError ($feedback);
                            } else if ($message === 'Parameter error') {
                                throw new ExchangeError ($feedback);
                            }
                        }
                        throw new $exceptions[$code] ($feedback);
                    } else {
                        throw new ExchangeError ($this->id . ' unknown "error" value => ' . $this->json ($response));
                    }
                }
            }
        }
    }
}
