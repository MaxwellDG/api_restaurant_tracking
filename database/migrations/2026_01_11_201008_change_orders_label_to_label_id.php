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
            // Drop the old label string column
            $table->dropColumn('label');
            
            // Add label_id foreign key
            $table->foreignId('label_id')->nullable()->after('status')->constrained('labels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['label_id']);
            $table->dropColumn('label_id');
            
            // Restore the old label string column
            $table->string('label')->nullable()->after('status');
        });
    }
};
