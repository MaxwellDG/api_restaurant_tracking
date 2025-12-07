<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('uuid')->change();
        });

        Schema::table('orders_items', function (Blueprint $table) {
            $table->string('order_id')->change();
            $table->foreign('order_id')->references('uuid')->on('orders')->onDelete('cascade');
        });

        Schema::create('orders_fees', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->foreignId('fee_id')->constrained('fees')->onDelete('cascade');
            $table->float('value');
            $table->timestamps();

            $table->foreign('order_id')->references('uuid')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_fees');
    }
};
