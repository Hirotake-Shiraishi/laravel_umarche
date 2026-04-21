<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;

/**
 * オーナー向け「自店舗商品のレビュー一覧」
 *
 * オーナーは「自分の店舗（shop）の商品」に付いたレビューだけを確認できるようにする。
 *
 * 【認可】
 * - ここでは Policy を増やさず、whereHas で自店商品のみに絞り込む。
 * - これにより、他店舗のレビューが一覧に出ることを防げる。
 */
class ReviewController extends Controller
{
    /**
     * 自店舗商品のレビュー一覧
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $owner = Auth::guard('owners')->user();
        $shopId = $owner && $owner->shop ? (int) $owner->shop->id : 0;

        // shopId が 0（店舗が無い）なら空一覧にする
        if ($shopId <= 0) {
            $reviews = Review::whereKey([])->paginate(10); // or Review::whereRaw('1=0')->paginate(10);

            return view('owner.reviews.index', compact('reviews'));
        }

        // 店舗がある場合は、その店舗の商品に付いたレビューを取得
        $reviews = Review::query()
            // ->whereHas('product', function ($q) use ($shopId) {
            //     $q->where('shop_id', $shopId);
            // })
            ->whereHas('product', fn ($q) => $q->where('shop_id', $shopId))
            ->with(['product', 'user'])
            ->latest()
            ->paginate(10);

        return view('owner.reviews.index', compact('reviews'));
    }
}
