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
        // Drop receipts table if it exists
        Schema::dropIfExists('receipts');

        // Add cascade delete to items.category_id foreign key
        Schema::table('items', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['category_id']);
            
            // Re-add the foreign key with cascade delete
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
        });

        // Add soft deletes to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Revert cascade delete on items.category_id
        Schema::table('items', function (Blueprint $table) {
            // Drop the cascade foreign key
            $table->dropForeign(['category_id']);
            
            // Re-add the original foreign key without cascade
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories');
        });

        // Note: We don't recreate the receipts table in rollback
        // as it's being permanently removed
    }
};
