<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;
use App\Http\Controllers\User\ItemController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ReviewController;

/*
 * ユーザー関連ルート
 *
 * 認証済みなら商品一覧へ、未認証なら welcomeページを表示。
 * 商品一覧は /items で提供。
 */
Route::get('/', function () {
    if (Auth::guard('users')->check()) {
        return redirect()->route('user.items.index');
    }
    return view('user.welcome');
});

Route::middleware('auth:users')
    ->group(function () {
        Route::get('items', [ItemController::class, 'index'])->name('items.index');
        Route::get('show/{item}', [ItemController::class, 'show'])->name('items.show');

        /**
         * レビュー投稿
         */
        Route::post('show/{item}/reviews', [ReviewController::class, 'store'])->name('items.reviews.store');
    });


/*
 * カート
 */
Route::prefix('cart')
    ->middleware('auth:users')
    ->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('add', [CartController::class, 'add'])->name('cart.add');
        Route::post('delete/{item}', [CartController::class, 'delete'])->name('cart.delete');
        Route::get('checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::get('success', [CartController::class, 'success'])->name('cart.success');
        Route::get('cancel', [CartController::class, 'cancel'])->name('cart.cancel');
});


/*
 * 注文履歴
 *
 * - RouteServiceProvider で as('user.') が付くため、ルート名は user.orders.index となる。
 */
Route::prefix('user')->middleware('auth:users')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Route::get('/dashboard', function () {
//     return view('user.dashboard');
// })->middleware(['auth:users'])->name('dashboard');

/*
 * コンポーネントテスト関連ルート
 */
Route::get('/component-test1', [ComponentTestController::class, 'showComponent1']);
Route::get('/component-test2', [ComponentTestController::class, 'showComponent2']);
Route::get('/servicecontainertest', [LifeCycleTestController::class, 'showServiceContainerTest']);
Route::get('/serviceprovidertest', [LifeCycleTestController::class, 'showServiceProviderTest']);

require __DIR__.'/auth.php';
