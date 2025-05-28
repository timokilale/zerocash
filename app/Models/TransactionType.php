<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'fee_type',
        'fee_amount',
        'fee_percentage',
    ];

    protected function casts(): array
    {
        return [
            'fee_amount' => 'decimal:2',
            'fee_percentage' => 'decimal:2',
        ];
    }

    /**
     * Get the transactions of this type.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
