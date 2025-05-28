<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\Account;
use App\Models\User;

class DepositController extends Controller
{
    /**
     * Display a listing of deposits.
     */
    public function index()
    {
        $deposits = Deposit::with(['account.user', 'depositedBy'])
            ->latest()
            ->paginate(20);

        return view('banking.deposits.index', compact('deposits'));
    }

    /**
     * Show the form for creating a new deposit.
     */
    public function create()
    {
        $accounts = Account::with(['user', 'accountType'])
            ->where('status', 'active')
            ->get();

        return view('banking.deposits.create', compact('accounts'));
    }

    /**
     * Store a newly created deposit.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'deposit_method' => 'required|in:cash,check,transfer,online',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $deposit = Deposit::create([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'deposited_by' => auth()->id(),
                'deposit_method' => $request->deposit_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            return redirect()->route('deposits.show', $deposit)
                ->with('success', 'Deposit created successfully. Awaiting authorization.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create deposit: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified deposit.
     */
    public function show(Deposit $deposit)
    {
        $deposit->load(['account.user', 'depositedBy']);
        return view('banking.deposits.show', compact('deposit'));
    }

    /**
     * Show the form for editing the specified deposit.
     */
    public function edit(Deposit $deposit)
    {
        // Only allow editing pending deposits
        if ($deposit->status !== 'pending') {
            return redirect()->route('deposits.show', $deposit)
                ->withErrors(['error' => 'Cannot edit a deposit that has been processed.']);
        }

        $accounts = Account::with(['user', 'accountType'])
            ->where('status', 'active')
            ->get();

        return view('banking.deposits.edit', compact('deposit', 'accounts'));
    }

    /**
     * Update the specified deposit.
     */
    public function update(Request $request, Deposit $deposit)
    {
        // Only allow updating pending deposits
        if ($deposit->status !== 'pending') {
            return redirect()->route('deposits.show', $deposit)
                ->withErrors(['error' => 'Cannot update a deposit that has been processed.']);
        }

        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'deposit_method' => 'required|in:cash,check,transfer,online',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $deposit->update([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'deposit_method' => $request->deposit_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            return redirect()->route('deposits.show', $deposit)
                ->with('success', 'Deposit updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update deposit: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified deposit.
     */
    public function destroy(Deposit $deposit)
    {
        // Only allow deleting pending deposits
        if ($deposit->status !== 'pending') {
            return redirect()->route('deposits.index')
                ->withErrors(['error' => 'Cannot delete a deposit that has been processed.']);
        }

        try {
            $deposit->delete();
            return redirect()->route('deposits.index')
                ->with('success', 'Deposit deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete deposit: ' . $e->getMessage()]);
        }
    }

    /**
     * Authorize and process the deposit.
     */
    public function authorize(Deposit $deposit)
    {
        if ($deposit->status !== 'pending') {
            return back()->withErrors(['error' => 'Deposit has already been processed.']);
        }

        try {
            if ($deposit->process()) {
                return redirect()->route('deposits.show', $deposit)
                    ->with('success', 'Deposit authorized and processed successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to process deposit.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to authorize deposit: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject the deposit.
     */
    public function reject(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'pending') {
            return back()->withErrors(['error' => 'Deposit has already been processed.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $deposit->update([
                'status' => 'cancelled',
                'notes' => ($deposit->notes ? $deposit->notes . "\n\n" : '') . 
                          "REJECTED: " . $request->rejection_reason,
            ]);

            return redirect()->route('deposits.show', $deposit)
                ->with('success', 'Deposit rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reject deposit: ' . $e->getMessage()]);
        }
    }
}
