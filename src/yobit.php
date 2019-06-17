<?php

namespace ccxt;

use Exception as Exception; // a common import

class yobit extends liqui {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'yobit',
            'name' => 'YoBit',
            'countries' => array ( 'RU' ),
            'rateLimit' => 3000, // responses are cached every 2 seconds
            'version' => '3',
            'has' => array (
                'createDepositAddress' => true,
                'fetchDepositAddress' => true,
                'fetchDeposits' => false,
                'fetchWithdrawals' => false,
                'fetchTransactions' => false,
                'fetchTickers' => false,
                'CORS' => false,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766910-cdcbfdae-5eea-11e7-9859-03fea873272d.jpg',
                'api' => array (
                    'public' => 'https://yobit.net/api',
                    'private' => 'https://yobit.net/tapi',
                ),
                'www' => 'https://www.yobit.net',
                'doc' => 'https://www.yobit.net/en/api/',
                'fees' => 'https://www.yobit.net/en/fees/',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'depth/{pair}',
                        'info',
                        'ticker/{pair}',
                        'trades/{pair}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'ActiveOrders',
                        'CancelOrder',
                        'GetDepositAddress',
                        'getInfo',
                        'OrderInfo',
                        'Trade',
                        'TradeHistory',
                        'WithdrawCoinsToAddress',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.002,
                    'taker' => 0.002,
                ),
                'funding' => array (
                    'withdraw' => array(),
                ),
            ),
            'commonCurrencies' => array (
                'AIR' => 'AirCoin',
                'ANI' => 'ANICoin',
                'ANT' => 'AntsCoin',  // what is this, a coin for ants?
                'ATMCHA' => 'ATM',
                'ASN' => 'Ascension',
                'AST' => 'Astral',
                'ATM' => 'Autumncoin',
                'BCC' => 'BCH',
                'BCS' => 'BitcoinStake',
                'BLN' => 'Bulleon',
                'BOT' => 'BOTcoin',
                'BON' => 'BONES',
                'BPC' => 'BitcoinPremium',
                'BTS' => 'Bitshares2',
                'CAT' => 'BitClave',
                'CMT' => 'CometCoin',
                'COV' => 'Coven Coin',
                'COVX' => 'COV',
                'CPC' => 'Capricoin',
                'CS' => 'CryptoSpots',
                'DCT' => 'Discount',
                'DGD' => 'DarkGoldCoin',
                'DIRT' => 'DIRTY',
                'DROP' => 'FaucetCoin',
                'EKO' => 'EkoCoin',
                'ENTER' => 'ENTRC',
                'EPC' => 'ExperienceCoin',
                'ERT' => 'Eristica Token',
                'ESC' => 'EdwardSnowden',
                'EUROPE' => 'EUROP',
                'EXT' => 'LifeExtension',
                'FUNK' => 'FUNKCoin',
                'GCC' => 'GlobalCryptocurrency',
                'GEN' => 'Genstake',
                'GENE' => 'Genesiscoin',
                'GOLD' => 'GoldMint',
                'GOT' => 'Giotto Coin',
                'HTML5' => 'HTML',
                'HYPERX' => 'HYPER',
                'ICN' => 'iCoin',
                'INSANE' => 'INSN',
                'JNT' => 'JointCoin',
                'JPC' => 'JupiterCoin',
                'KNC' => 'KingN Coin',
                'LBTCX' => 'LiteBitcoin',
                'LIZI' => 'LiZi',
                'LOC' => 'LocoCoin',
                'LOCX' => 'LOC',
                'LUNYR' => 'LUN',
                'LUN' => 'LunarCoin',  // they just change the ticker if it is already taken
                'MDT' => 'Midnight',
                'NAV' => 'NavajoCoin',
                'NBT' => 'NiceBytes',
                'OMG' => 'OMGame',
                'PAC' => '$PAC',
                'PLAY' => 'PlayCoin',
                'PIVX' => 'Darknet',
                'PRS' => 'PRE',
                'PUTIN' => 'PUT',
                'STK' => 'StakeCoin',
                'SUB' => 'Subscriptio',
                'PAY' => 'EPAY',
                'PLC' => 'Platin Coin',
                'RCN' => 'RCoin',
                'REP' => 'Republicoin',
                'RUR' => 'RUB',
                'XIN' => 'XINCoin',
            ),
            'options' => array (
                'fetchOrdersRequiresSymbol' => true,
                'fetchTickersMaxLength' => 512,
            ),
            'exceptions' => array (
                'broad' => array (
                    'Total transaction amount' => '\\ccxt\\ExchangeError', // array( "success" => 0, "error" => "Total transaction amount is less than minimal total => 0.00010000")
                    'Insufficient funds' => '\\ccxt\\InsufficientFunds',
                    'invalid key' => '\\ccxt\\AuthenticationError',
                    'invalid nonce' => '\\ccxt\\InvalidNonce', // array("success":0,"error":"invalid nonce (has already been used)")'
                ),
            ),
        ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '0' => 'open',
            '1' => 'closed',
            '2' => 'canceled',
            '3' => 'open', // or partially-filled and closed? https://github.com/ccxt/ccxt/issues/1594
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ($params);
        $balances = $response['return'];
        $result = array( 'info' => $balances );
        $sides = array( 'free' => 'funds', 'total' => 'funds_incl_orders' );
        $keys = is_array($sides) ? array_keys($sides) : array();
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            $side = $sides[$key];
            if (is_array($balances) && array_key_exists($side, $balances)) {
                $currencies = is_array($balances[$side]) ? array_keys($balances[$side]) : array();
                for ($j = 0; $j < count ($currencies); $j++) {
                    $lowercase = $currencies[$j];
                    $uppercase = strtoupper($lowercase);
                    $currency = $this->common_currency_code($uppercase);
                    $account = null;
                    if (is_array($result) && array_key_exists($currency, $result)) {
                        $account = $result[$currency];
                    } else {
                        $account = $this->account ();
                    }
                    $account[$key] = $balances[$side][$lowercase];
                    if (($account['total'] !== null) && ($account['free'] !== null)) {
                        $account['used'] = $account['total'] - $account['free'];
                    }
                    $result[$currency] = $account;
                }
            }
        }
        return $this->parse_balance($result);
    }

    public function create_deposit_address ($code, $params = array ()) {
        $request = array (
            'need_new' => 1,
        );
        $response = $this->fetch_deposit_address ($code, array_merge ($request, $params));
        $address = $this->safe_string($response, 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $response['info'],
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'coinName' => $currency['id'],
            'need_new' => 0,
        );
        $response = $this->privatePostGetDepositAddress (array_merge ($request, $params));
        $address = $this->safe_string($response['return'], 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $response,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        // some derived classes use camelcase notation for $request fields
        $request = array (
            // 'from' => 123456789, // $trade ID, from which the display starts numerical 0 (test $result => liqui ignores this field)
            // 'count' => 1000, // the number of $trades for display numerical, default = 1000
            // 'from_id' => $trade ID, from which the display starts numerical 0
            // 'end_id' => $trade ID on which the display ends numerical ∞
            // 'order' => 'ASC', // sorting, default = DESC (test $result => liqui ignores this field, most recent $trade always goes last)
            // 'since' => 1234567890, // UTC start time, default = 0 (test $result => liqui ignores this field)
            // 'end' => 1234567890, // UTC end time, default = ∞ (test $result => liqui ignores this field)
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
        $trades = $this->safe_value($response, 'return', array());
        $ids = is_array($trades) ? array_keys($trades) : array();
        $result = array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $trade = $this->parse_trade(array_merge ($trades[$id], array (
                'trade_id' => $id,
            )), $market);
            $result[] = $trade;
        }
        return $this->filter_by_symbol_since_limit($result, $symbol, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'coinName' => $currency['id'],
            'amount' => $amount,
            'address' => $address,
        );
        $response = $this->privatePostWithdrawCoinsToAddress (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => null,
        );
    }
}
