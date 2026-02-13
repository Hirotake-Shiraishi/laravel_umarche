<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');
    }

    public function index()
    {
        // ログイン中のユーザーIDを取得
        $ownerId = Auth::id();
        // owner_id が $ownerId と一致するレコードだけを取得する
        $shops = Shop::where('owner_id', $ownerId)->get();

        return view('owner.shops.index', compact('shops'));
    }

    public function edit($id)
    {

    }

    public function update(Request $request, $id)
    {

    }
}
