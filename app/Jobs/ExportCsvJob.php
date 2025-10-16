<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ExportCsvJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    protected $startDate;
    protected $endDate;
    protected $email;
    protected $exportId;
    protected $totalCount;

    /**
     * Create a new job instance.
     */
    public function __construct($startDate, $endDate, $email, $exportId, $totalCount)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->email = $email;
        $this->exportId = $exportId;
        $this->totalCount = $totalCount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate unique filename
        $filename = "orders_export_{$this->exportId}_" . date('Y-m-d_H-i-s') . ".csv";
        $filePath = storage_path("app/temp/{$filename}");
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $output = fopen($filePath, 'w');
        $processed = 0;
        
        try {
            // Write CSV headers
            $headers = [
                'Order ID',
                'Customer Email',
                'Customer Name',
                'Order Date',
                'Total Amount',
                'Completed At',
                'Paid At',
                'Item Name',
                'Item Description',
                'Item Price',
                'Quantity',
                'Unit Price',
                'Item Total'
            ];
            
            fputcsv($output, $headers);
            
            $chunkSize = 100; // Process 100 orders at a time
            
            // Use chunked queries to process orders in batches
            Order::whereHas('user', function ($query) {
                    $query->where('email', $this->email);
                })
                ->where('orders.created_at', '>=', $this->startDate)
                ->where('orders.created_at', '<=', $this->endDate)
                ->orderBy('created_at', 'desc')
                ->chunk($chunkSize, function ($orders) use ($output, &$processed) {
                    // Load relationships for this chunk
                    $orders->load('user', 'items');
                    
                    foreach ($orders as $order) {
                        $this->writeOrderToCsv($output, $order);
                        $processed++;
                        
                        // Update progress every 10 orders to avoid too many cache writes
                        if ($processed % 10 === 0) {
                            $percentage = $this->totalCount > 0 ? round(($processed / $this->totalCount) * 100, 2) : 0;
                            CacheFacade::put("export_progress_{$this->exportId}", [
                                'total' => $this->totalCount,
                                'processed' => $processed,
                                'percentage' => $percentage,
                                'status' => 'processing',
                                'email' => $this->email
                            ], 3600);
                        }
                    }
                });
            
            fclose($output);
            
            // Send email with CSV attachment
            $this->sendCsvEmail($this->email, $filePath, $filename, $processed);
            
            // Mark as completed
            CacheFacade::put("export_progress_{$this->exportId}", [
                'total' => $this->totalCount,
                'processed' => $processed,
                'percentage' => 100,
                'status' => 'completed',
                'email' => $this->email,
                'message' => 'CSV file sent to your email successfully'
            ], 3600);
            
        } catch (\Exception $e) {
            if (is_resource($output)) {
                fclose($output);
            }
            
            // Clean up file on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Mark as failed
            CacheFacade::put("export_progress_{$this->exportId}", [
                'total' => $this->totalCount,
                'processed' => $processed,
                'percentage' => 0,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'email' => $this->email
            ], 3600);
            
            Log::error("CSV Export Job Failed: " . $e->getMessage());
            throw $e;
        } finally {
            // Clean up the temporary file after email is sent
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Write a single order and its items to CSV
     */
    private function writeOrderToCsv($output, $order)
    {
        $orderData = [
            $order->id,
            $order->user->email ?? '',
            $order->user->name ?? '',
            $order->created_at->format('Y-m-d H:i:s'),
            $order->total_amount,
            $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : '',
            $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '',
        ];
        
        if ($order->items->count() > 0) {
            foreach ($order->items as $item) {
                $itemData = array_merge($orderData, [
                    $item->name,
                    $item->description,
                    $item->price,
                    $item->pivot->quantity,
                    $item->pivot->unit_price,
                    $item->pivot->quantity * $item->pivot->unit_price
                ]);
                
                fputcsv($output, $itemData);
            }
        } else {
            // If no items, still include the order with empty item fields
            $itemData = array_merge($orderData, ['', '', '', '', '', '']);
            fputcsv($output, $itemData);
        }
    }

    /**
     * Send CSV file via email
     */
    private function sendCsvEmail($email, $filePath, $filename, $totalRecords)
    {
        try {
            Mail::raw("Your orders export is ready!\n\nTotal records: {$totalRecords}\n\nPlease find the CSV file attached.", function ($message) use ($email, $filePath, $filename) {
                $message->to($email)
                    ->subject('Your Orders Export - ' . date('Y-m-d H:i:s'))
                    ->attach($filePath, [
                        'as' => $filename,
                        'mime' => 'text/csv'
                    ]);
            });
        } catch (\Exception $e) {
            Log::error("Failed to send CSV email to {$email}: " . $e->getMessage());
            throw new \Exception("Failed to send email: " . $e->getMessage());
        }
    }
}
