<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitibu extends acx {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitibu',
            'name' => 'Bitibu',
            'countries' => array ( 'CY' ),
            'has' => array (
                'CORS' => false,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/45444675-c9ce6680-b6d0-11e8-95ab-3e749a940de1.jpg',
                'extension' => '.json',
                'api' => 'https://bitibu.com',
                'www' => 'https://bitibu.com',
                'doc' => 'https://bitibu.com/documents/api_v2',
            ),
        ));
    }
}
