<?php

use App\Http\Controllers\Owner\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Owner\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Owner\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Owner\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Owner\Auth\NewPasswordController;
use App\Http\Controllers\Owner\Auth\PasswordResetLinkController;
use App\Http\Controllers\Owner\Auth\RegisteredUserController;
use App\Http\Controllers\Owner\Auth\VerifyEmailController;
use App\Http\Controllers\Owner\ShopController;
use App\Http\Controllers\Owner\ImageController;
use App\Http\Controllers\Owner\ProductController;
use App\Http\Controllers\Owner\OrderController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('owner.welcome');
// });

Route::prefix('shops')
    ->middleware('auth:owners')
    ->group(function () {
        Route::get('index', [ShopController::class, 'index'])
            ->name('shops.index');
        Route::get('edit/{shop}', [ShopController::class, 'edit'])
            ->name('shops.edit');
        Route::post('update/{shop}', [ShopController::class, 'update'])
            ->name('shops.update');
    });

/**
 * dashboard ダッシュボード
 */
Route::get('/dashboard', function () {
    return view('owner.dashboard');
})->middleware(['auth:owners'])->name('dashboard');

/**
 * products 商品管理
 */
Route::resource('products', ProductController::class)
    ->middleware('auth:owners')
    ->except(['show']);

/**
 * images 画像管理
 */
Route::resource('images', ImageController::class)
    ->middleware('auth:owners')
    ->except(['show']);

/**
 * orders 受注管理
 *
 * 専用エンドポイントで用途を明確化 :
 * - PATCH /orders/{order} だと「注文の"何か"を部分更新」と読めるが、
 *   PATCH /orders/{order}/status とすると「注文の”ステータス”を部分更新」と一目で読める。
 */
Route::middleware('auth:owners')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

// Route::get('/register', [RegisteredUserController::class, 'create'])
//                 ->middleware('guest')
//                 ->name('register');

// Route::post('/register', [RegisteredUserController::class, 'store'])
//                 ->middleware('guest');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->middleware('guest')
                ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
                ->middleware('guest')
                ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->middleware('guest')
                ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.update');

Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->middleware('auth:owners')
                ->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['auth:owners', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth:owners', 'throttle:6,1'])
                ->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->middleware('auth:owners')
                ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware('auth:owners');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth:owners')
                ->name('logout');
