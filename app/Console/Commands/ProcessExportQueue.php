<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessExportQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:process-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the export queue for CSV generation and email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting export queue worker...');
        $this->info('This will process CSV export jobs and send them via email.');
        $this->info('Press Ctrl+C to stop the worker.');
        
        // Run the queue worker
        $this->call('queue:work', [
            '--queue' => 'default',
            '--timeout' => 300,
            '--tries' => 3
        ]);
    }
}
