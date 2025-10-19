<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'transactionable_id',
        'transactionable_type',
        'type',
        'amount',
        'status',
        'payhero_transaction_id',
        'external_reference',
        'failure_reason',
    ];

    /**
     * Get the parent transactionable model (loan, loan request, etc.).
     */
    public function transactionable()
    {
        return $this->morphTo();
    }

    /**
     * A Transaction belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
