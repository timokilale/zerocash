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
     * Check if user has admin access for employee management.
     */
    private function checkAdminAccess()
    {
        if (!auth()->user() || !in_array(auth()->user()->role, ['admin', 'root'])) {
            abort(403, 'Access denied. Only administrators can manage employee records.');
        }
    }

    /**
     * Display a listing of employees.
     */
    public function index()
    {
        $this->checkAdminAccess();

        $employees = Employee::with(['user', 'branch'])
            ->withTrashed() // Include soft deleted employees
            ->latest()
            ->paginate(20);

        return view('banking.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(Request $request)
    {
        $this->checkAdminAccess();

        $branches = Branch::all();

        // Check if we're pre-filling from a branch manager
        $prefilledUser = null;
        $prefilledBranch = null;

        if ($request->has('user_id') && $request->has('branch_id')) {
            $prefilledUser = User::find($request->user_id);
            $prefilledBranch = Branch::find($request->branch_id);
        }

        return view('banking.employees.create', compact('branches', 'prefilledUser', 'prefilledBranch'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $this->checkAdminAccess();
        // Check if we're creating an employee record for an existing user
        if ($request->has('existing_user_id')) {
            $request->validate([
                'existing_user_id' => 'required|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
                'position' => 'required|string|max:255',
                'salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
            ]);

            try {
                $user = User::findOrFail($request->existing_user_id);

                // Check if user already has an employee record
                if ($user->employee) {
                    return back()->withErrors(['error' => 'This user already has an employee record.']);
                }

                // Create employee record for existing user
                Employee::create([
                    'user_id' => $user->id,
                    'branch_id' => $request->branch_id,
                    'position' => $request->position,
                    'salary' => $request->salary,
                    'hire_date' => $request->hire_date,
                ]);

                return redirect()->route('employees.index')
                    ->with('success', 'Employee record created successfully for ' . $user->first_name . ' ' . $user->last_name . '.');
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Failed to create employee record: ' . $e->getMessage()])
                    ->withInput();
            }
        } else {
            // Original logic for creating new user + employee
            $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'branch_id' => 'required|exists:branches,id',
                'position' => 'required|string|max:255',
                'salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            try {
                \DB::transaction(function () use ($request) {
                    // Generate username and password
                    $username = User::generateUsername($request->first_name, $request->last_name);
                    $password = User::generatePassword();

                    // Handle avatar upload
                    $avatarPath = null;
                    if ($request->hasFile('avatar')) {
                        $avatarPath = $request->file('avatar')->store('avatars', 'public');
                    }

                    // Create user account for employee
                    $user = User::create([
                        'username' => $username,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'nida' => 'EMP-' . time() . '-' . rand(1000, 9999), // Temporary NIDA for employees
                        'date_of_birth' => '1990-01-01', // Default date, can be updated later
                        'role' => 'employee',
                        'password' => Hash::make($password),
                        'password_auto_generated' => true,
                        'email_verified_at' => now(),
                        'avatar' => $avatarPath,
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
    }

    /**
     * Display the specified employee.
     */
    public function show($id)
    {
        $this->checkAdminAccess();

        // Find employee including soft-deleted ones
        $employee = Employee::withTrashed()
            ->with(['user', 'branch'])
            ->findOrFail($id);

        return view('banking.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $this->checkAdminAccess();

        // Find employee including soft-deleted ones
        $employee = Employee::withTrashed()
            ->with(['user', 'branch'])
            ->findOrFail($id);

        $branches = Branch::all();
        return view('banking.employees.edit', compact('employee', 'branches'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, $id)
    {
        $this->checkAdminAccess();

        // Find employee including soft-deleted ones
        $employee = Employee::withTrashed()->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        try {
            \DB::transaction(function () use ($request, $employee) {
                // Handle avatar upload/removal
                $avatarPath = $employee->user->avatar;

                // Remove avatar if requested
                if ($request->has('remove_avatar') && $request->remove_avatar) {
                    if ($avatarPath && \Storage::disk('public')->exists($avatarPath)) {
                        \Storage::disk('public')->delete($avatarPath);
                    }
                    $avatarPath = null;
                }

                // Upload new avatar if provided
                if ($request->hasFile('avatar')) {
                    // Delete old avatar if exists
                    if ($avatarPath && \Storage::disk('public')->exists($avatarPath)) {
                        \Storage::disk('public')->delete($avatarPath);
                    }

                    // Store new avatar
                    $avatarPath = $request->file('avatar')->store('avatars', 'public');
                }

                // Update user information
                $employee->user->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'avatar' => $avatarPath,
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
    public function destroy($id)
    {
        $this->checkAdminAccess();

        // Find employee including soft-deleted ones
        $employee = Employee::withTrashed()->findOrFail($id);

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
        $this->checkAdminAccess();

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
