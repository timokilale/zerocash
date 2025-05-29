<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'account_id',
        'loan_type_id',
        'branch_id',
        'principal_amount',
        'interest_rate',
        'term_months',
        'monthly_payment',
        'outstanding_balance',
        'status',
        'issued_date',
        'due_date',
        'auto_transferred',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'monthly_payment' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'issued_date' => 'date',
            'due_date' => 'date',
            'auto_transferred' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            if (empty($loan->loan_number)) {
                $loan->loan_number = static::generateLoanNumber();
            }

            // Calculate interest rate if not set
            if ($loan->interest_rate === null) {
                $loan->interest_rate = static::calculateInterestRate(
                    $loan->loan_type_id,
                    $loan->principal_amount,
                    $loan->term_months
                );
            }

            // Calculate monthly payment
            $loan->monthly_payment = static::calculateMonthlyPayment(
                $loan->principal_amount,
                $loan->interest_rate,
                $loan->term_months
            );

            // Set outstanding balance to total amount (principal + interest)
            $totalPayment = $loan->monthly_payment * $loan->term_months;
            $loan->outstanding_balance = $totalPayment;

            // Set due date
            if ($loan->issued_date) {
                $loan->due_date = \Carbon\Carbon::parse($loan->issued_date)
                    ->addMonths($loan->term_months);
            }
        });

        static::updated(function ($loan) {
            // Auto-transfer loan amount to account when approved
            if ($loan->isDirty('status') && $loan->status === 'approved' && !$loan->auto_transferred) {
                $loan->transferToAccount();
            }
        });
    }

    /**
     * Generate a unique loan number.
     */
    public static function generateLoanNumber(): string
    {
        do {
            // Generate loan number: LN + YYYYMMDD + 6 random digits
            $loanNumber = 'LN' . date('Ymd') . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('loan_number', $loanNumber)->exists());

        return $loanNumber;
    }

    /**
     * Calculate interest rate based on loan type, amount, and term.
     */
    public static function calculateInterestRate(int $loanTypeId, float $amount, int $termMonths): float
    {
        $loanType = LoanType::find($loanTypeId);

        if (!$loanType) {
            return 15.0; // Default rate
        }

        $baseRate = $loanType->base_interest_rate;

        // Get applicable interest rate from interest_rates table
        $interestRate = InterestRate::where('loan_type_id', $loanTypeId)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->where('min_term_months', '<=', $termMonths)
            ->where('max_term_months', '>=', $termMonths)
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', now());
            })
            ->first();

        if ($interestRate) {
            return $interestRate->base_rate + $interestRate->risk_factor;
        }

        // Apply risk-based adjustments to base rate
        $riskFactor = 0;

        // Higher amounts get lower rates
        if ($amount >= 10000000) {
            $riskFactor -= 2.0;
        } elseif ($amount >= 5000000) {
            $riskFactor -= 1.0;
        } elseif ($amount <= 500000) {
            $riskFactor += 2.0;
        }

        // Longer terms get higher rates
        if ($termMonths >= 60) {
            $riskFactor += 1.5;
        } elseif ($termMonths >= 36) {
            $riskFactor += 1.0;
        } elseif ($termMonths <= 12) {
            $riskFactor -= 0.5;
        }

        return max($baseRate + $riskFactor, 5.0); // Minimum 5% rate
    }

    /**
     * Calculate monthly payment using loan formula.
     */
    public static function calculateMonthlyPayment(float $principal, float $annualRate, int $termMonths): float
    {
        $monthlyRate = ($annualRate / 100) / 12;

        if ($monthlyRate == 0) {
            return $principal / $termMonths;
        }

        $payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) /
                   (pow(1 + $monthlyRate, $termMonths) - 1);

        return round($payment, 2);
    }

    /**
     * Transfer loan amount to the associated account.
     */
    public function transferToAccount(): bool
    {
        if ($this->auto_transferred || $this->status !== 'approved') {
            return false;
        }

        try {
            \DB::transaction(function () {
                // Get loan disbursement transaction type
                $transactionType = TransactionType::where('code', 'LND')->first();

                if (!$transactionType) {
                    throw new \Exception('Loan disbursement transaction type not found');
                }

                // Create transaction for loan disbursement
                $transaction = Transaction::create([
                    'receiver_account_id' => $this->account_id,
                    'transaction_type_id' => $transactionType->id,
                    'amount' => $this->principal_amount,
                    'description' => "Loan disbursement for loan #{$this->loan_number}",
                    'reference' => $this->loan_number,
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                // Credit the account
                $this->account->credit($this->principal_amount);

                // Mark loan as transferred and active
                $this->auto_transferred = true;
                $this->status = 'active';
                $this->issued_date = now()->toDateString();
                $this->save();

                // Create notification
                Notification::create([
                    'user_id' => $this->account->user_id,
                    'title' => 'Loan Approved and Disbursed',
                    'message' => "Your loan of TSh " . number_format($this->principal_amount, 2) . " has been approved and transferred to your account.",
                    'type' => 'loan',
                    'data' => ['loan_id' => $this->id, 'transaction_id' => $transaction->id],
                ]);
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the account associated with the loan.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the loan type.
     */
    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }

    /**
     * Get the branch that offered the loan.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the customer (user) for this loan.
     */
    public function customer()
    {
        return $this->account->user();
    }

    /**
     * Get the total interest amount for this loan.
     */
    public function getTotalInterestAttribute(): float
    {
        $totalPayment = $this->monthly_payment * $this->term_months;
        return $totalPayment - $this->principal_amount;
    }

    /**
     * Get the total payment amount for this loan.
     */
    public function getTotalPaymentAttribute(): float
    {
        return $this->monthly_payment * $this->term_months;
    }

    /**
     * Get the amount paid so far.
     */
    public function getAmountPaidAttribute(): float
    {
        return $this->total_payment - $this->outstanding_balance;
    }
}
