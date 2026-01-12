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
            // Drop the label_id foreign key if it exists
            if (Schema::hasColumn('orders', 'label_id')) {
                $table->dropForeign(['label_id']);
                $table->dropColumn('label_id');
            }
            
            // Add simple text label field (nullable first)
            $table->string('label')->nullable()->after('status');
        });
        
        // Backfill existing orders with "None"
        \Illuminate\Support\Facades\DB::table('orders')
            ->whereNull('label')
            ->update(['label' => 'None']);
        
        // Make label non-nullable
        Schema::table('orders', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the text label
            $table->dropColumn('label');
            
            // Restore label_id foreign key
            $table->foreignId('label_id')->nullable()->after('status')->constrained('labels')->onDelete('set null');
        });
    }
};
