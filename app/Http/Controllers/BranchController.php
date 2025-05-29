<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Check if user has admin access for branch management.
     */
    private function checkAdminAccess()
    {
        if (!auth()->user() || !in_array(auth()->user()->role, ['admin', 'root'])) {
            abort(403, 'Access denied. Only administrators can manage branches.');
        }
    }

    /**
     * Display a listing of branches.
     */
    public function index()
    {
        $branches = Branch::with(['manager', 'employees', 'accounts', 'loans'])
            ->withCount(['employees', 'accounts', 'loans'])
            ->latest()
            ->paginate(20);

        return view('banking.branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        $this->checkAdminAccess();

        // Get potential managers (admin and staff users)
        $managers = User::whereIn('role', ['admin', 'staff'])
            ->where('status', 'active')
            ->get();

        return view('banking.branches.create', compact('managers'));
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        $this->checkAdminAccess();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'code' => 'required|string|max:10|unique:branches,code',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        try {
            $branch = Branch::create($validated);

            return redirect()->route('branches.show', $branch->id)
                ->with('success', 'Branch created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create branch: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified branch.
     */
    public function show(string $id)
    {
        $branch = Branch::with(['manager', 'employees.user', 'accounts.user', 'loans.account.user'])
            ->withCount(['employees', 'accounts', 'loans'])
            ->findOrFail($id);

        return view('banking.branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(string $id)
    {
        $this->checkAdminAccess();

        $branch = Branch::findOrFail($id);

        // Get potential managers (admin and staff users)
        $managers = User::whereIn('role', ['admin', 'staff'])
            ->where('status', 'active')
            ->get();

        return view('banking.branches.edit', compact('branch', 'managers'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->checkAdminAccess();

        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($branch->id)],
            'code' => ['required', 'string', 'max:10', Rule::unique('branches')->ignore($branch->id)],
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        try {
            $branch->update($validated);

            return redirect()->route('branches.show', $branch->id)
                ->with('success', 'Branch updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update branch: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy(string $id)
    {
        $this->checkAdminAccess();

        $branch = Branch::findOrFail($id);

        // Check if branch has associated records
        if ($branch->employees()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete branch with active employees. Please reassign employees first.']);
        }

        if ($branch->accounts()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete branch with active accounts. Please transfer accounts first.']);
        }

        if ($branch->loans()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete branch with active loans. Please transfer loans first.']);
        }

        try {
            $branch->delete();

            return redirect()->route('branches.index')
                ->with('success', 'Branch deleted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete branch: ' . $e->getMessage()]);
        }
    }
}
