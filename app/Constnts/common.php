<?php

namespace App\Constnts;

class Common
{
    const PRODUCT_ADD = '1';
    const PRODUCT_REDUCE = '2';

    // 連想配列でも管理可能。
    const PRODUCT_LIST = [
        'add' => self::PRODUCT_ADD,
        'reduce' => self::PRODUCT_REDUCE,
    ];
}
