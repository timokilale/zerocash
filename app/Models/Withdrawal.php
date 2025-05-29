<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'amount',
        'withdrawn_by',
        'withdrawal_method',
        'reference',
        'notes',
        'status',
        'authorized_by',
        'authorized_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'authorized_at' => 'datetime',
        ];
    }

    /**
     * Get the account that owns the withdrawal.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who initiated the withdrawal.
     */
    public function withdrawnBy()
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }

    /**
     * Get the user who authorized the withdrawal.
     */
    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /**
     * Process the withdrawal.
     */
    public function process(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        try {
            \DB::transaction(function () {
                // Get withdrawal transaction type
                $transactionType = TransactionType::where('code', 'WTH')->first();

                if (!$transactionType) {
                    throw new \Exception('Withdrawal transaction type not found');
                }

                // Check if account has sufficient balance
                if (!$this->account->hasSufficientBalance($this->amount)) {
                    throw new \Exception('Insufficient balance');
                }

                // Calculate fee
                $fee = Transaction::calculateFee($this->amount, $transactionType->id);
                $totalAmount = $this->amount + $fee;

                // Check if account has sufficient balance including fee
                if (!$this->account->hasSufficientBalance($totalAmount)) {
                    throw new \Exception('Insufficient balance including withdrawal fee');
                }

                // Create transaction for withdrawal
                $transaction = Transaction::create([
                    'sender_account_id' => $this->account_id,
                    'transaction_type_id' => $transactionType->id,
                    'amount' => $this->amount,
                    'fee' => $fee,
                    'description' => "Cash withdrawal - {$this->withdrawal_method}",
                    'reference' => $this->reference,
                    'status' => 'completed',
                    'processed_by' => $this->withdrawn_by,
                    'processed_at' => now(),
                ]);

                // Debit the account (amount + fee)
                $this->account->debit($totalAmount);

                // Update withdrawal status
                $this->update([
                    'status' => 'completed',
                    'authorized_by' => auth()->id(),
                    'authorized_at' => now(),
                ]);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Withdrawal processing failed: ' . $e->getMessage());
            return false;
        }
    }
}
