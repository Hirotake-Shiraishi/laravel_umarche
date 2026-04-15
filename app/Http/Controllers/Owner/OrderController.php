<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * オーナー向け「受注一覧・ステータス更新」コントローラ
 *
 * 見せる注文の範囲は「自店の商品を含む注文」に限定。
 */
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');
    }

    /**
     * 受注一覧（自店の商品が1行以上含まれる注文を表示する）
     */
    public function index()
    {
        // 「owners ガード」のログインユーザーのモデル（Owner）を取得
        // ※ config\auth.php で、owners ガードは、Ownerモデルを使用するように設定してるので取得可。
        $owner = Auth::guard('owners')->user();

        // Ownerモデルに、shopメソッドがリレーションとして定義されている。
        // このオーナーが店舗を持っているかどうかの判定（三項演算子で記述）
        // - ? オーナーが紐づく店舗を持っていれば、その shop_id を取得。
        // - : 紐づく店舗が無い場合は shop_id を 0 にする。（whereHas で null だとエラーとなるため）
        $shopId = $owner->shop ? (int) $owner->shop->id : 0;

        // query() :クエリの開始（省略可能）
        // - Laravelでは、普段は省略されていることも多いが、可読性のために、
        //   クエリであることが一目でわかるように明示して書く場合もある。

        // whereHas('<リレーション>', <条件>)
        // - そのリレーションが存在して、さらに条件を満たすものだけ取得。

        // with('<リレーション>')
        // - eager loading: 関連データもまとめて取得。読み込みの最適化し、N+1問題を防ぐ。

        $orders = Order::query()
            ->whereHas('orderItems.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->with(['user', 'orderItems.product'])
            ->latest()
            ->paginate(10);

        return view('owner.orders.index', compact('orders', 'shopId'));
    }

    /**
     * 注文ステータス更新（PATCH）
     *
     * - 一覧などの画面からフォームで PATCH を送る（_method=PATCH や @method('PATCH')）
     *
     * 【ルートモデルバインディング Order $order】
     * - URL の {order} の数値 id から、自動的に Order モデルを取得。
     *
     * 【authorizeForUser】
     * - デフォルトの authorize() は「デフォルトガードのログインユーザー」。
     * - オーナー画面では owners ガードでのログインユーザーを明示的に渡す必要がある。
     *
     * 【validate】
     * - 許可した値以外の status が送られてきた場合は 422 エラーにし、不正更新を防ぐ。
     */
    public function updateStatus(Request $request, Order $order)
    {
        // authorizeForUser(ユーザー, ポリシーメソッド, モデル)
        // - OrderPolicy::updateStatus を実行。true なら許可、false なら拒否（403 Forbidden）。
        $this->authorizeForUser(Auth::guard('owners')->user(), 'updateStatus', $order);

        // validate() :バリデーションをかける
        $validated = $request->validate([
            'status' => 'required|in:' . \Constant::ORDER_STATUS_PENDING . ',' . \Constant::ORDER_STATUS_SHIPPED,
        ]);

        // update() :該当カラム（status）のみを更新してデータベースに保存。
        $order->update(['status' => $validated['status']]);

        return redirect()
            ->route('owner.orders.index')
            ->with(['message' => 'ステータスを更新しました。', 'status' => 'info']);
    }
}
