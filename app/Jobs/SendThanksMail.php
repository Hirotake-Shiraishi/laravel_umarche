<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ThanksMail;

class SendThanksMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ジョブクラスで、事前にパブリックプロパティを定義し、
    // コンストラクタの中で設定することで、コントローラから渡ってくる値を受け取ることができる。
    public $products;
    public $user;

    public function __construct($products, $user)
    {
        $this->products = $products;
        $this->user = $user;
    }


    public function handle()
    {
        // Mail::to('test@example.com') //メールの送信先の指定
        //     ->send(new TestMail()); //Mailableクラス


        // メールの設定
        Mail::to($this->user) //メールの送信先の指定
            ->send(new ThanksMail($this->products, $this->user)); //コントローラから渡ってきた情報を続いて、Mailableクラスに渡す
    }
}
