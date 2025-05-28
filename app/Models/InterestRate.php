<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_type_id',
        'min_amount',
        'max_amount',
        'min_term_months',
        'max_term_months',
        'base_rate',
        'risk_factor',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'base_rate' => 'decimal:2',
            'risk_factor' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    /**
     * Get the loan type.
     */
    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }

    /**
     * Scope for currently effective rates.
     */
    public function scopeEffective($query)
    {
        return $query->where('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', now());
                    });
    }
}
