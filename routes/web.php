<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Loan;
use App\Models\Notification;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $credentials = request()->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    if (Auth::attempt($credentials)) {
        request()->session()->regenerate();

        // Role-based redirect
        $user = Auth::user();
        if ($user->role === 'customer') {
            return redirect()->intended('/customer/dashboard');
        } else {
            return redirect()->intended('/banking-dashboard');
        }
    }

    return back()->withErrors([
        'username' => 'The provided credentials do not match our records.',
    ]);
})->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Legacy customer dashboard redirect
Route::get('/customer-dashboard', function () {
    return redirect()->route('customer.dashboard');
});

// Banking Dashboard (Protected - Admin/Staff only)
Route::get('/banking-dashboard', function () {
    if (!Auth::check() || Auth::user()->role === 'customer') {
        return redirect()->route('login');
    }

    $user = Auth::user();
    $stats = [
        'total_customers' => User::where('role', 'customer')->count(),
        'total_accounts' => Account::count(),
        'total_transactions' => Transaction::count(),
        'total_loans' => Loan::count(),
        'total_balance' => Account::sum('balance'),
        'active_loans' => Loan::where('status', 'active')->count(),
        'pending_transactions' => Transaction::where('status', 'pending')->count(),
    ];

    return view('banking.dashboard', compact('user', 'stats'));
})->name('banking.dashboard');

// Customer Routes (Customer role only) - MOVED TO SECURE SECTION BELOW

// Protected Banking Routes (Admin/Staff only)
Route::middleware(['auth', 'role:admin,staff,manager'])->group(function () {

    // Customer Management Routes
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/create-account', [CustomerController::class, 'createAccount'])->name('customers.create-account');

    // Account Management Routes
    Route::resource('accounts', AccountController::class);
    Route::patch('accounts/{account}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggle-status');

    // Transaction Management Routes
    Route::resource('transactions', TransactionController::class);
    Route::get('transfer', [TransactionController::class, 'showTransferForm'])->name('transfer.form');
    Route::post('transfer', [TransactionController::class, 'transfer'])->name('transfer.process');
    Route::post('calculate-fee', [TransactionController::class, 'calculateFee'])->name('calculate.fee');

    // Loan Management Routes
    Route::resource('loans', LoanController::class);
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');
    Route::post('loans/{loan}/disburse', [LoanController::class, 'disburse'])->name('loans.disburse');
    Route::post('calculate-loan-details', [LoanController::class, 'calculateLoanDetails'])->name('calculate.loan.details');

    // Loan Settings Routes
    Route::get('loan-settings', [App\Http\Controllers\LoanSettingsController::class, 'index'])->name('loan-settings.index');
    Route::put('loan-settings', [App\Http\Controllers\LoanSettingsController::class, 'update'])->name('loan-settings.update');
    Route::post('loan-settings/eligibility-preview', [App\Http\Controllers\LoanSettingsController::class, 'eligibilityPreview'])->name('loan-settings.eligibility-preview');

    // Employee Management Routes
    Route::resource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');

    // Branch Management Routes
    Route::resource('branches', BranchController::class);

    // Deposit Management Routes
    Route::resource('deposits', DepositController::class);
    Route::post('deposits/{deposit}/authorize', [DepositController::class, 'authorize'])->name('deposits.authorize');
    Route::post('deposits/{deposit}/reject', [DepositController::class, 'reject'])->name('deposits.reject');

    // Withdrawal Management (Employee Only)
    Route::resource('withdrawals', WithdrawalController::class);
    Route::post('withdrawals/{withdrawal}/authorize', [WithdrawalController::class, 'authorize'])->name('withdrawals.authorize');
    Route::post('withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Money Transfers (Employee + Customer)
    Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('transfers/account-details', [TransferController::class, 'getAccountDetails'])->name('transfers.account-details');

});

// Customer Banking Routes
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {

    // Customer Dashboard
    Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Transaction History
    Route::get('transactions', [CustomerDashboardController::class, 'transactions'])->name('transactions');
    Route::get('transactions/statement-options', [CustomerDashboardController::class, 'showStatementOptions'])->name('transactions.statement-options');
    Route::get('transactions/print-statement', [CustomerDashboardController::class, 'printStatement'])->name('transactions.print-statement');

    // Account Details
    Route::get('accounts/{account}', [CustomerDashboardController::class, 'account'])->name('account');

    // Money Transfers (Customer can transfer from their own accounts)
    Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('transfers/account-details', [TransferController::class, 'getAccountDetails'])->name('transfers.account-details');

    // Customer Loan Management (Secure)
    Route::get('loans/apply', [LoanController::class, 'customerApply'])->name('loans.apply');
    Route::post('loans/apply', [LoanController::class, 'customerStore'])->name('loans.store');
    Route::get('loans/{loan}/repay', [LoanController::class, 'customerRepay'])->name('loans.repay');
    Route::post('loans/{loan}/repay', [LoanController::class, 'customerProcessRepayment'])->name('loans.process-repayment');
    Route::get('loans', [LoanController::class, 'customerIndex'])->name('loans.index');
    Route::get('loans/{loan}', [LoanController::class, 'customerShow'])->name('loans.show');

});

// Banking System Dashboard Routes (ADMIN ONLY)
Route::middleware(['auth', 'role:admin,staff,manager'])->get('/dashboard', function () {
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
    ]);
})->name('dashboard');

// API Routes for testing (ADMIN ONLY)
Route::prefix('api')->middleware(['auth', 'role:admin,root'])->group(function () {

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
        try {
            $nidaService = new \App\Services\NidaService();
            $nidaNumber = request('nida_number', '19900107-23106-00002-99');

            // Just fetch customer details for verification (don't create customer)
            $result = $nidaService->fetchCustomerDetails($nidaNumber);

            if ($result['success']) {
                return [
                    'success' => true,
                    'customer' => $result['data'],
                    'message' => 'NIDA verification successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['error'] ?? 'NIDA verification failed'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'NIDA service error: ' . $e->getMessage()
            ];
        }
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
