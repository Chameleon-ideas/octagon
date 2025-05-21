<?php

namespace App\Helpers;

class Helper {

    public static function checkServer() {
        if (strpos(url()->current(), '127.0.0.1') !== false) {
            //local
            return [
                'BASE_URL' => 'http://127.0.0.1:8000'
            ];
        } else if (strpos(url()->current(), '34.209.113.224') !== false) {
            // staging
            return [
                'BASE_URL' => 'http://3.134.119.154'
            ];
        } else {
            //producation
            return [
                'BASE_URL' => 'http://3.134.119.154'
            ];
        }
    }

}
