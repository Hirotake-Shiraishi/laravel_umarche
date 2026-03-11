<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThanksMail extends Mailable
{
    use Queueable, SerializesModels;

    // Mailableクラスでも同様に、事前にパブリックプロパティを定義し、
    // コンストラクタの中で設定することで、渡ってくる値を受け取ることができる。
    public $products;
    public $user;

    public function __construct($products, $user)
    {
        $this->products = $products;
        $this->user = $user;
    }


    public function build()
    {
        return $this
            ->subject('ご購入ありがとうございます。')//タイトル
            ->view('emails.thanks');//本文
    }
}
