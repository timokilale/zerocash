<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index()
    {
        $transactions = Transaction::with([
            'senderAccount.user',
            'receiverAccount.user',
            'transactionType'
        ])
        ->latest()
        ->paginate(20);

        return view('banking.transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $accounts = Account::with(['user', 'accountType'])->get();
        $transactionTypes = TransactionType::all();

        return view('banking.transactions.create', compact('accounts', 'transactionTypes'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'sender_account_id' => 'nullable|exists:accounts,id',
            'receiver_account_id' => 'nullable|exists:accounts,id',
            'reference' => 'nullable|string|max:100',
        ]);

        // Validate that at least one account is provided
        if (!$request->sender_account_id && !$request->receiver_account_id) {
            return back()->withErrors(['error' => 'At least one account (sender or receiver) must be specified.'])
                ->withInput();
        }

        try {
            $transaction = Transaction::create([
                'sender_account_id' => $request->sender_account_id,
                'receiver_account_id' => $request->receiver_account_id,
                'transaction_type_id' => $request->transaction_type_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'reference' => $request->reference,
                'status' => 'pending',
            ]);

            // Process the transaction
            if ($transaction->process(Auth::user())) {
                return redirect()->route('transactions.show', $transaction->id)
                    ->with('success', 'Transaction completed successfully!');
            } else {
                return back()->withErrors(['error' => 'Transaction failed to process.'])
                    ->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create transaction: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with([
            'senderAccount.user',
            'receiverAccount.user',
            'transactionType'
        ])->findOrFail($id);

        return view('banking.transactions.show', compact('transaction'));
    }

    /**
     * Transfer money between accounts.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'sender_account_id' => 'required|exists:accounts,id',
            'receiver_account_id' => 'required|exists:accounts,id|different:sender_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            $transferType = TransactionType::where('code', 'TRF')->first();

            if (!$transferType) {
                return back()->withErrors(['error' => 'Transfer transaction type not found.'])
                    ->withInput();
            }

            $transaction = Transaction::create([
                'sender_account_id' => $request->sender_account_id,
                'receiver_account_id' => $request->receiver_account_id,
                'transaction_type_id' => $transferType->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'reference' => $request->reference,
                'status' => 'pending',
            ]);

            if ($transaction->process(Auth::user())) {
                return redirect()->route('transactions.show', $transaction->id)
                    ->with('success', 'Transfer completed successfully!');
            } else {
                return back()->withErrors(['error' => 'Transfer failed. Please check account balances.'])
                    ->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to process transfer: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show transfer form.
     */
    public function showTransferForm()
    {
        $accounts = Account::with(['user', 'accountType'])->get();

        return view('banking.transactions.transfer', compact('accounts'));
    }

    /**
     * Get transaction fee calculation.
     */
    public function calculateFee(Request $request)
    {
        $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $fee = Transaction::calculateFee($request->amount, $request->transaction_type_id);
        $total = $request->amount + $fee;

        return response()->json([
            'fee' => $fee,
            'total' => $total,
            'formatted_fee' => 'TSh ' . number_format($fee, 2),
            'formatted_total' => 'TSh ' . number_format($total, 2),
        ]);
    }
}
