<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;
use App\Models\AccountType;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index()
    {
        $accounts = Account::with(['user', 'accountType', 'branch'])
            ->latest()
            ->paginate(20);

        return view('banking.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new account.
     */
    public function create()
    {
        $customers = User::where('role', 'customer')->get();
        $accountTypes = AccountType::all();
        $branches = Branch::all();

        return view('banking.accounts.create', compact('customers', 'accountTypes', 'branches'));
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'account_type_id' => 'required|exists:account_types,id',
            'branch_id' => 'required|exists:branches,id',
            'initial_deposit' => 'nullable|numeric|min:0',
        ]);

        try {
            $account = Account::create([
                'user_id' => $request->user_id,
                'account_type_id' => $request->account_type_id,
                'branch_id' => $request->branch_id,
                'balance' => $request->initial_deposit ?? 0,
                'status' => 'active',
            ]);

            // Create notification
            Notification::create([
                'user_id' => $request->user_id,
                'title' => 'New Account Created',
                'message' => "A new account has been created for you. Account Number: {$account->account_number}",
                'type' => 'account',
                'data' => [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                ],
            ]);

            return redirect()->route('accounts.show', $account->id)
                ->with('success', 'Account created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create account: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified account.
     */
    public function show(string $id)
    {
        $account = Account::with([
            'user',
            'accountType',
            'branch',
            'senderTransactions.transactionType',
            'receiverTransactions.transactionType'
        ])->findOrFail($id);

        return view('banking.accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the account.
     */
    public function edit(string $id)
    {
        $account = Account::findOrFail($id);
        $accountTypes = AccountType::all();
        $branches = Branch::all();

        return view('banking.accounts.edit', compact('account', 'accountTypes', 'branches'));
    }

    /**
     * Update the specified account.
     */
    public function update(Request $request, string $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'account_type_id' => 'required|exists:account_types,id',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:active,inactive,frozen,closed',
        ]);

        $account->update($request->only([
            'account_type_id', 'branch_id', 'status'
        ]));

        return redirect()->route('accounts.show', $account->id)
            ->with('success', 'Account updated successfully!');
    }

    /**
     * Remove the specified account.
     */
    public function destroy(string $id)
    {
        $account = Account::findOrFail($id);

        // Check if account has balance
        if ($account->balance > 0) {
            return back()->withErrors(['error' => 'Cannot delete account with positive balance.']);
        }

        // Check if account has pending transactions
        $pendingTransactions = Transaction::where(function($query) use ($account) {
            $query->where('sender_account_id', $account->id)
                  ->orWhere('receiver_account_id', $account->id);
        })->where('status', 'pending')->count();

        if ($pendingTransactions > 0) {
            return back()->withErrors(['error' => 'Cannot delete account with pending transactions.']);
        }

        $account->update(['status' => 'closed']);

        return redirect()->route('accounts.index')
            ->with('success', 'Account closed successfully!');
    }

    /**
     * Toggle account status.
     */
    public function toggleStatus(string $id)
    {
        $account = Account::findOrFail($id);

        $newStatus = $account->status === 'active' ? 'inactive' : 'active';
        $account->update(['status' => $newStatus]);

        // Create notification
        Notification::create([
            'user_id' => $account->user_id,
            'title' => 'Account Status Changed',
            'message' => "Your account status has been changed to {$newStatus}.",
            'type' => 'account',
            'data' => [
                'account_id' => $account->id,
                'new_status' => $newStatus,
            ],
        ]);

        return back()->with('success', "Account status changed to {$newStatus}!");
    }
}
