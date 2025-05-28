<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'employee_id',
        'position',
        'salary',
        'hire_date',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'hire_date' => 'date',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_id)) {
                $employee->employee_id = static::generateEmployeeId();
            }
        });
    }

    /**
     * Generate a unique employee ID.
     */
    public static function generateEmployeeId(): string
    {
        do {
            // Generate employee ID: EMP + YYYY + 4 random digits
            $employeeId = 'EMP' . date('Y') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('employee_id', $employeeId)->exists());

        return $employeeId;
    }

    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where the employee works.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Soft delete the employee (move to dormant state).
     */
    public function makeInactive()
    {
        $this->user->update(['status' => 'inactive']);
        $this->delete(); // Soft delete
    }

    /**
     * Restore the employee from dormant state.
     */
    public function makeActive()
    {
        $this->user->update(['status' => 'active']);
        $this->restore();
    }
}
