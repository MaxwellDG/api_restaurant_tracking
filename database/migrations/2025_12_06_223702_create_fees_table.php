<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('value');
            $table->string('applies_to')->default('order');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
        });

        // Create fee entries for existing companies with name defaulted to "Tax"
        $companies = DB::table('companies')->whereNull('deleted_at')->get();
        foreach ($companies as $company) {
            DB::table('fees')->insert([
                'company_id' => $company->id,
                'name' => 'Tax',
                'value' => 0,
                'applies_to' => 'order',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
