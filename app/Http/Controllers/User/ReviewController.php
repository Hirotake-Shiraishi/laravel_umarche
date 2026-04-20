<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Http\Requests\ReviewStoreRequest;

/**
 * 一般ユーザー向け「レビュー投稿」コントローラ
 *
 * 【このコントローラの目的】
 * - 購入者だけがレビュー投稿できるようにする（認可）
 * - 1商品につき1ユーザー1回までを守る（バリデーション + DBユニーク）
 */
class ReviewController extends Controller
{
    /**
     * レビュー投稿（POST）
     *
     * ルート例: POST show/{item}/reviews
     *
     * @param  ReviewStoreRequest  $request
     * @param  mixed  $item  ルートパラメータ {item}（商品ID）
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ReviewStoreRequest $request, $item)
    {
        $productId = (int) $item;

        // まず商品が存在するか（存在しないIDなら 404）
        $product = Product::findOrFail($productId);

        /** @var User|null $user ログイン中の一般ユーザー（users ガード） */
        $user = Auth::guard('users')->user();

        if ($user === null) {
            // ルート側で auth:users を掛ける想定だが、念のため。
            abort(403);
        }

        /**
         * 「詳細表示が許される商品か」の判定（ItemController と同じ方針）
         *
         * - 掲載中: availableItems() に含まれる
         * - または購入済み: user がこの商品を購入したことがある
         *
         * どちらでもない場合は 404（購入者限定レビューの前提を満たさない）
         */
        $isAvailable = Product::availableItems()
            ->where('products.id', $productId)
            ->exists();

        $hasPurchased = $user->hasPurchasedProduct($productId);

        if (!$isAvailable && !$hasPurchased) {
            abort(404);
        }

        // 購入者限定
        if (!$hasPurchased) {
            return back()
                ->withErrors(['review' => 'レビューは購入済みの方のみ投稿できます。'])
                ->withInput();
        }

        // すでに投稿済みか（事前に弾く。最終的には DB の unique(product_id,user_id) でも防げる）
        $already = Review::query()
            ->where('product_id', $productId)
            ->where('user_id', (int) $user->id)
            ->exists();

        if ($already) {
            return back()
                ->withErrors(['review' => 'この商品へのレビューは既に投稿済みです。'])
                ->withInput();
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => (int) $user->id,
            'rating' => (int) $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return redirect()
            ->route('user.items.show', ['item' => $product->id])
            ->with(['message' => 'レビューを投稿しました。', 'status' => 'info']);
    }
}
