<?php

namespace App\Exceptions;

use Exception;

/**
 * 「決済は Stripe 上で成功したが、自社DBの在庫が足りない」という例外用クラス
 *
 * 【なぜ専用の Exception クラスを作るのか】
 * - DB::transaction の中で在庫不足がわかったとき、汎用の Exception だと、
 *  「他の種類のエラー」と区別しづらい。
 * - このクラスだけ catch すれば「返金処理に進む」など、意図がコード上はっきりする。
 *
 * - PHP ではよく使うエラー種別ごとに Exception を分ける。
 * 　または独自のメッセージ・コードで判別する。
 */
class CheckoutStockInsufficientException extends Exception
{
    //
}
