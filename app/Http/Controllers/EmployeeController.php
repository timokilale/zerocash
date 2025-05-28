<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index()
    {
        $employees = Employee::with(['user', 'branch'])
            ->withTrashed() // Include soft deleted employees
            ->latest()
            ->paginate(20);

        return view('banking.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $branches = Branch::all();
        return view('banking.employees.create', compact('branches'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
        ]);

        try {
            \DB::transaction(function () use ($request) {
                // Create user account for employee
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'role' => 'employee',
                    'password' => Hash::make(User::generateRandomPassword()),
                    'email_verified_at' => now(),
                ]);

                // Create employee record
                Employee::create([
                    'user_id' => $user->id,
                    'branch_id' => $request->branch_id,
                    'position' => $request->position,
                    'salary' => $request->salary,
                    'hire_date' => $request->hire_date,
                ]);
            });

            return redirect()->route('employees.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load(['user', 'branch']);
        return view('banking.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        $employee->load(['user', 'branch']);
        $branches = Branch::all();
        return view('banking.employees.edit', compact('employee', 'branches'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
        ]);

        try {
            \DB::transaction(function () use ($request, $employee) {
                // Update user information
                $employee->user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                ]);

                // Update employee information
                $employee->update([
                    'branch_id' => $request->branch_id,
                    'position' => $request->position,
                    'salary' => $request->salary,
                    'hire_date' => $request->hire_date,
                ]);
            });

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Soft delete the specified employee (make dormant).
     */
    public function destroy(Employee $employee)
    {
        try {
            $employee->delete(); // Soft delete
            return redirect()->route('employees.index')
                ->with('success', 'Employee marked as dormant successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark employee as dormant: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore a soft deleted employee.
     */
    public function restore($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->restore();
            
            return redirect()->route('employees.index')
                ->with('success', 'Employee restored successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to restore employee: ' . $e->getMessage()]);
        }
    }
}
