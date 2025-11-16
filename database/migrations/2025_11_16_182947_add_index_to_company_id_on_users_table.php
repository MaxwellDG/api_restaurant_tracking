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
        Schema::table('users', function (Blueprint $table) {
            $table->index('company_id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->index('company_id');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->index('company_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });
    }
};
