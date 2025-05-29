<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'address',
        'nida',
        'date_of_birth',
        'gender',
        'role',
        'status',
        'password_auto_generated',
        'nida_verified',
        'nida_data',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'password_auto_generated' => 'boolean',
            'nida_verified' => 'boolean',
            'nida_data' => 'array',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee(): bool
    {
        return in_array($this->role, ['employee', 'manager', 'admin']);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'root']);
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the accounts for the user.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the deposits made by the user.
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'deposited_by');
    }

    /**
     * Get the transactions processed by the user.
     */
    public function processedTransactions()
    {
        return $this->hasMany(Transaction::class, 'processed_by');
    }

    /**
     * Generate a random password for the user.
     */
    public static function generatePassword(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    /**
     * Generate a unique username based on first and last name.
     */
    public static function generateUsername(string $firstName, string $lastName): string
    {
        $baseUsername = strtolower($firstName . '.' . $lastName);
        $username = $baseUsername;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
