<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'sender_account_id',
        'receiver_account_id',
        'transaction_type_id',
        'amount',
        'fee_amount',
        'total_amount',
        'description',
        'reference',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = static::generateTransactionNumber();
            }

            // Calculate fee if not set
            if ($transaction->fee_amount === null) {
                $transaction->fee_amount = static::calculateFee(
                    $transaction->amount,
                    $transaction->transaction_type_id
                );
            }

            // Calculate total amount
            $transaction->total_amount = $transaction->amount + $transaction->fee_amount;
        });
    }

    /**
     * Generate a unique transaction number.
     */
    public static function generateTransactionNumber(): string
    {
        do {
            // Generate transaction number: TXN + YYYYMMDD + 8 random digits
            $transactionNumber = 'TXN' . date('Ymd') . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (static::where('transaction_number', $transactionNumber)->exists());

        return $transactionNumber;
    }

    /**
     * Generate a unique reference number for transfers.
     */
    public static function generateReferenceNumber(): string
    {
        do {
            // Generate reference number: REF + YYYYMMDD + 6 random digits
            $referenceNumber = 'REF' . date('Ymd') . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('reference', $referenceNumber)->exists());

        return $referenceNumber;
    }

    /**
     * Calculate transaction fee based on amount and transaction type.
     */
    public static function calculateFee(float $amount, int $transactionTypeId): float
    {
        $transactionType = TransactionType::find($transactionTypeId);

        if (!$transactionType) {
            return 0;
        }

        switch ($transactionType->fee_type) {
            case 'fixed':
                return $transactionType->fee_amount;
            case 'percentage':
                return ($amount * $transactionType->fee_percentage) / 100;
            case 'tiered':
                // Implement tiered fee calculation logic here
                return static::calculateTieredFee($amount, $transactionType);
            default:
                return 0;
        }
    }

    /**
     * Calculate tiered fee (can be customized based on business rules).
     */
    private static function calculateTieredFee(float $amount, TransactionType $transactionType): float
    {
        // Example tiered structure - customize as needed
        if ($amount <= 10000) {
            return 500;
        } elseif ($amount <= 50000) {
            return 1000;
        } elseif ($amount <= 100000) {
            return 1500;
        } else {
            return 2000;
        }
    }

    /**
     * Get the sender account.
     */
    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    /**
     * Get the receiver account.
     */
    public function receiverAccount()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id');
    }

    /**
     * Get the transaction type.
     */
    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    /**
     * Get the user who processed the transaction.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Process the transaction.
     */
    public function process(User $processedBy = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        try {
            \DB::transaction(function () use ($processedBy) {
                // Debit sender account if exists
                if ($this->senderAccount) {
                    if (!$this->senderAccount->debit($this->total_amount)) {
                        throw new \Exception('Insufficient balance in sender account');
                    }
                }

                // Credit receiver account if exists
                if ($this->receiverAccount) {
                    $this->receiverAccount->credit($this->amount);
                }

                // Update transaction status
                $this->status = 'completed';
                $this->processed_at = now();
                if ($processedBy) {
                    $this->processed_by = $processedBy->id;
                }
                $this->save();

                // Create notifications
                $this->createNotifications();
            });

            return true;
        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->save();
            return false;
        }
    }

    /**
     * Create notifications for transaction.
     */
    private function createNotifications(): void
    {
        // Notify sender
        if ($this->senderAccount) {
            Notification::create([
                'user_id' => $this->senderAccount->user_id,
                'title' => 'Transaction Completed',
                'message' => "Your transaction of TSh " . number_format($this->amount, 2) . " has been completed.",
                'type' => 'transaction',
                'data' => ['transaction_id' => $this->id],
            ]);
        }

        // Notify receiver
        if ($this->receiverAccount) {
            Notification::create([
                'user_id' => $this->receiverAccount->user_id,
                'title' => 'Money Received',
                'message' => "You have received TSh " . number_format($this->amount, 2) . " in your account.",
                'type' => 'transaction',
                'data' => ['transaction_id' => $this->id],
            ]);
        }
    }
}
