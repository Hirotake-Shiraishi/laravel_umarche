<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
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
     * Determine whether the user can view any models.
     * ユーザーがすべてのモデルを一覧表示できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

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
     * Determine whether the user can create models.
     * ユーザーがモデルを新規作成できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     * ユーザーが当該モデルを更新できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Order $order)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     * ユーザーが当該モデルを削除できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Order $order)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     * ユーザーが当該モデルを復元できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Order $order)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     * ユーザーが当該モデルを完全に削除できるかどうかを判定する。
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Order $order)
    {
        //
    }
}
