<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'amount',
        'deposited_by',
        'deposit_method',
        'reference',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the account for this deposit.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who made the deposit.
     */
    public function depositedBy()
    {
        return $this->belongsTo(User::class, 'deposited_by');
    }

    /**
     * Process the deposit.
     */
    public function process(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        try {
            \DB::transaction(function () {
                // Get deposit transaction type
                $transactionType = TransactionType::where('code', 'DEP')->first();

                if (!$transactionType) {
                    throw new \Exception('Deposit transaction type not found');
                }

                // Create transaction for deposit
                $transaction = Transaction::create([
                    'receiver_account_id' => $this->account_id,
                    'transaction_type_id' => $transactionType->id,
                    'amount' => $this->amount,
                    'description' => "Cash deposit - {$this->deposit_method}",
                    'reference' => $this->reference,
                    'status' => 'completed',
                    'processed_by' => $this->deposited_by,
                    'processed_at' => now(),
                ]);

                // Credit the account
                $this->account->credit($this->amount);

                // Update deposit status
                $this->status = 'completed';
                $this->save();

                // Create notification
                Notification::create([
                    'user_id' => $this->account->user_id,
                    'title' => 'Deposit Completed',
                    'message' => "A deposit of TSh " . number_format($this->amount, 2) . " has been made to your account.",
                    'type' => 'transaction',
                    'data' => ['deposit_id' => $this->id, 'transaction_id' => $transaction->id],
                ]);
            });

            return true;
        } catch (\Exception $e) {
            $this->status = 'cancelled';
            $this->save();
            return false;
        }
    }
}
