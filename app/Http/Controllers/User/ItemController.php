<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PrimaryCategory;
use App\Models\User;
use App\Models\Review;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users');

        // クロージャを使ったコントローラミドルウェア
        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('item'); //ルートパラメータのid {item}

            if (!is_null($id)) {

                // 表示可能な商品（在庫が1以上などの条件を満たす）の中で、指定の商品IDが存在するかどうか
                $isAvailable = Product::availableItems()
                    ->where('products.id', $id)
                    ->exists();

                $productId = (int) $id;

                if(!$isAvailable) {
                    /** @var User|null $user ログイン中の一般ユーザー（users ガード） */
                    $user = Auth::guard('users')->user();
                    $hasPurchased = $user ? $user->hasPurchasedProduct($productId) : false;

                    if(!$hasPurchased) {
                        abort(404);
                    }
                }
            }
            return $next($request);
        });
    }

    // ビューからのリクエストを受け取るために、Request $requestを引数に追加
    public function index(Request $request)
    {
        // dd($request);

        // 同期的にメールを送信する場合
        // Mail::to('test@example.com') //受信者の指定
        //     ->send(new TestMail()); //Mailableクラス

        // 非同期でメールを送信する場合
        // SendThanksMail::dispatch();

        $categories = PrimaryCategory::with('secondary')
            ->get();

        // ローカルスコープに、クエリを定義
        $products = Product::availableItems() // 表示可能な商品
            ->selectCategory($request->category ?? '0') // カテゴリー
            ->searchKeyword($request->keyword) // キーワード
            ->sortOrder($request->sort) // 並び順
            ->paginate($request->pagination ?? '20'); // ページネーション

        return view('user.index', compact('products', 'categories'));
    }

    public function show($id)
    {
        /**
         * 商品詳細で必要な情報
         * - 商品情報（画像/カテゴリ/店舗など）
         * - レビュー一覧（投稿者名も表示するので user を eager load）
         * - 平均評価（scopeWithAvgRating で SQL 側で計算）
         *
         * 【N+1対策】
         * reviews.user をまとめて読み込むことで、レビュー件数分の追加SQLを避ける。
         */
        $productId = (int) $id;

        $product = Product::query()
            ->withAvgRating()
            ->with([
                'secondaryCategory',
                'shop',
                'imageFirst',
                'imageSecond',
                'imageThird',
                'imageFourth',
                'reviews' => function ($query) {
                    $query->latest();
                },
                'reviews.user',
            ])
            ->findOrFail($productId);

        // 「購入者のみレビュー可能」判定と、すでに投稿済みかどうか
        /** @var User|null $user ログイン中の一般ユーザー（users ガード） */
        $user = Auth::guard('users')->user();
        $hasPurchased = $user ? $user->hasPurchasedProduct($productId) : false;
        $userReview = null;

        if ($user) {
            $userReview = Review::query()
                ->where('product_id', $productId)
                ->where('user_id', (int) $user->id)
                ->first();
        }

        // 「レビュー可能」判定：「購入済み」かつ「まだレビューしてない」
        $canReview = $hasPurchased && $userReview === null;

        $quantity = Stock::where('product_id', $product->id)
            ->sum('quantity');

        // 在庫が10以上あれば、数量は9まで表示する
        if($quantity > 9){
            $quantity = 9;
        }

        return view('user.show', compact('product', 'hasPurchased', 'userReview', 'canReview', 'quantity'));
    }
}
