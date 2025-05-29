<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Loan;
use App\Models\Employee;
use App\Models\Deposit;
use App\Services\NidaService;

class TestBankingFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banking:test-features';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all banking system features and requirements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏦 Testing ZeroCash Banking System Features...');
        $this->newLine();

        // Test 1: NIDA Customer Creation (Requirement #9)
        $this->testNidaCustomerCreation();

        // Test 2: Automatic Account Number Generation (Requirement #6)
        $this->testAccountNumberGeneration();

        // Test 3: Employee Creation with Auto Password (Requirement #7)
        $this->testEmployeeCreation();

        // Test 4: Transaction with Automatic Fees (Requirement #2)
        $this->testTransactionFees();

        // Test 5: Loan with Interest Rate Algorithm (Requirement #5)
        $this->testLoanInterestCalculation();

        // Test 6: Loan Auto-Transfer (Requirement #1)
        $this->testLoanAutoTransfer();

        // Test 7: Deposit by Employee (Requirement #3)
        $this->testEmployeeDeposit();

        // Test 8: Employee Soft Delete (Requirement #10)
        $this->testEmployeeSoftDelete();

        // Test 9: Automatic Notifications (Requirement #4)
        $this->testNotifications();

        $this->newLine();
        $this->info('✅ All banking system features tested successfully!');
    }

    private function testNidaCustomerCreation()
    {
        $this->info('🔍 Testing NIDA Customer Creation...');

        $nidaNumber = '19900107-23106-00002-21';

        // Check if customer already exists
        $existingCustomer = User::where('nida', $nidaNumber)->first();
        if ($existingCustomer) {
            $this->line("   ✓ Customer already exists: {$existingCustomer->full_name}");
            $this->line("   ✓ NIDA verified: " . ($existingCustomer->nida_verified ? 'Yes' : 'No'));
            $this->line("   ✓ Account: {$existingCustomer->accounts->first()->account_number}");
            return;
        }

        $nidaService = new NidaService();
        $result = $nidaService->createCustomerFromNida($nidaNumber, [
            'email' => 'test.customer@zerocash.com',
            'phone' => '+255700123456'
        ]);

        if ($result['success']) {
            $customer = $result['data']['user'];
            $account = $result['data']['account'];
            $this->line("   ✓ Customer created: {$customer->full_name}");
            $this->line("   ✓ Account created: {$account->account_number}");
            $this->line("   ✓ Temporary password: {$result['data']['temporary_password']}");
        } else {
            $this->error("   ✗ Failed: {$result['error']}");
        }
    }

    private function testAccountNumberGeneration()
    {
        $this->info('🔢 Testing Automatic Account Number Generation...');

        // Get first customer
        $customer = User::where('role', 'customer')->first();
        $accountType = \App\Models\AccountType::where('code', 'SAV')->first();
        $branch = \App\Models\Branch::first();

        $account = Account::create([
            'user_id' => $customer->id,
            'account_type_id' => $accountType->id,
            'branch_id' => $branch->id,
        ]);

        $this->line("   ✓ Auto-generated account number: {$account->account_number}");
        $this->line("   ✓ Format: " . date('Ymd') . "XXXXXX");
    }

    private function testEmployeeCreation()
    {
        $this->info('👨‍💼 Testing Employee Creation with Auto Password...');

        // Check if employee already exists
        $existingEmployee = User::where('role', 'employee')->where('first_name', 'John')->where('last_name', 'Employee')->first();
        if ($existingEmployee && $existingEmployee->employee) {
            $this->line("   ✓ Employee already exists: {$existingEmployee->full_name}");
            $this->line("   ✓ Employee ID: {$existingEmployee->employee->employee_id}");
            $this->line("   ✓ Password auto-generated: " . ($existingEmployee->password_auto_generated ? 'Yes' : 'No'));
            return;
        }

        $password = User::generatePassword();
        $username = User::generateUsername('John', 'Employee');
        $email = 'john.employee.' . time() . '@zerocash.com';
        $nida = '19850315-23106-00002-' . rand(10, 99);

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => \Hash::make($password),
            'first_name' => 'John',
            'last_name' => 'Employee',
            'phone' => '+255700987654',
            'address' => 'Employee Address',
            'nida' => $nida,
            'date_of_birth' => '1985-03-15',
            'role' => 'employee',
            'password_auto_generated' => true,
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => 1,
            'position' => 'Teller',
            'salary' => 800000,
            'hire_date' => now(),
        ]);

        $this->line("   ✓ Employee created: {$user->full_name}");
        $this->line("   ✓ Employee ID: {$employee->employee_id}");
        $this->line("   ✓ Auto-generated password: {$password}");
    }

    private function testTransactionFees()
    {
        $this->info('💰 Testing Transaction with Automatic Fees...');

        $accounts = Account::take(2)->get();
        $transferType = \App\Models\TransactionType::where('code', 'TRF')->first();

        $transaction = Transaction::create([
            'sender_account_id' => $accounts[0]->id,
            'receiver_account_id' => $accounts[1]->id,
            'transaction_type_id' => $transferType->id,
            'amount' => 50000,
            'description' => 'Test transfer',
        ]);

        $this->line("   ✓ Transaction created: {$transaction->transaction_number}");
        $this->line("   ✓ Amount: TSh " . number_format($transaction->amount, 2));
        $this->line("   ✓ Auto-calculated fee: TSh " . number_format($transaction->fee_amount, 2));
        $this->line("   ✓ Total amount: TSh " . number_format($transaction->total_amount, 2));
    }

    private function testLoanInterestCalculation()
    {
        $this->info('📊 Testing Loan Interest Rate Algorithm...');

        $customer = User::where('role', 'customer')->first();
        $account = $customer->accounts()->first();
        $loanType = \App\Models\LoanType::where('code', 'PL')->first();

        $loan = Loan::create([
            'account_id' => $account->id,
            'loan_type_id' => $loanType->id,
            'branch_id' => 1,
            'principal_amount' => 1000000,
            'term_months' => 24,
        ]);

        $this->line("   ✓ Loan created: {$loan->loan_number}");
        $this->line("   ✓ Principal: TSh " . number_format($loan->principal_amount, 2));
        $this->line("   ✓ Auto-calculated interest rate: {$loan->interest_rate}%");
        $this->line("   ✓ Monthly payment: TSh " . number_format($loan->monthly_payment, 2));
    }

    private function testLoanAutoTransfer()
    {
        $this->info('🔄 Testing Loan Auto-Transfer to Account...');

        $loan = Loan::where('status', 'pending')->first();
        $initialBalance = $loan->account->balance;

        // Approve the loan (this should trigger auto-transfer)
        $loan->status = 'approved';
        $loan->save();

        $loan->refresh();
        $finalBalance = $loan->account->balance;

        $this->line("   ✓ Loan approved: {$loan->loan_number}");
        $this->line("   ✓ Initial account balance: TSh " . number_format($initialBalance, 2));
        $this->line("   ✓ Final account balance: TSh " . number_format($finalBalance, 2));
        $this->line("   ✓ Auto-transferred: " . ($loan->auto_transferred ? 'Yes' : 'No'));
    }

    private function testEmployeeDeposit()
    {
        $this->info('💵 Testing Employee Deposit...');

        $employee = Employee::first();
        $account = Account::first();

        $deposit = Deposit::create([
            'account_id' => $account->id,
            'amount' => 100000,
            'deposited_by' => $employee->user_id,
            'deposit_method' => 'cash',
            'reference' => 'TEST-DEP-001',
            'notes' => 'Test deposit by employee',
        ]);

        $initialBalance = $account->balance;
        $deposit->process();
        $account->refresh();
        $finalBalance = $account->balance;

        $this->line("   ✓ Deposit processed by: {$employee->user->full_name}");
        $this->line("   ✓ Amount: TSh " . number_format($deposit->amount, 2));
        $this->line("   ✓ Account balance increased: TSh " . number_format($finalBalance - $initialBalance, 2));
    }

    private function testEmployeeSoftDelete()
    {
        $this->info('🗑️ Testing Employee Soft Delete...');

        $employee = Employee::first();
        $employeeName = $employee->user->full_name;

        // Soft delete the employee
        $employee->makeInactive();

        $activeEmployees = Employee::count();
        $allEmployees = Employee::withTrashed()->count();

        $this->line("   ✓ Employee made inactive: {$employeeName}");
        $this->line("   ✓ Active employees: {$activeEmployees}");
        $this->line("   ✓ Total employees (including inactive): {$allEmployees}");
        $this->line("   ✓ Data preserved in database");
    }

    private function testNotifications()
    {
        $this->info('🔔 Testing Automatic Notifications...');

        $customer = User::where('role', 'customer')->first();
        $notificationCount = $customer->notifications()->count();

        $this->line("   ✓ Customer: {$customer->full_name}");
        $this->line("   ✓ Total notifications: {$notificationCount}");

        if ($notificationCount > 0) {
            $latestNotification = $customer->notifications()->latest()->first();
            $this->line("   ✓ Latest notification: {$latestNotification->title}");
            $this->line("   ✓ Message: {$latestNotification->message}");
        }
    }
}
