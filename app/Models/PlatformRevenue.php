<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformRevenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'source_id',
        'source_type',
        'amount',
        'percentage',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
        'percentage' => 'decimal:2',
    ];

    /**
     * Get the source model (polymorphic relationship)
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Get revenue by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get total revenue amount
     */
    public function scopeTotalAmount($query)
    {
        return $query->sum('amount');
    }

    /**
     * Get revenue for a specific period
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted amount in KES
     */
    public function getFormattedAmountAttribute()
    {
        return 'KES '.number_format($this->amount / 100, 2);
    }
}
