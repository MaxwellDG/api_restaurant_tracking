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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('paid_at');
            $table->renameColumn('id', 'uuid');
            $table->renameColumn('total_amount', 'total');
            $table->string('subtotal');
            $table->enum('status', ['open', 'pending', 'completed'])->default('open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->timestamp('paid_at')->nullable();
            $table->renameColumn('uuid', 'id');
            $table->renameColumn('total', 'total_amount');
            $table->dropColumn('subtotal');
            $table->dropColumn('status');
        });
    }
};
