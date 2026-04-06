<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 指摘#5 / 課題4: バリデーション強化対応
 * CartController::add()で quantity・product_id をバリデーションをせずそのまま使っており、
 * 負の数や極端に大きい数を送信できる・存在しないIDで孤立したカートレコードが作れるようになってしまっていた。
 * CartAddRequestクラスを作成し、ここでバリデーションを実施。
 */
class CartAddRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'quantity' => 'required|integer|min:1|max:99',
            'product_id' => 'required|integer|exists:products,id',
        ];
    }
}
