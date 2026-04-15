<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Models\Owner;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * OrderPolicy（注文に対する認可ルール）
 *
 * 【Policyとは】
 * 「このユーザーは、この注文を見てよいか」「更新していいか」をまとめて書くクラス。
 * コントローラ内に if 文が増えるのを防ぎ、ルールを1か所に集約させる。
 *
 * 【authorize() との関係】
 * コントローラで $this->authorize('view', $order) と書くと、
 * Laravel がこのクラスの view() メソッドを呼び、true なら許可、false なら拒否（403 Forbidden）。
 */
class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     * ユーザーが当該モデルを閲覧できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Order $order)
    {
        // 注文の user_id とログインユーザーの id が一致するときだけ自分の注文とみなす
        return (int) $user->id === (int) $order->user_id;
    }

    /**
     * オーナー（owners ガード）が注文のステータスを更新できるか
     *
     * カートは複数店舗の商品が混在し得るため、1注文に「他店の商品」が含まれることがある。
     * 明細がすべて自店の商品である注文だけ、ステータス更新を許可する。
     * 自店と他店の商品が混在する注文は、一覧には出すが更新は不可にする。
     * （誤って他店分までステータスを「発送済」に更新しないため）
     *
     * @param  \App\Models\Owner $owner ログイン中のオーナー
     * @param  \App\Models\Order $order 更新対象の注文
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateStatus(Owner $owner, Order $order)
    {
        // オーナーに店舗が無ければ操作不可
        $shop = $owner->shop;
        if ($shop === null) {
            return false;
        }

        $shopId = (int) $shop->id;

        $items = $order->orderItems()->get();

        if ($items->isEmpty()) {
            return false;
        }

        foreach ($items as $item) {

            $product = $item->product;

            if ($product === null) {
                return false;
            }

            if ((int) $product->shop_id !== $shopId) {
                return false;
            }
        }

        return true;
    }
}
