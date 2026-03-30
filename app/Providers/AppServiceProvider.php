<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }


    public function boot()
    {
        // ownerから始まるURLの場合、セッションクッキー名を変更
        if (request()->is('owner*')) {
            config(['session.cookie' => config('session.cookie_owner')]);
        }

        // adminから始まるURLの場合、セッションクッキー名を変更
        if (request()->is('admin*')) {
        config(['session.cookie' => config('session.cookie_admin')]);
        }
    }
}
