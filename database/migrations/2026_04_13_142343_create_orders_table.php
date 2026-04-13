<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 注文ヘッダ（orders）テーブル
 */
class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

            // 注文ID。主キー。
            $table->id();

            // ユーザーID。users テーブルへの外部キー。
            // onDelete('cascade'): ユーザー削除時に注文も削除する。
            $table->foreignId('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Stripe Checkout のセッション ID。決済完了後の success URL のクエリで渡される。
            // unique: 同じセッションで二重に注文レコードが作られるのを DB レベルでも防ぐため。
            // nullable: URL に付かないアクセス（ブックマーク等）でもアプリが落ちないようにするため。
            $table->string('stripe_session_id')->nullable()->unique();

            // 注文時点の合計金額（円・整数）。カート画面の計算と揃える。
            $table->unsignedInteger('total_price');

            // 発送状態など。DB には英語の値（pending / shipped）を保存し、画面で日本語表示する。
            $table->string('status', 32);

            // 作成日時・更新日時。created_at / updated_at は自動生成。
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
