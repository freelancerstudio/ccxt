<?php

namespace ccxt;

use Exception; // a common import

class fcoinjp extends fcoin {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'fcoinjp',
            'name' => 'FCoinJP',
            'countries' => array( 'JP' ),
            'hostname' => 'fcoinjp.com',
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/54219174-08b66b00-4500-11e9-862d-f522d0fe08c6.jpg',
                'fees' => 'https://fcoinjp.zendesk.com/hc/en-us/articles/360018727371',
                'www' => 'https://www.fcoinjp.com',
                'referral' => null,
            ),
        ));
    }
}
