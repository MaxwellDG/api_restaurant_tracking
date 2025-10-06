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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('order_id')->constrained('orders');
            $table->float('subtotal');
            $table->float('tax');
            $table->float('total');
            $table->enum('payment_method', ['Visa', 'MasterCard', 'Cash']);
            $table->enum('status', ['Pending', 'Paid', 'Failed']);
            $table->string('transaction_id');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('client_id')->constrained('clients'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
