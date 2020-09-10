<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\ArgumentsRequired;
use \ccxt\InvalidOrder;

class bcex extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'bcex',
            'name' => 'BCEX',
            'countries' => array( 'CN', 'HK' ),
            'version' => '1',
            'has' => array(
                'cancelOrder' => true,
                'createOrder' => true,
                'fetchBalance' => true,
                'fetchClosedOrders' => 'emulated',
                'fetchMarkets' => true,
                'fetchMyTrades' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOrderBook' => true,
                'fetchTicker' => true,
                'fetchTickers' => false,
                'fetchTrades' => true,
                'fetchTradingLimits' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/51840849/77231516-851c6900-6bac-11ea-8fd6-ee5c23eddbd4.jpg',
                'api' => 'https://www.bcex.top',
                'www' => 'https://www.bcex.top',
                'doc' => 'https://github.com/BCEX-TECHNOLOGY-LIMITED/API_Docs/wiki/Interface',
                'fees' => 'https://bcex.udesk.cn/hc/articles/57085',
                'referral' => 'https://www.bcex.top/register?invite_code=758978&lang=en',
            ),
            'status' => array(
                'status' => 'error',
                'updated' => null,
                'eta' => null,
                'url' => null,
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'Api_Market/getPriceList', // tickers
                        'Api_Order/ticker', // last ohlcv candle (ticker)
                        'Api_Order/depth', // orderbook
                        'Api_Market/getCoinTrade', // ticker
                        'Api_Order/marketOrder', // trades...
                    ),
                    'post' => array(
                        'Api_Market/getPriceList', // tickers
                        'Api_Order/ticker', // last ohlcv candle (ticker)
                        'Api_Order/depth', // orderbook
                        'Api_Market/getCoinTrade', // ticker
                        'Api_Order/marketOrder', // trades...
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'Api_Order/cancel',
                        'Api_Order/coinTrust', // limit order
                        'Api_Order/orderList', // open / all orders (my trades?)
                        'Api_Order/orderInfo',
                        'Api_Order/tradeList', // open / all orders
                        'Api_Order/trustList', // ?
                        'Api_User/userBalance',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.1 / 100,
                    'taker' => 0.2 / 100,
                ),
                'funding' => array(
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(
                        'ckusd' => 0.0,
                        'other' => 0.05 / 100,
                    ),
                    'deposit' => array(),
                ),
            ),
            'exceptions' => array(
                '该币不存在,非法操作' => '\\ccxt\\ExchangeError', // array( code => 1, msg => "该币不存在,非法操作" ) - returned when a required symbol parameter is missing in the request (also, maybe on other types of errors as well)
                '公钥不合法' => '\\ccxt\\AuthenticationError', // array( code => 1, msg => '公钥不合法' ) - wrong public key
                '您的可用余额不足' => '\\ccxt\\InsufficientFunds', // array( code => 1, msg => '您的可用余额不足' ) - your available balance is insufficient
                '您的btc不足' => '\\ccxt\\InsufficientFunds', // array( code => 1, msg => '您的btc不足' ) - your btc is insufficient
                '参数非法' => '\\ccxt\\InvalidOrder', // array('code' => 1, 'msg' => '参数非法') - 'Parameter illegal'
                '订单信息不存在' => '\\ccxt\\OrderNotFound', // array('code' => 1, 'msg' => '订单信息不存在') - 'Order information does not exist'
            ),
            'commonCurrencies' => array(
                'PNT' => 'Penta',
            ),
            'options' => array(
                'limits' => array(
                    // hardcoding is deprecated, using these predefined values is not recommended, use loadTradingLimits instead
                    'AFC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 6, 'max' => 120000 ))),
                    'AFC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 6, 'max' => 120000 ))),
                    'AFT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 15, 'max' => 300000 ))),
                    'AICC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 50000 ))),
                    'AIDOC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 100000 ))),
                    'AISI/ETH' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.001, 'max' => 500 ))),
                    'AIT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 20, 'max' => 400000 ))),
                    'ANS/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => 500 ))),
                    'ANS/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => 1000 ))),
                    'ARC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 60, 'max' => 600000 ))),
                    'AXF/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => 1000000 ))),
                    'BASH/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 250, 'max' => 3000000 ))),
                    'BATT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 60, 'max' => 1500000 ))),
                    'BCD/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.3, 'max' => 7000 ))),
                    'BHPC/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 2, 'max' => 70000 ))),
                    'BHPC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 2, 'max' => 60000 ))),
                    'BOPO/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => 2000000 ))),
                    'BOPO/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => 10000000 ))),
                    'BTC/CKUSD' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.001, 'max' => 10 ))),
                    'BTC/CNET' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.0005, 'max' => 5 ))),
                    'BTC/USDT' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.0002, 'max' => 4 ))),
                    'BTE/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 25, 'max' => 250000 ))),
                    'BU/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 20, 'max' => 400000 ))),
                    'CIC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 3000, 'max' => 30000000 ))),
                    'CIT/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 4, 'max' => 40000 ))),
                    'CIT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 4, 'max' => 40000 ))),
                    'CMT/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 2500000 ))),
                    'CNET/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 12, 'max' => 120000 ))),
                    'CNMC/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 4, 'max' => 50000 ))),
                    'CTC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 550000 ))),
                    'CZR/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 12, 'max' => 500000 ))),
                    'DCON/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 8, 'max' => 300000 ))),
                    'DCT/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 2, 'max' => 40000 ))),
                    'DCT/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 3 ), 'limits' => array( 'amount' => array( 'min' => 2, 'max' => 2000 ))),
                    'DOGE/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 3000, 'max' => 14000000 ))),
                    'DOGE/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 500, 'max' => 2000000 ))),
                    'DRCT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 16, 'max' => 190000 ))),
                    'ELA/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.02, 'max' => 500 ))),
                    'ELF/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => 100000 ))),
                    'ELF/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 3 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => 100000 ))),
                    'EOS/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.5, 'max' => 5000 ))),
                    'EOS/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 2.5, 'max' => 30000 ))),
                    'EOS/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.18, 'max' => 1800 ))),
                    'ETC/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.2, 'max' => 2500 ))),
                    'ETC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.2, 'max' => 2500 ))),
                    'ETF/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 7, 'max' => 150000 ))),
                    'ETH/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.015, 'max' => 100 ))),
                    'ETH/CKUSD' => array( 'precision' => array( 'amount' => 4, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 0.005, 'max' => 100 ))),
                    'ETH/USDT' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.005, 'max' => 100 ))),
                    'FCT/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.24, 'max' => 1000 ))),
                    'FCT/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.24, 'max' => 1000 ))),
                    'GAME/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => 10000 ))),
                    'GOOC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 200, 'max' => 2000000 ))),
                    'GP/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 600, 'max' => 6000000 ))),
                    'HSC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 1000, 'max' => 20000000 ))),
                    'IFISH/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 300, 'max' => 8000000 ))),
                    'IIC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 50, 'max' => 4000000 ))),
                    'IMOS/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 15, 'max' => 300000 ))),
                    'JC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 300, 'max' => 3000000 ))),
                    'LBTC/BTC' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => 3000 ))),
                    'LEC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 500, 'max' => 5000000 ))),
                    'LKY/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 70000 ))),
                    'LKY/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 100000 ))),
                    'LMC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 25, 'max' => 250000 ))),
                    'LSK/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.3, 'max' => 3000 ))),
                    'LTC/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => 500 ))),
                    'LTC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => 500 ))),
                    'LTC/USDT' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.02, 'max' => 450 ))),
                    'MC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 10000, 'max' => 100000000 ))),
                    'MCC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 30, 'max' => 350000 ))),
                    'MOC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 25, 'max' => 600000 ))),
                    'MRYC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 300, 'max' => 3000000 ))),
                    'MT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 200, 'max' => 6000000 ))),
                    'MXI/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 5000, 'max' => 60000000 ))),
                    'NAI/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 100000 ))),
                    'NAS/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.2, 'max' => 15000 ))),
                    'NAS/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.5, 'max' => 5000 ))),
                    'NEWOS/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 65, 'max' => 700000 ))),
                    'NKN/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 3, 'max' => 350000 ))),
                    'NTK/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 2, 'max' => 30000 ))),
                    'ONT/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 3 ), 'limits' => array( 'amount' => array( 'min' => 0.2, 'max' => 2000 ))),
                    'ONT/ETH' => array( 'precision' => array( 'amount' => 3, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => 1000 ))),
                    'PNT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 80, 'max' => 800000 ))),
                    'PST/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 100000 ))),
                    'PTT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 450, 'max' => 10000000 ))),
                    'QTUM/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.4, 'max' => 2800 ))),
                    'QTUM/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => 1000 ))),
                    'RATING/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 500, 'max' => 10000000 ))),
                    'RHC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 1000, 'max' => 10000000 ))),
                    'SDA/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 20, 'max' => 500000 ))),
                    'SDD/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 3 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 100000 ))),
                    'SHC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 250, 'max' => 2500000 ))),
                    'SHE/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => 5000000 ))),
                    'SMC/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 1000, 'max' => 10000000 ))),
                    'SOP/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 50, 'max' => 1000000 ))),
                    'TAC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 35, 'max' => 800000 ))),
                    'TIP/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 7, 'max' => 200000 ))),
                    'TKT/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 40, 'max' => 400000 ))),
                    'TLC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 500, 'max' => 10000000 ))),
                    'TNC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 110000 ))),
                    'TUB/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 200, 'max' => 8000000 ))),
                    'UC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => 3000000 ))),
                    'UDB/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 2000, 'max' => 40000000 ))),
                    'UIC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 150000 ))),
                    'VAAC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 250000 ))),
                    'VPN/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 200, 'max' => 2000000 ))),
                    'VSC/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 30, 'max' => 650000 ))),
                    'WAVES/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 3 ), 'limits' => array( 'amount' => array( 'min' => 0.15, 'max' => 1500 ))),
                    'WDNA/ETH' => array( 'precision' => array( 'amount' => 2, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => 250000 ))),
                    'WIC/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 3, 'max' => 30000 ))),
                    'XAS/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 2.5, 'max' => 25000 ))),
                    'XLM/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 300000 ))),
                    'XLM/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => 300000 ))),
                    'XLM/USDT' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 150000 ))),
                    'XRP/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 24, 'max' => 100000 ))),
                    'XRP/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 3 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => 50000 ))),
                    'YBCT/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 15, 'max' => 200000 ))),
                    'YBCT/CKUSD' => array( 'precision' => array( 'amount' => 2, 'price' => 4 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => 200000 ))),
                    'YBY/CNET' => array( 'precision' => array( 'amount' => 2, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 25000, 'max' => 250000000 ))),
                    'ZEC/BTC' => array( 'precision' => array( 'amount' => 4, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.02, 'max' => 100 ))),
                    'ZEC/CKUSD' => array( 'precision' => array( 'amount' => 4, 'price' => 2 ), 'limits' => array( 'amount' => array( 'min' => 0.02, 'max' => 100 ))),
                ),
            ),
        ));
    }

    public function fetch_trading_limits($symbols = null, $params = array ()) {
        // this method should not be called directly, use loadTradingLimits () instead
        // by default it will try load withdrawal fees of all currencies (with separate requests, sequentially)
        // however if you define $symbols = array( 'ETH/BTC', 'LTC/BTC' ) in args it will only load those
        $this->load_markets();
        if ($symbols === null) {
            $symbols = $this->symbols;
        }
        $result = array();
        for ($i = 0; $i < count($symbols); $i++) {
            $symbol = $symbols[$i];
            $result[$symbol] = $this->fetch_trading_limits_by_id($this->market_id($symbol), $params);
        }
        return $result;
    }

    public function fetch_trading_limits_by_id($id, $params = array ()) {
        $request = array(
            'symbol' => $id,
        );
        $response = $this->publicPostApiOrderTicker (array_merge($request, $params));
        //
        //     {  code =>    0,
        //         msg =>   "获取牌价信息成功",
        //        data => {         high =>  0.03721392,
        //                         low =>  0.03335362,
        //                         buy => "0.03525757",
        //                        sell => "0.03531160",
        //                        last =>  0.0352634,
        //                         vol => "184742.4176",
        //                   min_trade => "0.01500000",
        //                   max_trade => "100.00000000",
        //                number_float => "4",
        //                 price_float => "8"             } } }
        //
        return $this->parse_trading_limits($this->safe_value($response, 'data', array()));
    }

    public function parse_trading_limits($limits, $symbol = null, $params = array ()) {
        //
        //  {         high =>  0.03721392,
        //             low =>  0.03335362,
        //             buy => "0.03525757",
        //            sell => "0.03531160",
        //            last =>  0.0352634,
        //             vol => "184742.4176",
        //       min_trade => "0.01500000",
        //       max_trade => "100.00000000",
        //    number_float => "4",
        //     price_float => "8"             }
        //
        return array(
            'info' => $limits,
            'precision' => array(
                'amount' => $this->safe_integer($limits, 'number_float'),
                'price' => $this->safe_integer($limits, 'price_float'),
            ),
            'limits' => array(
                'amount' => array(
                    'min' => $this->safe_float($limits, 'min_trade'),
                    'max' => $this->safe_float($limits, 'max_trade'),
                ),
            ),
        );
    }

    public function fetch_markets($params = array ()) {
        $response = $this->publicGetApiMarketGetPriceList ($params);
        $result = array();
        $keys = is_array($response) ? array_keys($response) : array();
        for ($i = 0; $i < count($keys); $i++) {
            $currentMarketId = $keys[$i];
            $currentMarkets = $response[$currentMarketId];
            for ($j = 0; $j < count($currentMarkets); $j++) {
                $market = $currentMarkets[$j];
                $baseId = $this->safe_string($market, 'coin_from');
                $quoteId = $this->safe_string($market, 'coin_to');
                $base = strtoupper($baseId);
                $quote = strtoupper($quoteId);
                $base = $this->safe_currency_code($base);
                $quote = $this->safe_currency_code($quote);
                $id = $baseId . '2' . $quoteId;
                $symbol = $base . '/' . $quote;
                $active = true;
                $defaults = $this->safe_value($this->options['limits'], $symbol, array());
                $result[] = array_merge(array(
                    'id' => $id,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'active' => $active,
                    // overrided by $defaults from $this->options['limits']
                    'precision' => array(
                        'amount' => null,
                        'price' => null,
                    ),
                    // overrided by $defaults from $this->options['limits']
                    'limits' => array(
                        'amount' => array( 'min' => null, 'max' => null ),
                        'price' => array( 'min' => null, 'max' => null ),
                        'cost' => array( 'min' => null, 'max' => null ),
                    ),
                    'info' => $market,
                ), $defaults);
            }
        }
        return $result;
    }

    public function parse_trade($trade, $market = null) {
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_timestamp_2($trade, 'date', 'created');
        $id = $this->safe_string($trade, 'tid');
        $orderId = $this->safe_string($trade, 'order_id');
        $amount = $this->safe_float_2($trade, 'number', 'amount');
        $price = $this->safe_float($trade, 'price');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $amount * $price;
            }
        }
        $side = $this->safe_string($trade, 'side');
        if ($side === 'sale') {
            $side = 'sell';
        }
        return array(
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'order' => $orderId,
            'fee' => null,
            'takerOrMaker' => null,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'symbol' => $this->market_id($symbol),
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $market = $this->market($symbol);
        $response = $this->publicPostApiOrderMarketOrder (array_merge($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostApiUserUserBalance ($params);
        $data = $this->safe_value($response, 'data');
        $keys = is_array($data) ? array_keys($data) : array();
        $result = array( );
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $amount = $this->safe_float($data, $key);
            $parts = explode('_', $key);
            $currencyId = $parts[0];
            $lockOrOver = $parts[1];
            $code = $this->safe_currency_code($currencyId);
            if (!(is_array($result) && array_key_exists($code, $result))) {
                $result[$code] = $this->account();
            }
            if ($lockOrOver === 'lock') {
                $result[$code]['used'] = floatval($amount);
            } else {
                $result[$code]['free'] = floatval($amount);
            }
        }
        $keys = is_array($result) ? array_keys($result) : array();
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $total = $this->sum($result[$key]['used'], $result[$key]['free']);
            $result[$key]['total'] = $total;
        }
        $result['info'] = $data;
        return $this->parse_balance($result);
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->markets[$symbol];
        $request = array(
            'part' => $market['quoteId'],
            'coin' => $market['baseId'],
        );
        $response = $this->publicPostApiMarketGetCoinTrade (array_merge($request, $params));
        $timestamp = $this->milliseconds();
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_float($response, 'max'),
            'low' => $this->safe_float($response, 'min'),
            'bid' => $this->safe_float($response, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($response, 'sale'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $this->safe_float($response, 'price'),
            'last' => $this->safe_float($response, 'price'),
            'previousClose' => null,
            'change' => null,
            'percentage' => $this->safe_float($response, 'change_24h'),
            'average' => null,
            'baseVolume' => $this->safe_float($response, 'volume_24h'),
            'quoteVolume' => null,
            'info' => $response,
        );
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $marketId = $this->market_id($symbol);
        $request = array(
            'symbol' => $marketId,
        );
        $response = $this->publicPostApiOrderDepth (array_merge($request, $params));
        $data = $this->safe_value($response, 'data');
        $timestamp = $this->safe_timestamp($data, 'date');
        return $this->parse_order_book($data, $timestamp);
    }

    public function fetch_my_trades($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'symbol' => $market['id'],
        );
        $response = $this->privatePostApiOrderOrderList (array_merge($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function parse_order_status($status) {
        $statuses = array(
            '0' => 'open',
            '1' => 'open', // partially filled
            '2' => 'closed',
            '3' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrder requires a `$symbol` argument');
        }
        $this->load_markets();
        $request = array(
            'symbol' => $this->market_id($symbol),
            'trust_id' => $id,
        );
        $response = $this->privatePostApiOrderOrderInfo (array_merge($request, $params));
        $order = $this->safe_value($response, 'data');
        $timestamp = $this->safe_timestamp($order, 'created');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $side = $this->safe_string($order, 'flag');
        if ($side === 'sale') {
            $side = 'sell';
        }
        // Can't use parseOrder because the data format is different btw endpoint for fetchOrder and fetchOrders
        return array(
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $this->safe_float($order, 'price'),
            'cost' => null,
            'average' => $this->safe_float($order, 'avg_price'),
            'amount' => $this->safe_float($order, 'number'),
            'filled' => $this->safe_float($order, 'numberdeal'),
            'remaining' => $this->safe_float($order, 'numberover'),
            'status' => $status,
            'fee' => null,
            'clientOrderId' => null,
            'trades' => null,
        );
    }

    public function parse_order($order, $market = null) {
        $id = $this->safe_string($order, 'id');
        $timestamp = $this->safe_timestamp($order, 'datetime');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $type = null;
        $side = $this->safe_string($order, 'type');
        if ($side === 'sale') {
            $side = 'sell';
        }
        $price = $this->safe_float($order, 'price');
        $average = $this->safe_float($order, 'avg_price');
        $amount = $this->safe_float($order, 'amount');
        $remaining = $this->safe_float($order, 'amount_outstanding');
        $filled = $amount - $remaining;
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $cost = $filled * $price;
        $fee = null;
        $result = array(
            'info' => $order,
            'id' => $id,
            'clientOrderId' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
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
            'trades' => null,
        );
        return $result;
    }

    public function fetch_orders_by_type($type, $symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'type' => $type,
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['symbol'] = $market['id'];
        }
        $response = $this->privatePostApiOrderTradeList (array_merge($request, $params));
        if (is_array($response) && array_key_exists('data', $response)) {
            return $this->parse_orders($response['data'], $market, $since, $limit);
        }
        return array();
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type('open', $symbol, $since, $limit, $params);
    }

    public function fetch_closed_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, $since, $limit, $params);
        return $this->filter_by($orders, 'status', 'closed');
    }

    public function fetch_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type('all', $symbol, $since, $limit, $params);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'symbol' => $this->market_id($symbol),
            'type' => $side,
            'price' => $this->price_to_precision($symbol, $price),
            'number' => $this->amount_to_precision($symbol, $amount),
        );
        $response = $this->privatePostApiOrderCoinTrust (array_merge($request, $params));
        $data = $this->safe_value($response, 'data', array());
        $id = $this->safe_string($data, 'order_id');
        return array(
            'info' => $response,
            'id' => $id,
        );
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder requires a `$symbol` argument');
        }
        $this->load_markets();
        $request = array();
        if ($symbol !== null) {
            $request['symbol'] = $this->market_id($symbol);
        }
        if ($id !== null) {
            $request['order_id'] = $id;
        }
        return $this->privatePostApiOrderCancel (array_merge($request, $params));
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        } else {
            $this->check_required_credentials();
            $payload = $this->urlencode(array( 'api_key' => $this->apiKey ));
            if ($query) {
                $payload .= '&' . $this->urlencode($this->keysort($query));
            }
            $auth = $payload . '&secret_key=' . $this->secret;
            $signature = $this->hash($this->encode($auth));
            $body = $payload . '&sign=' . $signature;
            $headers = array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return; // fallback to default error handler
        }
        $errorCode = $this->safe_value($response, 'code');
        if ($errorCode !== null) {
            if ($errorCode !== 0) {
                //
                // array( $code => 1, msg => "该币不存在,非法操作" ) - returned when a required symbol parameter is missing in the request (also, maybe on other types of errors as well)
                // array( $code => 1, msg => '公钥不合法' ) - wrong public key
                // array( $code => 1, msg => '价格输入有误，请检查你的数值精度' ) - 'The price input is incorrect, please check your numerical accuracy'
                // array( $code => 1, msg => '单笔最小交易数量不能小于0.00100000,请您重新挂单') -
                //                  'The minimum number of single transactions cannot be less than 0.00100000. Please re-post the order'
                //
                $message = $this->safe_string($response, 'msg');
                $feedback = $this->id . ' ' . $message;
                $this->throw_exactly_matched_exception($this->exceptions, $message, $feedback);
                if (mb_strpos($message, '请您重新挂单') !== false) {  // minimum limit
                    throw new InvalidOrder($feedback);
                } else {
                    throw new ExchangeError($feedback);
                }
            }
        }
    }
}
