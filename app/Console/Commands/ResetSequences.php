<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetSequences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-sequences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all PostgreSQL sequences to match current max IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = ['categories', 'items', 'orders', 'users', 'companies'];
        
        $this->info('Resetting PostgreSQL sequences...');
        
        foreach ($tables as $table) {
            try {
                $result = DB::select("SELECT setval('{$table}_id_seq', (SELECT COALESCE(MAX(id), 1) FROM {$table}));");
                $this->line("✓ Reset {$table}_id_seq");
            } catch (\Exception $e) {
                $this->warn("✗ Could not reset {$table}_id_seq: " . $e->getMessage());
            }
        }
        
        $this->info('Sequences reset complete!');
        
        return 0;
    }
}
