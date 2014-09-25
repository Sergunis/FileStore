<?php
/**
 * Created by PhpStorm.
 * User: Hett
 * Date: 18.09.2014
 * Time: 16:17
 */

namespace components;


class Helper {

    public static function uniqid()
    {
        $rnd_dev=mcrypt_create_iv(6, MCRYPT_DEV_URANDOM); //need "apt-get install php5-mcrypt"
        $ord = [
            str_pad(dechex(ord(substr($rnd_dev, 0, 1))), 2, '0', STR_PAD_LEFT),
            str_pad(dechex(ord(substr($rnd_dev, 1, 1))), 2, '0', STR_PAD_LEFT),
            str_pad(dechex(ord(substr($rnd_dev, 2, 1))), 2, '0', STR_PAD_LEFT),
            str_pad(dechex(ord(substr($rnd_dev, 3, 1))), 2, '0', STR_PAD_LEFT),
            str_pad(dechex(ord(substr($rnd_dev, 4, 1))), 2, '0', STR_PAD_LEFT),
            str_pad(dechex(ord(substr($rnd_dev, 5, 1))), 2, '0', STR_PAD_LEFT),
        ];
        return dechex(rand(0, 15)) . implode('', $ord);
    }

} 