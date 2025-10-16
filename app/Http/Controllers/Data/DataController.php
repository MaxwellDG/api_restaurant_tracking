<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\Data\ExportDataRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ExportCsvJob;

class DataController extends Controller
{
    /**
     * Export orders data as CSV and send via email
     * 
     * This endpoint generates a CSV file and sends it as an email attachment
     * to handle large datasets without requiring client connection during processing.
     * Progress can be tracked using the returned export ID.
     * 
     * Usage:
     * 1. Call this endpoint to start the export
     * 2. Extract the export ID from response
     * 3. Poll /api/export/progress?export_id={id} to track progress
     * 4. Email is sent when processing reaches 100%
     * 
     * @param ExportDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportData(ExportDataRequest $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $address = $request->address;

        // Generate a unique export ID for progress tracking
        $exportId = 'export_' . uniqid();
        
        // Get total count for progress calculation
        $totalCount = Order::whereHas('user', function ($query) use ($address) {
                $query->where('email', $address);
            })
            ->where('orders.created_at', '>=', $start_date)
            ->where('orders.created_at', '<=', $end_date)
            ->count();

        // Initialize progress tracking
        CacheFacade::put("export_progress_{$exportId}", [
            'total' => $totalCount,
            'processed' => 0,
            'percentage' => 0,
            'status' => 'processing',
            'email' => $address
        ], 3600); // Cache for 1 hour

        // Dispatch the export job to the queue
        ExportCsvJob::dispatch($start_date, $end_date, $address, $exportId, $totalCount);

        return response()->json([
            'message' => 'Export started successfully',
            'export_id' => $exportId,
            'total_orders' => $totalCount,
            'email' => $address
        ], 202);
    }


    /**
     * Get export progress
     */
    public function getExportProgress(Request $request)
    {
        $exportId = $request->get('export_id');
        
        if (!$exportId) {
            return response()->json(['error' => 'Export ID required'], 400);
        }
        
        $progress = CacheFacade::get("export_progress_{$exportId}");
        
        if (!$progress) {
            return response()->json(['error' => 'Export not found or expired'], 404);
        }
        
        return response()->json($progress);
    }

    private function convertOrdersToCsv($orders)
    {
        $csv = '';
        
        // CSV Headers
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
        
        $csv .= implode(',', array_map([$this, 'escapeCsvField'], $headers)) . "\n";
        
        // CSV Data
        foreach ($orders as $order) {
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
                    $itemData = [
                        $order->id,
                        $order->user->email ?? '',
                        $order->user->name ?? '',
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->total_amount,
                        $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : '',
                        $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '',
                        $item->name,
                        $item->description,
                        $item->price,
                        $item->pivot->quantity,
                        $item->pivot->unit_price,
                        $item->pivot->quantity * $item->pivot->unit_price
                    ];
                    
                    $csv .= implode(',', array_map([$this, 'escapeCsvField'], $itemData)) . "\n";
                }
            } else {
                // If no items, still include the order with empty item fields
                $itemData = array_merge($orderData, ['', '', '', '', '', '']);
                $csv .= implode(',', array_map([$this, 'escapeCsvField'], $itemData)) . "\n";
            }
        }
        
        return $csv;
    }

    private function escapeCsvField($field)
    {
        // Escape CSV field - wrap in quotes if contains comma, quote, or newline
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
            return '"' . str_replace('"', '""', $field) . '"';
        }
        return $field;
    }
}