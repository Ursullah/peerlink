<?php

namespace App\Models;

// 1. Import the missing HasFactory trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // 2. Use both traits correctly in the class
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * ...
     */
    protected $fillable = [
        'name',
        'phone_number',
        'national_id',
        'role',
        'email',
        'password',
        'avatar',
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

    // --- Relationships ---

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
