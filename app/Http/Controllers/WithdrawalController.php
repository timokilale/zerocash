<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\Account;
use App\Models\User;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawals.
     */
    public function index()
    {
        $withdrawals = Withdrawal::with(['account.user', 'withdrawnBy'])
            ->latest()
            ->paginate(20);

        return view('banking.withdrawals.index', compact('withdrawals'));
    }

    /**
     * Show the form for creating a new withdrawal.
     */
    public function create()
    {
        $accounts = Account::with(['user', 'accountType'])
            ->where('status', 'active')
            ->get();

        return view('banking.withdrawals.create', compact('accounts'));
    }

    /**
     * Store a newly created withdrawal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'withdrawal_method' => 'required|in:cash,check,atm,online',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $withdrawal = Withdrawal::create([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'withdrawn_by' => auth()->id(),
                'withdrawal_method' => $request->withdrawal_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            return redirect()->route('withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal created successfully. Awaiting authorization.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create withdrawal: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified withdrawal.
     */
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['account.user', 'withdrawnBy']);
        return view('banking.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Show the form for editing the specified withdrawal.
     */
    public function edit(Withdrawal $withdrawal)
    {
        // Only allow editing pending withdrawals
        if ($withdrawal->status !== 'pending') {
            return redirect()->route('withdrawals.show', $withdrawal)
                ->withErrors(['error' => 'Cannot edit a withdrawal that has been processed.']);
        }

        $accounts = Account::with(['user', 'accountType'])
            ->where('status', 'active')
            ->get();

        return view('banking.withdrawals.edit', compact('withdrawal', 'accounts'));
    }

    /**
     * Update the specified withdrawal.
     */
    public function update(Request $request, Withdrawal $withdrawal)
    {
        // Only allow updating pending withdrawals
        if ($withdrawal->status !== 'pending') {
            return redirect()->route('withdrawals.show', $withdrawal)
                ->withErrors(['error' => 'Cannot update a withdrawal that has been processed.']);
        }

        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'withdrawal_method' => 'required|in:cash,check,atm,online',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $withdrawal->update([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'withdrawal_method' => $request->withdrawal_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            return redirect()->route('withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update withdrawal: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified withdrawal from storage.
     */
    public function destroy(Withdrawal $withdrawal)
    {
        // Only allow deleting pending withdrawals
        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'Cannot delete a withdrawal that has been processed.']);
        }

        try {
            $withdrawal->delete();
            return redirect()->route('withdrawals.index')
                ->with('success', 'Withdrawal deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete withdrawal: ' . $e->getMessage()]);
        }
    }

    /**
     * Authorize and process the withdrawal.
     */
    public function authorize(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'Withdrawal has already been processed.']);
        }

        try {
            if ($withdrawal->process()) {
                return redirect()->route('withdrawals.show', $withdrawal)
                    ->with('success', 'Withdrawal authorized and processed successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to process withdrawal.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to authorize withdrawal: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject the withdrawal.
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'Withdrawal has already been processed.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $withdrawal->update([
                'status' => 'cancelled',
                'notes' => ($withdrawal->notes ? $withdrawal->notes . "\n\n" : '') .
                          "REJECTED: " . $request->rejection_reason,
            ]);

            return redirect()->route('withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reject withdrawal: ' . $e->getMessage()]);
        }
    }
}
