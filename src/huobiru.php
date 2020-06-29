<?php

namespace ccxt;

use Exception; // a common import

class huobiru extends huobipro {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'huobiru',
            'name' => 'Huobi Russia',
            'countries' => array( 'RU' ),
            'hostname' => 'www.huobi.com.ru',
            'pro' => true,
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/52978816-e8552e00-33e3-11e9-98ed-845acfece834.jpg',
                'api' => array(
                    'market' => 'https://{hostname}/api',
                    'public' => 'https://{hostname}/api',
                    'private' => 'https://{hostname}/api',
                ),
                'www' => 'https://www.huobi.com.ru/ru-ru',
                'referral' => 'https://www.huobi.com.ru/invite?invite_code=esc74',
                'doc' => 'https://github.com/cloudapidoc/API_Docs_en',
                'fees' => 'https://www.huobi.com.ru/ru-ru/about/fee/',
            ),
        ));
    }
}
