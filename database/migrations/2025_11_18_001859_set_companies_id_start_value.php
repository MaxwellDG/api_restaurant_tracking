<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set company ID sequence to start at 10000
        DB::statement("SELECT setval('company_id_seq', 10000, false)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset sequence back to 1
        DB::statement("SELECT setval('company_id_seq', 1, false)");
    }
};
