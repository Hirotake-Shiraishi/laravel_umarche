<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 注文明細（order_items）テーブル
 *
 * 【price カラムを products と別に持つ理由（スナップショット）】
 * 商品マスタ（products.price）は後から変更される可能性があるため、
 * 注文履歴では「購入した当時の単価」を表示したいので、明細行にその時点の価格をコピーして保存する。
 */
class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // どの注文に属する明細か
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // どの商品か（履歴の参照用）。商品削除を防ぎたい場合は onDelete('restrict') などにする。
            $table->foreignId('product_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->unsignedInteger('quantity');

            // 購入時点の単価（円・整数）
            $table->unsignedInteger('price');

            $table->timestamps();

            // order_id にインデックス: 注文詳細で「その注文の全明細」を取るクエリが速くなります。
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
