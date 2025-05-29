<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Branch;
use App\Models\Notification;
use App\Services\NidaService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->with(['accounts.accountType', 'accounts.branch'])
            ->latest()
            ->paginate(20);

        return view('banking.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        $accountTypes = AccountType::all();
        $branches = Branch::all();

        return view('banking.customers.create', compact('accountTypes', 'branches'));
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'nida_number' => 'nullable|string|unique:users,nida',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'create_account' => 'nullable|boolean',
            'account_type_id' => 'required_if:create_account,1|exists:account_types,id',
            'branch_id' => 'required_if:create_account,1|exists:branches,id',
            'initial_deposit' => 'nullable|numeric|min:0',
        ]);

        try {
            $customer = null;
            $account = null;

            DB::transaction(function () use ($request, &$customer, &$account) {
                // Generate username and password
                $username = User::generateUsername($request->first_name, $request->last_name);
                $password = User::generatePassword();

                // Create customer
                $customer = User::create([
                    'username' => $username,
                    'email' => $request->email,
                    'password' => Hash::make($password),
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'nida' => $request->nida_number,
                    'date_of_birth' => $request->date_of_birth,
                    'role' => 'customer',
                    'status' => 'active',
                    'password_auto_generated' => true,
                    'nida_verified' => !empty($request->nida_number),
                ]);

                // Create account if requested
                if ($request->create_account) {
                    $account = Account::create([
                        'user_id' => $customer->id,
                        'account_type_id' => $request->account_type_id,
                        'branch_id' => $request->branch_id,
                        'balance' => $request->initial_deposit ?? 0,
                        'status' => 'active',
                    ]);
                }

                // Create welcome notification
                $message = "Welcome {$customer->first_name}! Your customer profile has been created. Username: {$username}, Password: {$password}";
                if ($account) {
                    $message .= " Account Number: {$account->account_number}";
                }

                Notification::create([
                    'user_id' => $customer->id,
                    'title' => 'Welcome to ZeroCash Banking',
                    'message' => $message,
                    'type' => 'account',
                    'data' => [
                        'account_id' => $account?->id,
                        'username' => $username,
                        'password' => $password,
                    ],
                ]);
            });

            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create customer: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(string $id)
    {
        $customer = User::where('role', 'customer')
            ->with(['accounts.accountType', 'accounts.branch', 'notifications'])
            ->findOrFail($id);

        return view('banking.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the customer.
     */
    public function edit(string $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $accountTypes = AccountType::all();
        $branches = Branch::all();

        return view('banking.customers.edit', compact('customer', 'accountTypes', 'branches'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, string $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $customer->id,
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'nida_number' => 'nullable|string|unique:users,nida,' . $customer->id,
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'status' => 'required|in:active,inactive,suspended',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = $request->only([
            'first_name', 'last_name', 'username', 'email', 'phone', 'address',
            'date_of_birth', 'gender', 'status'
        ]);

        // Map nida_number to nida for database
        if ($request->filled('nida_number')) {
            $updateData['nida'] = $request->nida_number;
        }

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['password_auto_generated'] = false;
        }

        // Update NIDA verification status
        if ($request->filled('nida_number')) {
            $updateData['nida_verified'] = true;
        }

        $customer->update($updateData);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified customer and all associated data.
     */
    public function destroy(string $id)
    {
        // Only allow admin and root users to delete customers
        if (!in_array(auth()->user()->role, ['admin', 'root'])) {
            return back()->withErrors(['error' => 'Unauthorized. Only administrators can delete customers.']);
        }

        $customer = User::where('role', 'customer')->findOrFail($id);

        try {
            \DB::transaction(function () use ($customer) {
                // Get all customer accounts
                $accounts = $customer->accounts;

                foreach ($accounts as $account) {
                    // Delete all transactions related to this account
                    \App\Models\Transaction::where('sender_account_id', $account->id)
                        ->orWhere('receiver_account_id', $account->id)
                        ->delete();

                    // Delete all loans related to this account
                    \App\Models\Loan::where('account_id', $account->id)->delete();

                    // Delete all deposits related to this account
                    \App\Models\Deposit::where('account_id', $account->id)->delete();

                    // Delete the account
                    $account->delete();
                }

                // Delete all notifications for this customer
                $customer->notifications()->delete();

                // Delete the customer
                $customer->delete();
            });

            return redirect()->route('customers.index')
                ->with('success', 'Customer and all associated data deleted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete customer: ' . $e->getMessage()]);
        }
    }

    /**
     * Create customer from NIDA.
     */
    public function createFromNida(Request $request)
    {
        $request->validate([
            'nida_number' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'account_type_id' => 'required|exists:account_types,id',
            'branch_id' => 'required|exists:branches,id',
            'initial_deposit' => 'nullable|numeric|min:0',
        ]);

        try {
            $nidaService = new NidaService();
            $result = $nidaService->createCustomerFromNida($request->nida_number, [
                'email' => $request->email,
                'phone' => $request->phone,
                'account_type_id' => $request->account_type_id,
                'branch_id' => $request->branch_id,
                'initial_deposit' => $request->initial_deposit ?? 0,
            ]);

            if ($result['success']) {
                return redirect()->route('customers.show', $result['customer']['id'])
                    ->with('success', 'Customer created from NIDA successfully!');
            } else {
                return back()->withErrors(['error' => $result['message']])->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create customer from NIDA: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Create an account for an existing customer.
     */
    public function createAccount(Request $request, string $customerId)
    {
        $customer = User::where('role', 'customer')->findOrFail($customerId);

        $request->validate([
            'account_type_id' => 'required|exists:account_types,id',
            'branch_id' => 'required|exists:branches,id',
            'initial_deposit' => 'nullable|numeric|min:0',
        ]);

        try {
            $account = Account::create([
                'user_id' => $customer->id,
                'account_type_id' => $request->account_type_id,
                'branch_id' => $request->branch_id,
                'balance' => $request->initial_deposit ?? 0,
                'status' => 'active',
            ]);

            // Create notification
            Notification::create([
                'user_id' => $customer->id,
                'title' => 'New Account Created',
                'message' => "A new account has been created for you. Account Number: {$account->account_number}",
                'type' => 'account',
                'data' => [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                ],
            ]);

            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Account created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create account: ' . $e->getMessage()]);
        }
    }
}
