<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderedMail;

class SendOrderedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;
    public $user;

    public function __construct($product, $user)
    {
        $this->product = $product;
        $this->user = $user;
    }


    public function handle()
    {
        // レート制限対策: 送信前に待機（SendThanksMail送信直後は制限にかかりやすいため、1通目も確実に空ける）
        sleep(12);

        // このproductは、モデルではなく配列なので、['email']でメールアドレスを取得して指定する必要がある。
        Mail::to($this->product['email']) //メールの送信先の指定
            ->send(new OrderedMail($this->product, $this->user));
    }
}
