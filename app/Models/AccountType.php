<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'minimum_balance',
        'interest_rate',
        'monthly_fee',
    ];

    protected function casts(): array
    {
        return [
            'minimum_balance' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'monthly_fee' => 'decimal:2',
        ];
    }

    /**
     * Get the accounts of this type.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
