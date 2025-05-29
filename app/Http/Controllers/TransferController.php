<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * Show the transfer form.
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->role === 'customer') {
            // Customer can only transfer from their own accounts
            $accounts = Account::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('accountType')
                ->get();

            // Return customer-specific view
            return view('customer.transfers.create', compact('accounts'));
        } else {
            // Employees can transfer from any account
            $accounts = Account::where('status', 'active')
                ->with(['user', 'accountType'])
                ->get();

            // Return admin/employee view
            return view('banking.transfers.create', compact('accounts'));
        }
    }

    /**
     * Process the transfer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $user = auth()->user();

            // Additional security: Customers can only transfer from their own accounts
            if ($user->role === 'customer') {
                $fromAccount = Account::where('id', $request->from_account_id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$fromAccount) {
                    return back()->withErrors(['from_account_id' => 'You can only transfer from your own accounts.'])
                        ->withInput();
                }
            }

            // Auto-generate reference number
            $referenceNumber = Transaction::generateReferenceNumber();

            $result = $this->processTransfer(
                $request->from_account_id,
                $request->to_account_number,
                $request->amount,
                $request->description,
                $referenceNumber
            );

            if ($result['success']) {
                // Redirect based on user role
                if ($user->role === 'customer') {
                    return redirect()->route('customer.dashboard')
                        ->with('success', 'Transfer completed successfully! Reference: ' . $referenceNumber);
                } else {
                    return redirect()->route('transactions.show', $result['transaction'])
                        ->with('success', 'Transfer completed successfully!');
                }
            } else {
                return back()->withErrors(['error' => $result['message']])
                    ->withInput();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Process the actual transfer logic.
     */
    private function processTransfer($fromAccountId, $toAccountNumber, $amount, $description = null, $reference = null)
    {
        return DB::transaction(function () use ($fromAccountId, $toAccountNumber, $amount, $description, $reference) {
            // Get sender account
            $fromAccount = Account::find($fromAccountId);
            if (!$fromAccount) {
                return ['success' => false, 'message' => 'Sender account not found'];
            }

            // Get receiver account by account number
            $toAccount = Account::where('account_number', $toAccountNumber)->first();
            if (!$toAccount) {
                return ['success' => false, 'message' => 'Receiver account not found'];
            }

            // Check if trying to transfer to same account
            if ($fromAccount->id === $toAccount->id) {
                return ['success' => false, 'message' => 'Cannot transfer to the same account'];
            }

            // Check account statuses
            if ($fromAccount->status !== 'active') {
                return ['success' => false, 'message' => 'Sender account is not active'];
            }

            if ($toAccount->status !== 'active') {
                return ['success' => false, 'message' => 'Receiver account is not active'];
            }

            // Get transfer transaction type
            $transactionType = TransactionType::where('code', 'TRF')->first();
            if (!$transactionType) {
                return ['success' => false, 'message' => 'Transfer transaction type not configured'];
            }

            // Calculate fee
            $fee = Transaction::calculateFee($amount, $transactionType->id);
            $totalAmount = $amount + $fee;

            // Check if sender has sufficient balance
            if (!$fromAccount->hasSufficientBalance($totalAmount)) {
                return ['success' => false, 'message' => 'Insufficient balance (including transfer fee)'];
            }

            // Create transaction record
            $transaction = Transaction::create([
                'sender_account_id' => $fromAccount->id,
                'receiver_account_id' => $toAccount->id,
                'transaction_type_id' => $transactionType->id,
                'amount' => $amount,
                'fee' => $fee,
                'description' => $description ?: "Transfer to {$toAccount->account_number}",
                'reference' => $reference,
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Debit sender account (amount + fee)
            $fromAccount->debit($totalAmount);

            // Credit receiver account (amount only, no fee)
            $toAccount->credit($amount);

            return [
                'success' => true,
                'message' => 'Transfer completed successfully',
                'transaction' => $transaction
            ];
        });
    }

    /**
     * Get account details by account number (AJAX).
     */
    public function getAccountDetails(Request $request)
    {
        try {
            $accountNumber = $request->get('account_number');

            if (!$accountNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number is required'
                ]);
            }

            $account = Account::where('account_number', $accountNumber)
                ->with(['user', 'accountType'])
                ->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found'
                ]);
            }

            if ($account->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not active'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Account verified successfully',
                'account' => [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'account_type' => [
                        'name' => $account->accountType->name,
                        'code' => $account->accountType->code ?? 'N/A'
                    ],
                    'user' => [
                        'name' => $account->user->first_name . ' ' . $account->user->last_name,
                        'first_name' => $account->user->first_name,
                        'last_name' => $account->user->last_name
                    ],
                    'branch' => [
                        'name' => $account->branch->name ?? 'N/A',
                        'code' => $account->branch->code ?? 'N/A'
                    ],
                    'status' => $account->status,
                    'opened_date' => $account->opened_date->format('M d, Y'),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Account verification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the account'
            ], 500);
        }
    }
}
