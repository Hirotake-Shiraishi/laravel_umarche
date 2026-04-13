<?php

namespace App\Constants;

class Common
{
    const PRODUCT_ADD = '1';
    const PRODUCT_REDUCE = '2';

    // 連想配列でも管理可能。
    const PRODUCT_LIST = [
        'add' => self::PRODUCT_ADD,
        'reduce' => self::PRODUCT_REDUCE,
    ];

    const ORDER_RECOMMEND = '0';
    const ORDER_HIGHER = '1';
    const ORDER_LOWER = '2';
    const ORDER_LATER = '3';
    const ORDER_OLDER = '4';

    const SORT_ORDER = [
        'recommend' => self::ORDER_RECOMMEND,
        'higherPrice' => self::ORDER_HIGHER,
        'lowerPrice' => self::ORDER_LOWER,
        'later' => self::ORDER_LATER,
        'older' => self::ORDER_OLDER
    ];

    /** 注文ステータス（DB に保存する値。画面表示は Blade 側で日本語にマッピング） */
    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_SHIPPED = 'shipped';

    const ORDER_STATUS_LIST = [
        'pending' => self::ORDER_STATUS_PENDING,
        'shipped' => self::ORDER_STATUS_SHIPPED,
    ];
}
