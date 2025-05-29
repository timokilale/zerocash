<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'user_id',
        'account_type_id',
        'branch_id',
        'balance',
        'status',
        'opened_date',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'opened_date' => 'date',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->account_number)) {
                $account->account_number = static::generateAccountNumber();
            }
            if (empty($account->opened_date)) {
                $account->opened_date = now()->toDateString();
            }
        });
    }

    /**
     * Generate a unique account number.
     */
    public static function generateAccountNumber(): string
    {
        do {
            // Generate account number: YYYYMMDD + 6 random digits
            $accountNumber = date('Ymd') . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Get the user that owns the account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account type.
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the branch that maintains the account.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the loans for this account.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the deposits for this account.
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Get the sent transactions.
     */
    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_account_id');
    }

    /**
     * Get the received transactions.
     */
    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_account_id');
    }

    /**
     * Alias for sentTransactions (for backward compatibility).
     */
    public function senderTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_account_id');
    }

    /**
     * Alias for receivedTransactions (for backward compatibility).
     */
    public function receiverTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_account_id');
    }

    /**
     * Get all transactions (sent and received).
     */
    public function transactions()
    {
        return Transaction::where('sender_account_id', $this->id)
            ->orWhere('receiver_account_id', $this->id)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if account has sufficient balance.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Debit the account.
     */
    public function debit(float $amount): bool
    {
        if (!$this->hasSufficientBalance($amount)) {
            return false;
        }

        $this->balance -= $amount;
        return $this->save();
    }

    /**
     * Credit the account.
     */
    public function credit(float $amount): bool
    {
        $this->balance += $amount;
        return $this->save();
    }
}
