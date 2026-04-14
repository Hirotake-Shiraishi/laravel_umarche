<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

/**
 * 一般ユーザー向け「注文履歴」コントローラ
 *
 * - ログイン中のユーザーが、自分の注文一覧と詳細だけを閲覧するための画面を提供。
 */
class OrderController extends Controller
{
    public function __construct()
    {
        // ユーザー用ガードでログイン済みのみアクセス可能。
        $this->middleware('auth:users');
    }

    /**
     * 注文一覧
     *
     * - 自分の user_id に紐づく注文だけに絞り、
     *   created_at の新しい順に並べてページネーション。
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('user.orders.index', compact('orders'));
    }

    /**
     * 注文詳細
     *
     * 【ルートモデルバインディング Order $order】
     * - URL の {order} の数値 id から、自動的に Order モデルを取得。
     * - 存在しない id なら 404 エラー。
     *
     * 【load('orderItems.product')】
     * - 遅延 eager loading: 明細と各明細の商品をまとめて取得し、
     *   Blade の @foreach で都度 SQL が発行される N+1 問題を防ぐ。
     */
    public function show(Order $order)
    {
        // OrderPolicy::view を実行し、他人の注文なら 403 Forbidden を返す。
        $this->authorize('view', $order);

        $order->load(['orderItems.product']);

        return view('user.orders.show', compact('order'));
    }
}
