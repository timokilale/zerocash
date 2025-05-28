<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'base_interest_rate',
        'max_amount',
        'min_amount',
        'max_term_months',
        'min_term_months',
        'processing_fee_rate',
    ];

    protected function casts(): array
    {
        return [
            'base_interest_rate' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'processing_fee_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the loans of this type.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the interest rates for this loan type.
     */
    public function interestRates()
    {
        return $this->hasMany(InterestRate::class);
    }
}
