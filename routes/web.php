<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Loan;
use App\Models\Notification;

Route::get('/', function () {
    return view('welcome');
});

// Banking System Dashboard Routes
Route::get('/dashboard', function () {
    $stats = [
        'total_customers' => User::where('role', 'customer')->count(),
        'total_accounts' => Account::count(),
        'total_transactions' => Transaction::count(),
        'total_loans' => Loan::count(),
        'total_balance' => Account::sum('balance'),
        'active_loans' => Loan::where('status', 'active')->count(),
        'pending_transactions' => Transaction::where('status', 'pending')->count(),
    ];

    return response()->json([
        'message' => 'ZeroCash Banking System Dashboard',
        'statistics' => $stats,
        'features_implemented' => [
            '✅ Automatic loan-to-account transfers',
            '✅ Automatic transaction fee calculation',
            '✅ Employee/Admin deposit controls',
            '✅ Automatic notifications',
            '✅ Dynamic interest rate algorithm',
            '✅ Auto account number generation',
            '✅ Auto employee password generation',
            '✅ Auto customer password generation',
            '✅ NIDA integration for customer creation',
            '✅ Employee soft deletion system',
        ]
    ]);
})->name('dashboard');

// API Routes for testing
Route::prefix('api')->group(function () {

    // Customer Management
    Route::get('/customers', function () {
        return User::where('role', 'customer')
            ->with(['accounts', 'notifications'])
            ->get();
    });

    // Account Management
    Route::get('/accounts', function () {
        return Account::with(['user', 'accountType', 'branch'])
            ->get();
    });

    // Transaction Management
    Route::get('/transactions', function () {
        return Transaction::with(['senderAccount', 'receiverAccount', 'transactionType'])
            ->latest()
            ->take(50)
            ->get();
    });

    // Loan Management
    Route::get('/loans', function () {
        return Loan::with(['account.user', 'loanType', 'branch'])
            ->latest()
            ->get();
    });

    // Notifications
    Route::get('/notifications', function () {
        return Notification::with('user')
            ->latest()
            ->take(50)
            ->get();
    });

    // Test NIDA Integration
    Route::post('/test-nida', function () {
        $nidaService = new \App\Services\NidaService();
        $nidaNumber = request('nida_number', '19900107-23106-00002-99');

        return $nidaService->createCustomerFromNida($nidaNumber, [
            'email' => 'test@zerocash.com',
            'phone' => '+255700000000'
        ]);
    });

    // Test Transaction
    Route::post('/test-transaction', function () {
        $accounts = Account::take(2)->get();
        $transferType = \App\Models\TransactionType::where('code', 'TRF')->first();

        if ($accounts->count() < 2) {
            return response()->json(['error' => 'Need at least 2 accounts for transfer'], 400);
        }

        $transaction = Transaction::create([
            'sender_account_id' => $accounts[0]->id,
            'receiver_account_id' => $accounts[1]->id,
            'transaction_type_id' => $transferType->id,
            'amount' => request('amount', 10000),
            'description' => 'API Test Transfer',
        ]);

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction,
            'fee_calculated' => $transaction->fee_amount,
            'total_amount' => $transaction->total_amount,
        ]);
    });

    // Test Loan
    Route::post('/test-loan', function () {
        $customer = User::where('role', 'customer')->first();
        $account = $customer->accounts()->first();
        $loanType = \App\Models\LoanType::where('code', 'PL')->first();

        $loan = Loan::create([
            'account_id' => $account->id,
            'loan_type_id' => $loanType->id,
            'branch_id' => 1,
            'principal_amount' => request('amount', 500000),
            'term_months' => request('term', 12),
        ]);

        return response()->json([
            'message' => 'Loan created successfully',
            'loan' => $loan,
            'interest_rate_calculated' => $loan->interest_rate,
            'monthly_payment' => $loan->monthly_payment,
        ]);
    });
});
