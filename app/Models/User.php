<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'phone_number',
        'national_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // A User has one Wallet
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    // A User (as a borrower) has many LoanRequests
    public function loanRequests()
    {
        return $this->hasMany(LoanRequest::class);
    }
    
    // A User has many Transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}