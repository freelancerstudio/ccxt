<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\ArgumentsRequired;

class indodax extends Exchange {

    public function describe () {
        return array_replace_recursive(parent::describe (), array(
            'id' => 'indodax',
            'name' => 'INDODAX',
            'countries' => array( 'ID' ), // Indonesia
            'has' => array(
                'CORS' => false,
                'createMarketOrder' => false,
                'fetchTickers' => false,
                'fetchOrder' => true,
                'fetchOrders' => false,
                'fetchClosedOrders' => true,
                'fetchOpenOrders' => true,
                'fetchMyTrades' => false,
                'fetchCurrencies' => false,
                'withdraw' => true,
            ),
            'version' => '1.8', // as of 9 April 2018
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/37443283-2fddd0e4-281c-11e8-9741-b4f1419001b5.jpg',
                'api' => array(
                    'public' => 'https://indodax.com/api',
                    'private' => 'https://indodax.com/tapi',
                ),
                'www' => 'https://www.indodax.com',
                'doc' => 'https://indodax.com/downloads/BITCOINCOID-API-DOCUMENTATION.pdf',
                'referral' => 'https://indodax.com/ref/testbitcoincoid/1',
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        '{pair}/ticker',
                        '{pair}/trades',
                        '{pair}/depth',
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'getInfo',
                        'transHistory',
                        'trade',
                        'tradeHistory',
                        'getOrder',
                        'openOrders',
                        'cancelOrder',
                        'orderHistory',
                        'withdrawCoin',
                    ),
                ),
            ),
            'markets' => array(
                // HARDCODING IS DEPRECATED
                // but they don't have a corresponding endpoint in their API
                // IDR markets
                'BTC/IDR' => array( 'id' => 'btc_idr', 'symbol' => 'BTC/IDR', 'base' => 'BTC', 'quote' => 'IDR', 'baseId' => 'btc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.0001, 'max' => null ))),
                'TEN/IDR' => array( 'id' => 'ten_idr', 'symbol' => 'TEN/IDR', 'base' => 'TEN', 'quote' => 'IDR', 'baseId' => 'ten', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'ABYSS/IDR' => array( 'id' => 'abyss_idr', 'symbol' => 'ABYSS/IDR', 'base' => 'ABYSS', 'quote' => 'IDR', 'baseId' => 'abyss', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'ACT/IDR' => array( 'id' => 'act_idr', 'symbol' => 'ACT/IDR', 'base' => 'ACT', 'quote' => 'IDR', 'baseId' => 'act', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'ADA/IDR' => array( 'id' => 'ada_idr', 'symbol' => 'ADA/IDR', 'base' => 'ADA', 'quote' => 'IDR', 'baseId' => 'ada', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'AOA/IDR' => array( 'id' => 'aoa_idr', 'symbol' => 'AOA/IDR', 'base' => 'AOA', 'quote' => 'IDR', 'baseId' => 'aoa', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'ATOM/IDR' => array( 'id' => 'atom_idr', 'symbol' => 'ATOM/IDR', 'base' => 'ATOM', 'quote' => 'IDR', 'baseId' => 'atom', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'BAT/IDR' => array( 'id' => 'bat_idr', 'symbol' => 'BAT/IDR', 'base' => 'BAT', 'quote' => 'IDR', 'baseId' => 'bat', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'BCD/IDR' => array( 'id' => 'bcd_idr', 'symbol' => 'BCD/IDR', 'base' => 'BCD', 'quote' => 'IDR', 'baseId' => 'bcd', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'BCH/IDR' => array( 'id' => 'bchabc_idr', 'symbol' => 'BCH/IDR', 'base' => 'BCH', 'quote' => 'IDR', 'baseId' => 'bchabc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.001, 'max' => null ))),
                'BSV/IDR' => array( 'id' => 'bchsv_idr', 'symbol' => 'BSV/IDR', 'base' => 'BSV', 'quote' => 'IDR', 'baseId' => 'bchsv', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.001, 'max' => null ))),
                'BNB/IDR' => array( 'id' => 'bnb_idr', 'symbol' => 'BNB/IDR', 'base' => 'BNB', 'quote' => 'IDR', 'baseId' => 'bnb', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.001, 'max' => null ))),
                'BTG/IDR' => array( 'id' => 'btg_idr', 'symbol' => 'BTG/IDR', 'base' => 'BTG', 'quote' => 'IDR', 'baseId' => 'btg', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'BTS/IDR' => array( 'id' => 'bts_idr', 'symbol' => 'BTS/IDR', 'base' => 'BTS', 'quote' => 'IDR', 'baseId' => 'bts', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'BTT/IDR' => array( 'id' => 'btt_idr', 'symbol' => 'BTT/IDR', 'base' => 'BTT', 'quote' => 'IDR', 'baseId' => 'btt', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1000, 'max' => null ))),
                'COAL/IDR' => array( 'id' => 'coal_idr', 'symbol' => 'COAL/IDR', 'base' => 'COAL', 'quote' => 'IDR', 'baseId' => 'coal', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 50, 'max' => null ))),
                'CRO/IDR' => array( 'id' => 'cro_idr', 'symbol' => 'CRO/IDR', 'base' => 'CRO', 'quote' => 'IDR', 'baseId' => 'cro', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'DASH/IDR' => array( 'id' => 'drk_idr', 'symbol' => 'DASH/IDR', 'base' => 'DASH', 'quote' => 'IDR', 'baseId' => 'drk', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'DAX/IDR' => array( 'id' => 'dax_idr', 'symbol' => 'DAX/IDR', 'base' => 'DAX', 'quote' => 'IDR', 'baseId' => 'dax', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'DOGE/IDR' => array( 'id' => 'doge_idr', 'symbol' => 'DOGE/IDR', 'base' => 'DOGE', 'quote' => 'IDR', 'baseId' => 'doge', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1000, 'max' => null ))),
                'ETH/IDR' => array( 'id' => 'eth_idr', 'symbol' => 'ETH/IDR', 'base' => 'ETH', 'quote' => 'IDR', 'baseId' => 'eth', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'EOS/IDR' => array( 'id' => 'eos_idr', 'symbol' => 'EOS/IDR', 'base' => 'EOS', 'quote' => 'IDR', 'baseId' => 'eos', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'ETC/IDR' => array( 'id' => 'etc_idr', 'symbol' => 'ETC/IDR', 'base' => 'ETC', 'quote' => 'IDR', 'baseId' => 'etc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'GARD/IDR' => array( 'id' => 'gard_idr', 'symbol' => 'GARD/IDR', 'base' => 'GARD', 'quote' => 'IDR', 'baseId' => 'gard', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'GSC/IDR' => array( 'id' => 'gsc_idr', 'symbol' => 'GSC/IDR', 'base' => 'GSC', 'quote' => 'IDR', 'baseId' => 'gsc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'GXC/IDR' => array( 'id' => 'gxs_idr', 'symbol' => 'GXC/IDR', 'base' => 'GXC', 'quote' => 'IDR', 'baseId' => 'gxs', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'HPB/IDR' => array( 'id' => 'hpb_idr', 'symbol' => 'HPB/IDR', 'base' => 'HPB', 'quote' => 'IDR', 'baseId' => 'hpb', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'IGNIS/IDR' => array( 'id' => 'ignis_idr', 'symbol' => 'IGNIS/IDR', 'base' => 'IGNIS', 'quote' => 'IDR', 'baseId' => 'ignis', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'INX/IDR' => array( 'id' => 'inx_idr', 'symbol' => 'INX/IDR', 'base' => 'INX', 'quote' => 'IDR', 'baseId' => 'inx', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'IOTA/IDR' => array( 'id' => 'iota_idr', 'symbol' => 'IOTA/IDR', 'base' => 'IOTA', 'quote' => 'IDR', 'baseId' => 'iota', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'LINK/IDR' => array( 'id' => 'link_idr', 'symbol' => 'LINK/IDR', 'base' => 'LINK', 'quote' => 'IDR', 'baseId' => 'link', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'LTC/IDR' => array( 'id' => 'ltc_idr', 'symbol' => 'LTC/IDR', 'base' => 'LTC', 'quote' => 'IDR', 'baseId' => 'ltc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'MBL/IDR' => array( 'id' => 'mbl_idr', 'symbol' => 'MBL/IDR', 'base' => 'MBL', 'quote' => 'IDR', 'baseId' => 'mbl', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'NEO/IDR' => array( 'id' => 'neo_idr', 'symbol' => 'NEO/IDR', 'base' => 'NEO', 'quote' => 'IDR', 'baseId' => 'neo', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'NPXS/IDR' => array( 'id' => 'npxs_idr', 'symbol' => 'NPXS/IDR', 'base' => 'NPXS', 'quote' => 'IDR', 'baseId' => 'npxs', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'NXT/IDR' => array( 'id' => 'nxt_idr', 'symbol' => 'NXT/IDR', 'base' => 'NXT', 'quote' => 'IDR', 'baseId' => 'nxt', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'OKB/IDR' => array( 'id' => 'okb_idr', 'symbol' => 'OKB/IDR', 'base' => 'OKB', 'quote' => 'IDR', 'baseId' => 'okb', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.5, 'max' => null ))),
                'ONT/IDR' => array( 'id' => 'ont_idr', 'symbol' => 'ONT/IDR', 'base' => 'ONT', 'quote' => 'IDR', 'baseId' => 'ont', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'PXG/IDR' => array( 'id' => 'pxg_idr', 'symbol' => 'PXG/IDR', 'base' => 'PXG', 'quote' => 'IDR', 'baseId' => 'pxg', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'QTUM/IDR' => array( 'id' => 'qtum_idr', 'symbol' => 'QTUM/IDR', 'base' => 'QTUM', 'quote' => 'IDR', 'baseId' => 'qtum', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'RVN/IDR' => array( 'id' => 'rvn_idr', 'symbol' => 'RVN/IDR', 'base' => 'RVN', 'quote' => 'IDR', 'baseId' => 'rvn', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'SSP/IDR' => array( 'id' => 'ssp_idr', 'symbol' => 'SSP/IDR', 'base' => 'SSP', 'quote' => 'IDR', 'baseId' => 'ssp', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                'SUMO/IDR' => array( 'id' => 'sumo_idr', 'symbol' => 'SUMO/IDR', 'base' => 'SUMO', 'quote' => 'IDR', 'baseId' => 'sumo', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 5, 'max' => null ))),
                // 'STQ/IDR' => array( 'id' => 'stq_idr', 'symbol' => 'STQ/IDR', 'base' => 'STQ', 'quote' => 'IDR', 'baseId' => 'stq', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'TRX/IDR' => array( 'id' => 'trx_idr', 'symbol' => 'TRX/IDR', 'base' => 'TRX', 'quote' => 'IDR', 'baseId' => 'trx', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'USDC/IDR' => array( 'id' => 'usdc_idr', 'symbol' => 'USDC/IDR', 'base' => 'USDC', 'quote' => 'IDR', 'baseId' => 'usdc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'USDT/IDR' => array( 'id' => 'usdt_idr', 'symbol' => 'USDT/IDR', 'base' => 'USDT', 'quote' => 'IDR', 'baseId' => 'usdt', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'VEX/IDR' => array( 'id' => 'vex_idr', 'symbol' => 'VEX/IDR', 'base' => 'VEX', 'quote' => 'IDR', 'baseId' => 'vex', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => null, 'max' => null ))),
                'VIDY/IDR' => array( 'id' => 'vidy_idr', 'symbol' => 'VIDY/IDR', 'base' => 'VIDY', 'quote' => 'IDR', 'baseId' => 'vidy', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 100, 'max' => null ))),
                'WAVES/IDR' => array( 'id' => 'waves_idr', 'symbol' => 'WAVES/IDR', 'base' => 'WAVES', 'quote' => 'IDR', 'baseId' => 'waves', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'XEM/IDR' => array( 'id' => 'nem_idr', 'symbol' => 'XEM/IDR', 'base' => 'XEM', 'quote' => 'IDR', 'baseId' => 'nem', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'XLM/IDR' => array( 'id' => 'str_idr', 'symbol' => 'XLM/IDR', 'base' => 'XLM', 'quote' => 'IDR', 'baseId' => 'str', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 20, 'max' => null ))),
                'XDCE/IDR' => array( 'id' => 'xdce_idr', 'symbol' => 'XDCE/IDR', 'base' => 'XDCE', 'quote' => 'IDR', 'baseId' => 'xdce', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => null ))),
                'XMR/IDR' => array( 'id' => 'xmr_idr', 'symbol' => 'XMR/IDR', 'base' => 'XMR', 'quote' => 'IDR', 'baseId' => 'xmr', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'XRP/IDR' => array( 'id' => 'xrp_idr', 'symbol' => 'XRP/IDR', 'base' => 'XRP', 'quote' => 'IDR', 'baseId' => 'xrp', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 10, 'max' => null ))),
                'XZC/IDR' => array( 'id' => 'xzc_idr', 'symbol' => 'XZC/IDR', 'base' => 'XZC', 'quote' => 'IDR', 'baseId' => 'xzc', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'VSYS/IDR' => array( 'id' => 'vsys_idr', 'symbol' => 'VSYS/IDR', 'base' => 'VSYS', 'quote' => 'IDR', 'baseId' => 'vsys', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.1, 'max' => null ))),
                'ZEC/IDR' => array( 'id' => 'zec_idr', 'symbol' => 'ZEC/IDR', 'base' => 'ZEC', 'quote' => 'IDR', 'baseId' => 'zec', 'quoteId' => 'idr', 'precision' => array( 'amount' => 8, 'price' => 0 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                // BTC markets
                'BTS/BTC' => array( 'id' => 'bts_btc', 'symbol' => 'BTS/BTC', 'base' => 'BTS', 'quote' => 'BTC', 'baseId' => 'bts', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'DASH/BTC' => array( 'id' => 'drk_btc', 'symbol' => 'DASH/BTC', 'base' => 'DASH', 'quote' => 'BTC', 'baseId' => 'drk', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'DOGE/BTC' => array( 'id' => 'doge_btc', 'symbol' => 'DOGE/BTC', 'base' => 'DOGE', 'quote' => 'BTC', 'baseId' => 'doge', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'ETH/BTC' => array( 'id' => 'eth_btc', 'symbol' => 'ETH/BTC', 'base' => 'ETH', 'quote' => 'BTC', 'baseId' => 'eth', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 5 ), 'limits' => array( 'amount' => array( 'min' => 0.001, 'max' => null ))),
                'LTC/BTC' => array( 'id' => 'ltc_btc', 'symbol' => 'LTC/BTC', 'base' => 'LTC', 'quote' => 'BTC', 'baseId' => 'ltc', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 6 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'NXT/BTC' => array( 'id' => 'nxt_btc', 'symbol' => 'NXT/BTC', 'base' => 'NXT', 'quote' => 'BTC', 'baseId' => 'nxt', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'SUMO/BTC' => array( 'id' => 'sumo_btc', 'symbol' => 'SUMO/BTC', 'base' => 'SUMO', 'quote' => 'BTC', 'baseId' => 'sumo', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'TEN/BTC' => array( 'id' => 'ten_btc', 'symbol' => 'TEN/BTC', 'base' => 'TEN', 'quote' => 'BTC', 'baseId' => 'ten', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'XEM/BTC' => array( 'id' => 'nem_btc', 'symbol' => 'XEM/BTC', 'base' => 'XEM', 'quote' => 'BTC', 'baseId' => 'nem', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 1, 'max' => null ))),
                'XLM/BTC' => array( 'id' => 'str_btc', 'symbol' => 'XLM/BTC', 'base' => 'XLM', 'quote' => 'BTC', 'baseId' => 'str', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
                'XRP/BTC' => array( 'id' => 'xrp_btc', 'symbol' => 'XRP/BTC', 'base' => 'XRP', 'quote' => 'BTC', 'baseId' => 'xrp', 'quoteId' => 'btc', 'precision' => array( 'amount' => 8, 'price' => 8 ), 'limits' => array( 'amount' => array( 'min' => 0.01, 'max' => null ))),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0,
                    'taker' => 0.003,
                ),
            ),
            'exceptions' => array(
                'exact' => array(
                    'invalid_pair' => '\\ccxt\\BadSymbol', // array("error":"invalid_pair","error_description":"Invalid Pair")
                    'Insufficient balance.' => '\\ccxt\\InsufficientFunds',
                    'invalid order.' => '\\ccxt\\OrderNotFound',
                    'Invalid credentials. API not found or session has expired.' => '\\ccxt\\AuthenticationError',
                    'Invalid credentials. Bad sign.' => '\\ccxt\\AuthenticationError',
                ),
                'broad' => array(
                    'Minimum price' => '\\ccxt\\InvalidOrder',
                    'Minimum order' => '\\ccxt\\InvalidOrder',
                ),
            ),
        ));
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ($params);
        $balances = $this->safe_value($response, 'return', array());
        $free = $this->safe_value($balances, 'balance', array());
        $used = $this->safe_value($balances, 'balance_hold', array());
        $result = array( 'info' => $response );
        $currencyIds = is_array($free) ? array_keys($free) : array();
        for ($i = 0; $i < count($currencyIds); $i++) {
            $currencyId = $currencyIds[$i];
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account ();
            $account['free'] = $this->safe_float($free, $currencyId);
            $account['used'] = $this->safe_float($used, $currencyId);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'pair' => $this->market_id($symbol),
        );
        $orderbook = $this->publicGetPairDepth (array_merge($request, $params));
        return $this->parse_order_book($orderbook, null, 'buy', 'sell');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        $response = $this->publicGetPairTicker (array_merge($request, $params));
        //
        //     {
        //         "$ticker" => {
        //             "high":"0.01951",
        //             "low":"0.01877",
        //             "vol_eth":"39.38839319",
        //             "vol_btc":"0.75320886",
        //             "$last":"0.01896",
        //             "buy":"0.01896",
        //             "sell":"0.019",
        //             "server_time":1565248908
        //         }
        //     }
        //
        $ticker = $response['ticker'];
        $timestamp = $this->safe_timestamp($ticker, 'server_time');
        $baseVolume = 'vol_' . strtolower($market['baseId']);
        $quoteVolume = 'vol_' . strtolower($market['quoteId']);
        $last = $this->safe_float($ticker, 'last');
        return array(
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
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, $baseVolume),
            'quoteVolume' => $this->safe_float($ticker, $quoteVolume),
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_timestamp($trade, 'date');
        $id = $this->safe_string($trade, 'tid');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $type = null;
        $side = $this->safe_string($trade, 'type');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        return array(
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'order' => null,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        $response = $this->publicGetPairTrades (array_merge($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_order ($order, $market = null) {
        $side = null;
        if (is_array($order) && array_key_exists('type', $order)) {
            $side = $order['type'];
        }
        $status = $this->safe_string($order, 'status', 'open');
        if ($status === 'filled') {
            $status = 'closed';
        } else if ($status === 'cancelled') {
            $status = 'canceled';
        }
        $symbol = null;
        $cost = null;
        $price = $this->safe_float($order, 'price');
        $amount = null;
        $remaining = null;
        $filled = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $quoteId = $market['quoteId'];
            $baseId = $market['baseId'];
            if (($market['quoteId'] === 'idr') && (is_array($order) && array_key_exists('order_rp', $order))) {
                $quoteId = 'rp';
            }
            if (($market['baseId'] === 'idr') && (is_array($order) && array_key_exists('remain_rp', $order))) {
                $baseId = 'rp';
            }
            $cost = $this->safe_float($order, 'order_' . $quoteId);
            if ($cost) {
                $amount = $cost / $price;
                $remainingCost = $this->safe_float($order, 'remain_' . $quoteId);
                if ($remainingCost !== null) {
                    $remaining = $remainingCost / $price;
                    $filled = $amount - $remaining;
                }
            } else {
                $amount = $this->safe_float($order, 'order_' . $baseId);
                $cost = $price * $amount;
                $remaining = $this->safe_float($order, 'remain_' . $baseId);
                $filled = $amount - $remaining;
            }
        }
        $average = null;
        if ($filled) {
            $average = $cost / $filled;
        }
        $timestamp = $this->safe_integer($order, 'submit_time');
        $fee = null;
        $id = $this->safe_string($order, 'order_id');
        return array(
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'average' => $average,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ExchangeError($this->id . ' fetchOrder requires a symbol');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'pair' => $market['id'],
            'order_id' => $id,
        );
        $response = $this->privatePostGetOrder (array_merge($request, $params));
        $orders = $response['return'];
        $order = $this->parse_order(array_merge(array( 'id' => $id ), $orders['order']), $market);
        return array_merge(array( 'info' => $response ), $order);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array();
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
        }
        $response = $this->privatePostOpenOrders (array_merge($request, $params));
        $rawOrders = $response['return']['orders'];
        // array( success => 1, return => array( orders => null )) if no orders
        if (!$rawOrders) {
            return array();
        }
        // array( success => 1, return => array( orders => array( ... objects ) )) for orders fetched by $symbol
        if ($symbol !== null) {
            return $this->parse_orders($rawOrders, $market, $since, $limit);
        }
        // array( success => 1, return => array( orders => array( marketid => array( ... objects ) ))) if all orders are fetched
        $marketIds = is_array($rawOrders) ? array_keys($rawOrders) : array();
        $exchangeOrders = array();
        for ($i = 0; $i < count($marketIds); $i++) {
            $marketId = $marketIds[$i];
            $marketOrders = $rawOrders[$marketId];
            $market = $this->markets_by_id[$marketId];
            $parsedOrders = $this->parse_orders($marketOrders, $market, $since, $limit);
            $exchangeOrders = $this->array_concat($exchangeOrders, $parsedOrders);
        }
        return $exchangeOrders;
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ExchangeError($this->id . ' fetchOrders requires a symbol');
        }
        $this->load_markets();
        $request = array();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
        }
        $response = $this->privatePostOrderHistory (array_merge($request, $params));
        $orders = $this->parse_orders($response['return']['orders'], $market, $since, $limit);
        $orders = $this->filter_by($orders, 'status', 'closed');
        if ($symbol !== null) {
            return $this->filter_by_symbol($orders, $symbol);
        }
        return $orders;
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type !== 'limit') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'pair' => $market['id'],
            'type' => $side,
            'price' => $price,
        );
        $currency = $market['baseId'];
        if ($side === 'buy') {
            $request[$market['quoteId']] = $amount * $price;
        } else {
            $request[$market['baseId']] = $amount;
        }
        $request[$currency] = $amount;
        $result = $this->privatePostTrade (array_merge($request, $params));
        return array(
            'info' => $result,
            'id' => (string) $result['return']['order_id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder requires a $symbol argument');
        }
        $side = $this->safe_value($params, 'side');
        if ($side === null) {
            throw new ExchangeError($this->id . ' cancelOrder requires an extra "$side" param');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array(
            'order_id' => $id,
            'pair' => $market['id'],
            'type' => $side,
        );
        return $this->privatePostCancelOrder (array_merge($request, $params));
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        // Custom string you need to provide to identify each withdrawal.
        // Will be passed to callback URL (assigned via website to the API key)
        // so your system can identify the $request and confirm it.
        // Alphanumeric, max length 255.
        $requestId = $this->milliseconds ();
        // Alternatively:
        // $requestId = $this->uuid ();
        $request = array(
            'currency' => $currency['id'],
            'withdraw_amount' => $amount,
            'withdraw_address' => $address,
            'request_id' => (string) $requestId,
        );
        if ($tag) {
            $request['withdraw_memo'] = $tag;
        }
        $response = $this->privatePostWithdrawCoin (array_merge($request, $params));
        //
        //     {
        //         "success" => 1,
        //         "status" => "approved",
        //         "withdraw_currency" => "xrp",
        //         "withdraw_address" => "rwWr7KUZ3ZFwzgaDGjKBysADByzxvohQ3C",
        //         "withdraw_amount" => "10000.00000000",
        //         "fee" => "2.00000000",
        //         "amount_after_fee" => "9998.00000000",
        //         "submit_time" => "1509469200",
        //         "withdraw_id" => "xrp-12345",
        //         "txid" => "",
        //         "withdraw_memo" => "123123"
        //     }
        //
        $id = null;
        if ((is_array($response) && array_key_exists('txid', $response)) && (strlen($response['txid']) > 0)) {
            $id = $response['txid'];
        }
        return array(
            'info' => $response,
            'id' => $id,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api];
        if ($api === 'public') {
            $url .= '/' . $this->implode_params($path, $params);
        } else {
            $this->check_required_credentials();
            $body = $this->urlencode (array_merge(array(
                'method' => $path,
                'nonce' => $this->nonce (),
            ), $params));
            $headers = array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Key' => $this->apiKey,
                'Sign' => $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512'),
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        // array( success => 0, $error => "invalid order." )
        // or
        // [array( data, ... ), array( ... ), ... ]
        if (gettype($response) === 'array' && count(array_filter(array_keys($response), 'is_string')) == 0) {
            return; // public endpoints may return array()-arrays
        }
        $error = $this->safe_value($response, 'error', '');
        if (!(is_array($response) && array_key_exists('success', $response)) && $error === '') {
            return; // no 'success' property on public responses
        }
        if ($this->safe_integer($response, 'success', 0) === 1) {
            // array( success => 1, return => array( orders => array() ))
            if (!(is_array($response) && array_key_exists('return', $response))) {
                throw new ExchangeError($this->id . ' => malformed $response => ' . $this->json ($response));
            } else {
                return;
            }
        }
        $feedback = $this->id . ' ' . $body;
        $this->throw_exactly_matched_exception($this->exceptions['exact'], $error, $feedback);
        $this->throw_broadly_matched_exception($this->exceptions['broad'], $error, $feedback);
        throw new ExchangeError($feedback); // unknown message
    }
}
