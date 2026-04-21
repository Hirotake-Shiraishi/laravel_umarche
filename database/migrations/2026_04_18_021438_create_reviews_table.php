<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 商品レビュー（reviews）テーブル
 *
 * 【外部キーの onDelete 制約】
 * - product_id: order_items と同様に restrict（商品削除でレビューが消えるより、誤削除を防ぐ）
 * - user_id: cascade（ユーザー削除時は、レビューも削除）
 *
 * 【購入者限定・二重投稿防止】
 * - 1商品につき1ユーザー1回までにするため、(product_id, user_id) に複合ユニーク制約を張る。
 *   アプリ側のバリデーションだけだと同時投稿（レース）で抜ける可能性があるため、DBでも最後に防ぐ。
 */
class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreignId('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // 星評価：1〜5 の整数（バリデーションでも min/max を掛ける）
            $table->unsignedTinyInteger('rating');

            // 星だけのレビュー（コメント無し）も許可するため nullable
            $table->text('comment')->nullable();

            $table->timestamps();

            // 1商品につき1ユーザー1回のみ
            $table->unique(['product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
