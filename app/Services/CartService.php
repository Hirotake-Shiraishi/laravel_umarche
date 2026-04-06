<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Cart;

class CartService
{
    /**
     * カート内商品の一覧を取得
     *
     * （指摘#8 / 課題5: データ取得最適化対応）
     *
     *  問題点1：カートの数量取得のクエリに user_id の条件がなく、
     *  同一商品を複数のユーザーがカートに入れている場合、他のユーザーの数量まで合算して返してしまう可能性があった。
     *  ->where('user_id', $item->user_id) を追加。
     *
     *  問題点2：Product::findOrFail() の直後に同一 product_id で Product::where()->get() しており、
     *  二重クエリになっていた。取得済みの $p を再利用して配列を組み立てるように修正。
     */
    public static function getItemsInCart($items)
    {
        $products = [];

        // dd($items);

        foreach($items as $item)
        {
            $p = Product::findOrFail($item->product_id);

            // $p->shop->owner の時点で返り値が Ownerモデルになり、
            // firstでつなげると毎回 id=1のOwnerが取得されてしまうので修正。
            $owner = $p->shop->owner;

            $ownerInfo = [
                'ownerName' => $owner->name,
                'email' => $owner->email
            ];

            // dd($ownerInfo);

            // ※二重クエリになるため、以下の$productは不要。
            // $product = Product::where('id', $item->product_id)
            //     ->select('id', 'name', 'price')
            //     ->get()
            //     ->toArray(); // 商品情報の配列

            // dd($product);

            // 修正: user_id でフィルタし、他ユーザー分を合算しないように修正。
            $quantity = Cart::where('product_id', $item->product_id)
                ->where('user_id', $item->user_id)
                ->select('quantity')
                ->get()
                ->toArray(); // 在庫数の配列

            // dd($product, $ownerInfo, $quantity);

            // 修正: 二重クエリを避け、取得済みの $p から id/name/price を組み立てるように修正。
            $result = array_merge(
                ['id' => $p->id, 'name' => $p->name, 'price' => $p->price],
                $ownerInfo,
                $quantity[0]
            );

            // dd($result);

            array_push($products, $result); //配列に追加

            // dd($products);

        }

        return $products;
    }
}
