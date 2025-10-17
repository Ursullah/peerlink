<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_request_id',
        'borrower_id',
        'lender_id',
        'principal_amount',
        'interest_amount',
        'total_repayable',
        'amount_repaid',
        'status',
        'due_date',
    ];

    /**
     * A Loan belongs to a User (the borrower).
     */
    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    /**
     * A Loan belongs to a User (the lender).
     */
    public function lender()
    {
        return $this->belongsTo(User::class, 'lender_id');
    }

    /**
     * A Loan belongs to one LoanRequest.
     */
    public function loanRequest()
    {
        return $this->belongsTo(LoanRequest::class);
    }

    /**
     * A Loan can have many transactions (polymorphic relation).
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
