<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'manager_id',
    ];

    /**
     * Get the manager of the branch.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the employees working at this branch.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the accounts maintained by this branch.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the loans offered by this branch.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
