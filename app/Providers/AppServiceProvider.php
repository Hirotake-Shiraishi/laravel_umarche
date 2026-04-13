<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }


    public function boot()
    {
        // ngrokの場合はHTTPSに強制
        if (str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }

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
