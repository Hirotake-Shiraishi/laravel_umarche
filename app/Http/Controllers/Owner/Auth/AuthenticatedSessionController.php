<?php

namespace App\Http\Controllers\Owner\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('owner.auth.login');
    }


    public function store(LoginRequest $request)
    {
        $request->authenticate();

        // セッショントークンを再生成
        $request->session()->regenerate();

        // セッション情報をログ出力
        Log::debug('owner', $request->session()->all());

        return redirect()->intended(RouteServiceProvider::OWNER_HOME);
    }


    public function destroy(Request $request)
    {
        Auth::guard('owners')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/owner/login');
    }
}
