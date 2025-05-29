<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanSetting;

class LoanSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Multiple Loan Settings
            [
                'key' => 'allow_multiple_loans',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow customers to have multiple active loans simultaneously',
                'category' => 'eligibility'
            ],
            [
                'key' => 'max_concurrent_loans',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Maximum number of concurrent loans per customer (when multiple loans are allowed)',
                'category' => 'limits'
            ],

            // Balance Requirements
            [
                'key' => 'min_balance_percentage',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Minimum account balance as percentage of loan amount (10 = 10%)',
                'category' => 'eligibility'
            ],

            // Debt-to-Income Ratio
            [
                'key' => 'max_debt_to_income_percentage',
                'value' => '40',
                'type' => 'integer',
                'description' => 'Maximum debt-to-income ratio allowed (40 = 40%)',
                'category' => 'risk'
            ],
            [
                'key' => 'income_estimation_multiplier',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Monthly income estimation as percentage of account balance (20 = 20%)',
                'category' => 'risk'
            ],

            // Loan Amount Limits
            [
                'key' => 'max_loan_amount_per_customer',
                'value' => '10000000',
                'type' => 'decimal',
                'description' => 'Maximum loan amount per customer (TSh)',
                'category' => 'limits'
            ],

            // Repayment History
            [
                'key' => 'require_good_repayment_history',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Require good repayment history for loan approval',
                'category' => 'eligibility'
            ],

            // Risk-Based Pricing
            [
                'key' => 'enable_risk_based_pricing',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable risk-based interest rate adjustments',
                'category' => 'risk'
            ],

            // Account Age Requirements
            [
                'key' => 'min_account_age_months',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Minimum account age in months for loan eligibility',
                'category' => 'eligibility'
            ],

            // Loan Processing
            [
                'key' => 'auto_approve_low_risk',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically approve loans for low-risk customers',
                'category' => 'general'
            ],
            [
                'key' => 'low_risk_threshold',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Risk score threshold for low-risk classification (0-100)',
                'category' => 'risk'
            ],

            // Balance-Based Loan Limits
            [
                'key' => 'max_loan_to_balance_multiplier',
                'value' => '500',
                'type' => 'integer',
                'description' => 'Maximum loan amount as percentage of account balance (500 = 5x balance)',
                'category' => 'limits'
            ],
        ];

        foreach ($settings as $setting) {
            LoanSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
