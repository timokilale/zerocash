<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Loan;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerDashboardController extends Controller
{
    /**
     * Show the customer dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Get customer accounts
        $accounts = Account::where('user_id', $user->id)
            ->with('accountType')
            ->get();

        // Get recent transactions
        $recentTransactions = Transaction::where(function ($query) use ($accounts) {
                $accountIds = $accounts->pluck('id');
                $query->whereIn('sender_account_id', $accountIds)
                      ->orWhereIn('receiver_account_id', $accountIds);
            })
            ->with(['senderAccount', 'receiverAccount', 'transactionType'])
            ->latest()
            ->limit(10)
            ->get();

        // Get active loans (through accounts)
        $accountIds = $accounts->pluck('id');
        $activeLoans = Loan::whereIn('account_id', $accountIds)
            ->whereIn('status', ['approved', 'active'])
            ->with('loanType')
            ->get();

        // Calculate totals
        $totalBalance = $accounts->sum('balance');
        $totalLoans = $activeLoans->sum('principal_amount');
        $totalLoanBalance = $activeLoans->sum('outstanding_balance');

        // Get monthly transaction summary
        $monthlyData = $this->getMonthlyTransactionData($accounts);

        return view('customer.dashboard', compact(
            'accounts',
            'recentTransactions',
            'activeLoans',
            'totalBalance',
            'totalLoans',
            'totalLoanBalance',
            'monthlyData'
        ));
    }

    /**
     * Show transaction history with search and filters.
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();
        $accounts = Account::where('user_id', $user->id)->get();
        $accountIds = $accounts->pluck('id');

        $query = Transaction::where(function ($q) use ($accountIds) {
            $q->whereIn('sender_account_id', $accountIds)
              ->orWhereIn('receiver_account_id', $accountIds);
        })->with(['senderAccount', 'receiverAccount', 'transactionType']);

        // Apply filters
        if ($request->filled('account_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('sender_account_id', $request->account_id)
                  ->orWhere('receiver_account_id', $request->account_id);
            });
        }

        if ($request->filled('transaction_type')) {
            $query->whereHas('transactionType', function ($q) use ($request) {
                $q->where('code', $request->transaction_type);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(20);

        // Calculate summary for filtered results
        $summary = [
            'total_credit' => $query->clone()->whereIn('receiver_account_id', $accountIds)->sum('amount'),
            'total_debit' => $query->clone()->whereIn('sender_account_id', $accountIds)->sum('amount'),
            'total_fees' => $query->clone()->whereIn('sender_account_id', $accountIds)->sum('fee_amount'),
        ];

        return view('customer.transactions', compact('transactions', 'accounts', 'summary'));
    }

    /**
     * Show account details.
     */
    public function account(Account $account)
    {
        // Ensure customer can only view their own accounts
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to account');
        }

        $account->load('accountType');

        // Get account transactions
        $transactions = Transaction::where(function ($query) use ($account) {
                $query->where('sender_account_id', $account->id)
                      ->orWhere('receiver_account_id', $account->id);
            })
            ->with(['senderAccount', 'receiverAccount', 'transactionType'])
            ->latest()
            ->paginate(15);

        // Calculate monthly balance trend
        $balanceTrend = $this->getAccountBalanceTrend($account);

        return view('customer.account', compact('account', 'transactions', 'balanceTrend'));
    }

    /**
     * Get monthly transaction data for charts.
     */
    private function getMonthlyTransactionData($accounts)
    {
        $accountIds = $accounts->pluck('id');
        $months = [];
        $credits = [];
        $debits = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $monthlyCredit = Transaction::whereIn('receiver_account_id', $accountIds)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $monthlyDebit = Transaction::whereIn('sender_account_id', $accountIds)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $credits[] = $monthlyCredit;
            $debits[] = $monthlyDebit;
        }

        return [
            'months' => $months,
            'credits' => $credits,
            'debits' => $debits,
        ];
    }

    /**
     * Get account balance trend over time.
     */
    private function getAccountBalanceTrend($account)
    {
        $transactions = Transaction::where(function ($query) use ($account) {
                $query->where('sender_account_id', $account->id)
                      ->orWhere('receiver_account_id', $account->id);
            })
            ->orderBy('created_at')
            ->get();

        $balanceHistory = [];
        $runningBalance = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->sender_account_id == $account->id) {
                // Debit transaction
                $runningBalance -= ($transaction->amount + $transaction->fee_amount);
            } else {
                // Credit transaction
                $runningBalance += $transaction->amount;
            }

            $balanceHistory[] = [
                'date' => $transaction->created_at->format('Y-m-d'),
                'balance' => $runningBalance,
                'description' => $transaction->description,
            ];
        }

        return array_slice($balanceHistory, -30); // Last 30 transactions
    }

    /**
     * Show format selection for bank statement.
     */
    public function showStatementOptions(Request $request)
    {
        return view('customer.statement-options', [
            'filters' => $request->all()
        ]);
    }

    /**
     * Print transaction statement in selected format.
     */
    public function printStatement(Request $request)
    {
        $user = auth()->user();
        $accounts = Account::where('user_id', $user->id)->get();
        $accountIds = $accounts->pluck('id');

        // Apply same filters as the transactions page
        $query = Transaction::where(function ($q) use ($accountIds) {
            $q->whereIn('sender_account_id', $accountIds)
              ->orWhereIn('receiver_account_id', $accountIds);
        })->with(['senderAccount', 'receiverAccount', 'transactionType']);

        // Apply filters from request
        if ($request->filled('account_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('sender_account_id', $request->account_id)
                  ->orWhere('receiver_account_id', $request->account_id);
            });
        }

        if ($request->filled('transaction_type')) {
            $query->whereHas('transactionType', function ($q) use ($request) {
                $q->where('code', $request->transaction_type);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->oldest()->get(); // Get oldest first for proper balance calculation

        // Get current account balances
        $currentBalances = $accounts->pluck('balance', 'id');
        $totalCurrentBalance = $currentBalances->sum();

        // Calculate running balance for each transaction
        $transactionsWithBalance = collect();
        $runningBalances = $accounts->pluck('balance', 'id')->toArray(); // Start with current balances

        // Work backwards from current balance to calculate historical balances
        $reversedTransactions = $transactions->reverse();

        foreach ($reversedTransactions as $transaction) {
            $isCredit = in_array($transaction->receiver_account_id, $accountIds->toArray());
            $isDebit = in_array($transaction->sender_account_id, $accountIds->toArray());

            if ($isDebit) {
                // This was a debit, so add it back to get the balance before this transaction
                $runningBalances[$transaction->sender_account_id] += ($transaction->amount + $transaction->fee_amount);
            }
            if ($isCredit) {
                // This was a credit, so subtract it to get the balance before this transaction
                $runningBalances[$transaction->receiver_account_id] -= $transaction->amount;
            }
        }

        // Now process transactions forward with correct starting balances
        foreach ($transactions as $transaction) {
            $isCredit = in_array($transaction->receiver_account_id, $accountIds->toArray());
            $isDebit = in_array($transaction->sender_account_id, $accountIds->toArray());

            $balanceChange = 0;
            $accountId = null;

            if ($isDebit) {
                $balanceChange = -($transaction->amount + $transaction->fee_amount);
                $accountId = $transaction->sender_account_id;
                $runningBalances[$accountId] += $balanceChange;
            } elseif ($isCredit) {
                $balanceChange = $transaction->amount;
                $accountId = $transaction->receiver_account_id;
                $runningBalances[$accountId] += $balanceChange;
            }

            $transaction->balance_change = $balanceChange;
            $transaction->running_balance = $accountId ? $runningBalances[$accountId] : 0;
            $transaction->account_id = $accountId;

            $transactionsWithBalance->push($transaction);
        }

        // Calculate summary
        $totalCredit = $transactionsWithBalance->filter(function ($transaction) use ($accountIds) {
            return in_array($transaction->receiver_account_id, $accountIds->toArray());
        })->sum('amount');

        $totalDebit = $transactionsWithBalance->filter(function ($transaction) use ($accountIds) {
            return in_array($transaction->sender_account_id, $accountIds->toArray());
        })->sum(function ($transaction) {
            return $transaction->amount + $transaction->fee_amount;
        });

        $totalFees = $transactionsWithBalance->filter(function ($transaction) use ($accountIds) {
            return in_array($transaction->sender_account_id, $accountIds->toArray());
        })->sum('fee_amount');

        // Prepare data for Excel export with header information
        $exportData = collect();

        // Add header information
        $exportData->push([
            'ZEROCASH BANKING SYSTEM - BANK STATEMENT',
            '', '', '', '', '', '', '', '', ''
        ]);
        $exportData->push(['', '', '', '', '', '', '', '', '', '']);
        $exportData->push([
            'Customer Name:', $user->first_name . ' ' . $user->last_name,
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Customer ID:', $user->customer_id ?? $user->username,
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Statement Period:',
            $transactionsWithBalance->first() ? $transactionsWithBalance->first()->created_at->format('Y-m-d') : 'N/A',
            'to',
            $transactionsWithBalance->last() ? $transactionsWithBalance->last()->created_at->format('Y-m-d') : 'N/A',
            '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Statement Date:', now()->format('Y-m-d H:i:s'),
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push(['', '', '', '', '', '', '', '', '', '']);

        // Add account information
        $exportData->push([
            'ACCOUNT INFORMATION',
            '', '', '', '', '', '', '', '', ''
        ]);
        foreach ($accounts as $account) {
            $exportData->push([
                'Account Number:', $account->account_number,
                'Type:', $account->accountType->name,
                'Current Balance:', 'TSh ' . number_format($account->balance, 2),
                '', '', '', ''
            ]);
        }
        $exportData->push([
            'Total Available Balance:', 'TSh ' . number_format($totalCurrentBalance, 2),
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push(['', '', '', '', '', '', '', '', '', '']);

        // Add summary
        $exportData->push([
            'TRANSACTION SUMMARY',
            '', '', '', '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Total Credits:', 'TSh ' . number_format($totalCredit, 2),
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Total Debits:', 'TSh ' . number_format($totalDebit, 2),
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Total Fees Charged:', 'TSh ' . number_format($totalFees, 2),
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push([
            'Net Change:', 'TSh ' . number_format($totalCredit - $totalDebit, 2),
            '', '', '', '', '', '', '', ''
        ]);
        $exportData->push(['', '', '', '', '', '', '', '', '', '']);

        // Add column headers for transactions
        $exportData->push([
            'Date', 'Transaction #', 'Description', 'Reference', 'Debit (TSh)',
            'Credit (TSh)', 'Fee (TSh)', 'Running Balance (TSh)', 'Account', 'Status'
        ]);

        // Add transaction data with running balance
        foreach ($transactionsWithBalance as $transaction) {
            $isCredit = in_array($transaction->receiver_account_id, $accountIds->toArray());
            $isDebit = in_array($transaction->sender_account_id, $accountIds->toArray());

            $debitAmount = '';
            $creditAmount = '';

            if ($isDebit) {
                $debitAmount = number_format($transaction->amount + $transaction->fee_amount, 2);
            } elseif ($isCredit) {
                $creditAmount = number_format($transaction->amount, 2);
            }

            // Get account number for the affected account
            $affectedAccount = $accounts->firstWhere('id', $transaction->account_id);
            $accountNumber = $affectedAccount ? $affectedAccount->account_number : 'N/A';

            $exportData->push([
                $transaction->created_at->format('Y-m-d H:i'),
                $transaction->transaction_number,
                $transaction->description,
                $transaction->reference ?? '-',
                $debitAmount,
                $creditAmount,
                $isDebit ? number_format($transaction->fee_amount, 2) : '-',
                number_format($transaction->running_balance, 2),
                $accountNumber,
                ucfirst($transaction->status)
            ]);
        }

        // Add footer with current balances
        $exportData->push(['', '', '', '', '', '', '', '', '', '']);
        $exportData->push([
            'CURRENT ACCOUNT BALANCES',
            '', '', '', '', '', '', '', '', ''
        ]);
        foreach ($accounts as $account) {
            $exportData->push([
                $account->account_number,
                $account->accountType->name,
                '',
                '',
                '',
                '',
                '',
                'TSh ' . number_format($account->balance, 2),
                '',
                $account->status === 'active' ? 'Active' : 'Inactive'
            ]);
        }
        $exportData->push(['', '', '', '', '', '', '', '', '', '']);
        $exportData->push([
            'TOTAL AVAILABLE BALANCE:',
            'TSh ' . number_format($totalCurrentBalance, 2),
            '', '', '', '', '', '', '', ''
        ]);

        // Determine format
        $format = $request->get('format', 'excel'); // Default to excel for backward compatibility

        if ($format === 'pdf') {
            return $this->generatePdfStatement($user, $accounts, $accountIds, $transactionsWithBalance, $totalCurrentBalance, $totalCredit, $totalDebit, $totalFees);
        } else {
            return $this->generateExcelStatement($user, $exportData);
        }
    }

    /**
     * Generate PDF bank statement.
     */
    private function generatePdfStatement($user, $accounts, $accountIds, $transactionsWithBalance, $totalCurrentBalance, $totalCredit, $totalDebit, $totalFees)
    {
        $data = [
            'user' => $user,
            'accounts' => $accounts,
            'accountIds' => $accountIds,
            'transactionsWithBalance' => $transactionsWithBalance,
            'totalCurrentBalance' => $totalCurrentBalance,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'totalFees' => $totalFees
        ];

        $pdf = Pdf::loadView('customer.bank-statement-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'ZeroCash_Bank_Statement_' . $user->username . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate Excel bank statement.
     */
    private function generateExcelStatement($user, $exportData)
    {
        $filename = 'ZeroCash_Bank_Statement_' . $user->username . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return (new FastExcel($exportData))->download($filename);
    }
}
