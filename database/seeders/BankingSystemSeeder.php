<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BankingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert branches
        DB::table('branches')->insert([
            [
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'address' => 'No 1, Main Street, Arusha',
                'phone' => '+255710000001',
                'email' => 'main@zerocash.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Moshi Branch',
                'code' => 'MOSHI',
                'address' => 'No 2, Kilimanjaro Street, Moshi',
                'phone' => '+255710000002',
                'email' => 'moshi@zerocash.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert account types
        DB::table('account_types')->insert([
            [
                'name' => 'Savings Account',
                'code' => 'SAV',
                'description' => 'Standard savings account',
                'minimum_balance' => 10000.00,
                'interest_rate' => 2.50,
                'monthly_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Current Account',
                'code' => 'CUR',
                'description' => 'Current account for businesses',
                'minimum_balance' => 50000.00,
                'interest_rate' => 0.00,
                'monthly_fee' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fixed Deposit',
                'code' => 'FD',
                'description' => 'Fixed deposit account',
                'minimum_balance' => 100000.00,
                'interest_rate' => 5.00,
                'monthly_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert transaction types
        DB::table('transaction_types')->insert([
            [
                'name' => 'Transfer',
                'code' => 'TRF',
                'description' => 'Money transfer between accounts',
                'fee_type' => 'fixed',
                'fee_amount' => 1000.00,
                'fee_percentage' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Deposit',
                'code' => 'DEP',
                'description' => 'Cash deposit',
                'fee_type' => 'fixed',
                'fee_amount' => 0.00,
                'fee_percentage' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Withdrawal',
                'code' => 'WTH',
                'description' => 'Cash withdrawal',
                'fee_type' => 'percentage',
                'fee_amount' => 0.00,
                'fee_percentage' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Loan Disbursement',
                'code' => 'LND',
                'description' => 'Loan amount transfer to account',
                'fee_type' => 'fixed',
                'fee_amount' => 0.00,
                'fee_percentage' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert loan types
        DB::table('loan_types')->insert([
            [
                'name' => 'Personal Loan',
                'code' => 'PL',
                'description' => 'Personal loan for individuals',
                'base_interest_rate' => 15.00,
                'max_amount' => 5000000.00,
                'min_amount' => 100000.00,
                'max_term_months' => 60,
                'min_term_months' => 6,
                'processing_fee_rate' => 2.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Business Loan',
                'code' => 'BL',
                'description' => 'Business loan for enterprises',
                'base_interest_rate' => 12.00,
                'max_amount' => 50000000.00,
                'min_amount' => 500000.00,
                'max_term_months' => 120,
                'min_term_months' => 12,
                'processing_fee_rate' => 1.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mortgage',
                'code' => 'MTG',
                'description' => 'Home mortgage loan',
                'base_interest_rate' => 10.00,
                'max_amount' => 100000000.00,
                'min_amount' => 1000000.00,
                'max_term_months' => 360,
                'min_term_months' => 60,
                'processing_fee_rate' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert root user
        DB::table('users')->insert([
            'username' => 'root',
            'email' => 'root@zerocash.com',
            'password' => Hash::make('root123'),
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '+255710000000',
            'address' => 'System Address',
            'nida' => '20000101-23106-00002-21',
            'date_of_birth' => '2000-01-01',
            'role' => 'root',
            'status' => 'active',
            'password_auto_generated' => false,
            'nida_verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
