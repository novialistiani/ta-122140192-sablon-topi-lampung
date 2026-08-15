<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNumberSequence extends Model
{
    protected $primaryKey = 'date_key';
    public $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['date_key', 'next_number'];
    
    /**
     * Get next order number for given date
     * Uses atomic increment to prevent race conditions
     * Accounts for existing orders that might have been created with fake dates
     */
    public static function getNextOrderNumber($date = null)
    {
        $date = $date ?? date('Ymd');
        
        // Find the highest existing order number with this date prefix (regardless of actual created_at date)
        $maxOrder = \App\Models\Order::where('order_number', 'LIKE', 'ORD-' . $date . '-%')
            ->selectRaw('CAST(SUBSTRING(order_number, -4) AS UNSIGNED) as num')
            ->orderByDesc('num')
            ->first();
        
        $maxExistingNum = $maxOrder ? ((int)$maxOrder->num) : 0;
        
        // Now handle the sequence table
        // First try to update existing sequence
        $updated = \DB::table('order_number_sequences')
            ->where('date_key', $date)
            ->increment('next_number');
        
        if (!$updated) {
            // If no rows updated, create new sequence starting after the max existing
            try {
                self::create([
                    'date_key' => $date,
                    'next_number' => $maxExistingNum + 2  // Next number after the max
                ]);
                $nextNumber = $maxExistingNum + 1;
            } catch (\Exception $e) {
                // Race condition: another process created it
                // Get the current value
                $sequence = self::where('date_key', $date)->first();
                $nextNumber = $sequence->next_number;
                
                // Increment for next use
                \DB::table('order_number_sequences')
                    ->where('date_key', $date)
                    ->increment('next_number');
            }
        } else {
            // Get the current incremented value
            $sequence = self::where('date_key', $date)->first();
            $nextNumber = $sequence->next_number;
            
            // Make sure it's greater than any existing numbers
            if ($nextNumber <= $maxExistingNum) {
                $nextNumber = $maxExistingNum + 1;
                \DB::table('order_number_sequences')
                    ->where('date_key', $date)
                    ->update(['next_number' => $nextNumber + 1]);
            }
        }
        
        return 'ORD-' . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}

