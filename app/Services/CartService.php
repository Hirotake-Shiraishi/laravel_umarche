<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Cart;

class CartService
{
    public static function getItemsInCart($items)
    {
        $products = [];

        // dd($items);

        foreach($items as $item)
        {
            $p = Product::findOrFail($item->product_id);

            // $p->shop->owner の時点で返り値が Ownerモデルになり、
            // firstでつなげると毎回 id=1のOwnerが取得されていたので修正しています。
            $owner = $p->shop->owner;

            $ownerInfo = [
                'ownerName' => $owner->name,
                'email' => $owner->email
            ];

            // dd($ownerInfo);

            $product = Product::where('id', $item->product_id)
                ->select('id', 'name', 'price')
                ->get()
                ->toArray(); // 商品情報の配列

            // dd($product);

            $quantity = Cart::where('product_id', $item->product_id)
                ->select('quantity')
                ->get()
                ->toArray(); // 在庫数の配列

            // dd($product, $ownerInfo, $quantity);

            $result = array_merge($product[0], $ownerInfo, $quantity[0]); // 配列の結合

            // dd($result);

            array_push($products, $result); //配列に追加

            // dd($products);

        }

        return $products;
    }
}
