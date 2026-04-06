<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Stock;
use App\Services\CartService;
use App\Jobs\SendThanksMail;
use App\Jobs\SendOrderedMail;
use App\Http\Requests\CartAddRequest; //add()のバリデーション用

class CartController extends Controller
{
    public function index()
    {
        $user = User::findOrfail(Auth::id());

        $products = $user->products;

        $totalPrice = 0;

        foreach($products as $product){
            // 値段 * 数量
            $totalPrice += $product->price * $product->pivot->quantity;
        }

        // dd($products, $totalPrice);

        return view('user.cart', compact('products', 'totalPrice'));
    }


    /**
     * カートに商品を追加
     * 【指摘#5 修正】quantity・product_id にバリデーションがなく、負の数・存在しないIDで孤立レコードが作れるようになってしまっていた。
     * CartAddRequestを作成し、CartAddRequest で quantity: integer|min:1|max:99, product_id: exists:products,id のバリデーションを実施。
     */
    public function add(CartAddRequest $request)
    {
        // dd($request);

        // カートに商品があるか
        // ユーザーIDが、ログインIDと同じか
        $itemInCart = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)->first();

        if($itemInCart){
            // あれば数量を追加
            $itemInCart->quantity += $request->quantity;
            $itemInCart->save();
        } else {
            // なければ新規作成
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        // dd('テスト');
        return redirect()->route('user.cart.index');
    }


    public function delete($id)
    {
        Cart::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('user.cart.index');
    }


    /**
     * チェックアウト
     * 【指摘#2】line_items: 二重配列 [$lineItems] → $lineItems。price_data + product_data 形式（Stripe v7 以降）。
     * 【指摘#3】Stripe 失敗時は在庫を戻す（課題2でトランザクションとStripeを分離させるため、Stripe失敗時に明示的に在庫戻しを行う必要がある）。
     * 【指摘#9】Apikey を env()ではなく、config('services.stripe.secret'|'public') で取得。（config:cache 後も動作させるため）
     * 【課題1】在庫の取得・チェック・減算を同一トランザクション内で lockForUpdate() により悲観的ロック。
     * 【課題2】トランザクション内に外部API（Stripe）を含めない。在庫減算のみトランザクションで行い、コミット後にStripe呼び出し。Stripe失敗時は在庫を戻す。
     */
    public function checkout()
    {
        $user = User::findOrfail(Auth::id());
        $products = $user->products;

        $lineItems = [];
        foreach($products as $product){

            // 指摘#2 修正: price_data 形式で渡す（Stripev7以降で必須）
            $lineItem = [
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $product->price,
                    'product_data' => [
                        'name' => $product->name,
                        'description' => $product->information,
                    ],
                ],
                'quantity' => $product->pivot->quantity,
            ];
            // $lineItems配列に$lineItemを追加
            array_push($lineItems, $lineItem);

            // ※上記のコードの書き換え。同じ意味であるが、より読みやすいコードになっている。
            // $lineItems[] = [
            //     'price_data' => [
            //         'currency' => 'jpy',
            //         'unit_amount' => $product->price,
            //         'product_data' => [
            //             'name' => $product->name,
            //             'description' => $product->information,
            //         ],
            //     ],
            //     'quantity' => $product->pivot->quantity,
            // ];
        }
        // dd($lineItems);

        // Stripe の公開鍵を取得。これをクライアントサイドで Stripe.js に渡して、決済ページを表示する。
        $publicKey = config('services.stripe.public');


        // 【課題2】第1トランザクション: 在庫のロック・チェック・減算
        // Stripe 失敗時の在庫戻しは後述の catch で行う。
        try {
            DB::transaction(function () use ($products) {
                // 【課題1】在庫チェックをトランザクション内で実施し、lockForUpdate() で悲観的ロックを取得。
                // 修正前: トランザクション外で sum('quantity') のみ実行していたため、チェックと減算の間に他リクエストが割り込み在庫マイナスになる可能性があった。
                // 修正後: SELECT ... FOR UPDATE により行をロックし、同一トランザクション内でチェック→減算まで行うことで Race Condition を防止。不足時は Exception でロールバック。
                foreach ($products as $product) {

                    $quantity = Stock::where('product_id', $product->id)
                        ->lockForUpdate()
                        ->sum('quantity');

                    if ($product->pivot->quantity > $quantity) {
                        throw new \Exception('在庫不足');
                    }
                }

                // 【課題1】上記で全商品の在庫をロック・チェック済みのため、このタイミングで在庫減算を行っても他トランザクションは割り込めない。
                foreach ($products as $product) {
                    Stock::create([
                        'product_id' => $product->id,
                        'type' => \Constant::PRODUCT_LIST['reduce'],
                        'quantity' => $product->pivot->quantity * -1
                    ]);
                }
            });

        } catch (\Throwable $e) {

            // 【課題1】在庫不足で throw した場合は専用メッセージでカート一覧へリダイレクト
            if ($e->getMessage() === '在庫不足') {
                return redirect()->route('user.cart.index')
                    ->with(['message' => '在庫不足です。', 'status' => 'alert']);
            }

            // 在庫不足以外（DBエラー等）はトランザクションがロールバック済みのため、在庫戻しは不要。
            return redirect()->route('user.cart.index')
                ->with(['message' => '決済の開始に失敗しました。', 'status' => 'alert']);
        }


        // 【課題2】第2トランザクション: Stripe 決済の呼び出し
        // Stripeは、第1トランザクション完了後に、第1トランザクション外で呼び出す。（DB接続を占有したままHTTPを待たない）
        // stripe 決済失敗時は、第1トランザクションで減算した在庫を戻す。
        try {

            // setApiKeyで秘密鍵を、Stripe PHP SDKのAPIキーに設定
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // \Stripe\Checkout\Session::createでセッションを作成
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'], // カードのみに制限する場合
                'line_items' => $lineItems, // 商品情報
                'mode' => 'payment', // 決済モード 1回限りの決済
                'success_url' => route('user.cart.success'), // 成功時のリダイレクトURL
                'cancel_url' => route('user.cart.cancel'), // キャンセル時のリダイレクトURL
            ]);

        } catch (\Throwable $e) {

            // 【課題2】Stripe 失敗時: すでに減算した在庫を戻す（データ不整合を防ぐ）
            DB::transaction(function () use ($products) {
                foreach ($products as $product) {
                    Stock::create([
                        'product_id' => $product->id,
                        'type' => \Constant::PRODUCT_LIST['add'],
                        'quantity' => $product->pivot->quantity
                    ]);
                }
            });

            return redirect()->route('user.cart.index')
                ->with(['message' => '決済の開始に失敗しました。', 'status' => 'alert']);
        }

        return view('user.checkout', compact('session', 'publicKey'));
    }


    /**
     * Stripe 決済完了後のコールバック
     * メール送信（ThanksMail / OrderedMail）を dispatch し、
     * カートを削除して商品一覧へリダイレクトする。
     */
    public function success()
    {
        ////
        $items = Cart::where('user_id', Auth::id())->get(); //where句は最後にget()が必要

        $products = CartService::getItemsInCart($items);

        $user = User::findOrfail(Auth::id());

        // 非同期でメールを送信
        // jobクラスに、引数として「商品情報」と「ユーザー情報」を渡す

        // ユーザーに感謝メールを送信
        SendThanksMail::dispatch($products, $user);

        // オーナーに注文メールを送信
        // 商品により、オーナーが異なるため、foreachでループ処理する。
        // Mailtrap等のレート制限（1秒あたりのメール数）を避けるため、各ジョブに遅延を付与
        foreach($products as $index => $product){ // インデックス番号も取得
            SendOrderedMail::dispatch($product, $user)
                ->delay(now()->addSeconds(5 + $index * 5));
        }

        // dd('ユーザーサンクスメール送信テスト');
        ////

        // カート内の商品を削除
        Cart::where('user_id', Auth::id())->delete();

        // 成功時は、商品一覧ページにリダイレクト
        return redirect()->route('user.items.index');
    }


    /**
     * Stripe チェックアウト画面でユーザーがキャンセルしたときのコールバック
     *
     * 【課題2】checkout() で在庫を事前減算しているため、キャンセル時は在庫を戻す。
     * （Webhook 方式の場合は checkout() で減算しないため不要になる）
     */
    public function cancel()
    {
        $user = User::findOrfail(Auth::id());

        // キャンセル時はカート内の商品を在庫に戻す
        foreach($user->products as $product){
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['add'],
                'quantity' => $product->pivot->quantity
            ]);
        }

        // キャンセル時は、カートページにリダイレクト
        return redirect()->route('user.cart.index');
    }
}
