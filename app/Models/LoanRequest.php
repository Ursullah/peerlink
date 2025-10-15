<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'amount', 
        'repayment_period', 
        'interest_rate', 
        'reason', 
        'collateral_locked',
        'status'
    ];

    // A LoanRequest belongs to a User (the borrower)
    public function borrower()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}