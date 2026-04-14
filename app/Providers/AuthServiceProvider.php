<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * アプリケーションにおけるポリシー（モデルと Policy クラス）の対応付け。
     *
     * 【$policies の役割】
     * モデルクラスと Policy クラスの対応を登録する。
     * 登録後、authorize('view', $order) のように書くと OrderPolicy::view が呼び出される。
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     * 認証および認可に関するサービスを登録する。
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
