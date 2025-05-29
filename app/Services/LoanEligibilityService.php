<?php

namespace App\Services;

use App\Models\User;
use App\Models\Loan;
use App\Models\LoanSetting;
use App\Models\Account;
use Carbon\Carbon;

class LoanEligibilityService
{
    /**
     * Check if user is eligible for a loan.
     */
    public function checkEligibility(User $user, float $requestedAmount, int $termMonths): array
    {
        $account = $user->accounts()->first();

        if (!$account) {
            return [
                'eligible' => false,
                'reason' => 'No account found',
                'details' => 'Customer must have an active account to apply for loans.'
            ];
        }

        // Check multiple loan restriction
        $multipleLoansCheck = $this->checkMultipleLoans($user);
        if (!$multipleLoansCheck['allowed']) {
            return [
                'eligible' => false,
                'reason' => 'Multiple loans not allowed',
                'details' => $multipleLoansCheck['message']
            ];
        }

        // Check maximum loan amount based on account balance
        $balanceCheck = $this->checkAccountBalanceRequirement($account, $requestedAmount);
        if (!$balanceCheck['allowed']) {
            return [
                'eligible' => false,
                'reason' => 'Insufficient account balance history',
                'details' => $balanceCheck['message']
            ];
        }

        // Check debt-to-income ratio (using account balance as income proxy)
        // COMMENTED OUT: Can be too restrictive with simplified income estimation
        /*
        $debtToIncomeCheck = $this->checkDebtToIncomeRatio($account, $requestedAmount, $termMonths);
        if (!$debtToIncomeCheck['allowed']) {
            return [
                'eligible' => false,
                'reason' => 'Debt-to-income ratio too high',
                'details' => $debtToIncomeCheck['message']
            ];
        }
        */

        // Check repayment history
        $repaymentHistoryCheck = $this->checkRepaymentHistory($user);
        if (!$repaymentHistoryCheck['allowed']) {
            return [
                'eligible' => false,
                'reason' => 'Poor repayment history',
                'details' => $repaymentHistoryCheck['message']
            ];
        }

        // Check maximum loan amount per customer
        $maxAmountCheck = $this->checkMaximumLoanAmount($user, $requestedAmount);
        if (!$maxAmountCheck['allowed']) {
            return [
                'eligible' => false,
                'reason' => 'Loan amount exceeds maximum allowed',
                'details' => $maxAmountCheck['message']
            ];
        }

        return [
            'eligible' => true,
            'reason' => 'Eligible for loan',
            'details' => 'All eligibility criteria met.',
            'risk_score' => $this->calculateRiskScore($user, $account, $requestedAmount),
            'recommended_interest_adjustment' => $this->getInterestAdjustment($user, $account)
        ];
    }

    /**
     * Check multiple loan restrictions.
     */
    protected function checkMultipleLoans(User $user): array
    {
        $allowMultipleLoans = LoanSetting::get('allow_multiple_loans', false);

        if (!$allowMultipleLoans) {
            $activeLoans = Loan::whereHas('account', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereIn('status', ['pending', 'approved', 'active'])->count();

            if ($activeLoans > 0) {
                return [
                    'allowed' => false,
                    'message' => 'You have an existing loan. Please complete your current loan before applying for a new one.'
                ];
            }
        } else {
            $maxConcurrentLoans = LoanSetting::get('max_concurrent_loans', 2);
            $activeLoans = Loan::whereHas('account', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereIn('status', ['pending', 'approved', 'active'])->count();

            if ($activeLoans >= $maxConcurrentLoans) {
                return [
                    'allowed' => false,
                    'message' => "You have reached the maximum number of concurrent loans ({$maxConcurrentLoans}). Please complete some loans before applying for new ones."
                ];
            }
        }

        return ['allowed' => true, 'message' => 'Multiple loan check passed'];
    }

    /**
     * Check account balance requirement.
     */
    protected function checkAccountBalanceRequirement(Account $account, float $requestedAmount): array
    {
        $minBalancePercentage = LoanSetting::get('min_balance_percentage', 10); // 10% of loan amount
        $requiredBalance = $requestedAmount * ($minBalancePercentage / 100);

        if ($account->balance < $requiredBalance) {
            return [
                'allowed' => false,
                'message' => "Minimum account balance of TSh " . number_format($requiredBalance, 2) . " ({$minBalancePercentage}% of loan amount) required. Current balance: TSh " . number_format($account->balance, 2)
            ];
        }

        return ['allowed' => true, 'message' => 'Account balance requirement met'];
    }

    /**
     * Check debt-to-income ratio.
     */
    protected function checkDebtToIncomeRatio(Account $account, float $requestedAmount, int $termMonths): array
    {
        $maxDebtToIncomePercentage = LoanSetting::get('max_debt_to_income_percentage', 40); // 40%

        // Use account balance as income proxy (more realistic)
        $incomeMultiplier = LoanSetting::get('income_estimation_multiplier', 20) / 100; // Default 20% of balance as monthly income
        $estimatedMonthlyIncome = $account->balance * $incomeMultiplier;

        // Calculate existing monthly debt obligations
        $existingMonthlyDebt = Loan::whereHas('account', function($query) use ($account) {
            $query->where('user_id', $account->user_id);
        })->whereIn('status', ['active'])->sum('monthly_payment');

        // Calculate new monthly payment (more realistic with interest)
        $estimatedInterestRate = 0.15; // 15% annual rate for DTI calculation
        $monthlyRate = $estimatedInterestRate / 12;
        $newMonthlyPayment = $termMonths > 0 ?
            ($requestedAmount * $monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1) :
            $requestedAmount / max($termMonths, 1);

        $totalMonthlyDebt = $existingMonthlyDebt + $newMonthlyPayment;
        $debtToIncomePercentage = $estimatedMonthlyIncome > 0 ? ($totalMonthlyDebt / $estimatedMonthlyIncome) * 100 : 100;

        if ($debtToIncomePercentage > $maxDebtToIncomePercentage) {
            return [
                'allowed' => false,
                'message' => "Debt-to-income ratio (" . number_format($debtToIncomePercentage, 1) . "%) exceeds maximum allowed ({$maxDebtToIncomePercentage}%)"
            ];
        }

        return ['allowed' => true, 'message' => 'Debt-to-income ratio acceptable'];
    }

    /**
     * Check repayment history.
     */
    protected function checkRepaymentHistory(User $user): array
    {
        $requireGoodHistory = LoanSetting::get('require_good_repayment_history', true);

        if (!$requireGoodHistory) {
            return ['allowed' => true, 'message' => 'Repayment history check disabled'];
        }

        $completedLoans = Loan::whereHas('account', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'completed')->count();

        $defaultedLoans = Loan::whereHas('account', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'defaulted')->count();

        // If customer has defaulted loans, reject
        if ($defaultedLoans > 0) {
            return [
                'allowed' => false,
                'message' => 'You have defaulted loans in your history. Please contact customer service.'
            ];
        }

        // For new customers (no loan history), allow with higher scrutiny
        if ($completedLoans === 0 && $defaultedLoans === 0) {
            return ['allowed' => true, 'message' => 'New customer - no loan history'];
        }

        return ['allowed' => true, 'message' => 'Good repayment history'];
    }

    /**
     * Check maximum loan amount.
     */
    protected function checkMaximumLoanAmount(User $user, float $requestedAmount): array
    {
        $maxLoanAmount = LoanSetting::get('max_loan_amount_per_customer', 10000000); // 10M default

        if ($requestedAmount > $maxLoanAmount) {
            return [
                'allowed' => false,
                'message' => "Requested amount exceeds maximum allowed loan amount of TSh " . number_format($maxLoanAmount, 2)
            ];
        }

        return ['allowed' => true, 'message' => 'Loan amount within limits'];
    }

    /**
     * Calculate risk score (0-100, higher = riskier).
     */
    protected function calculateRiskScore(User $user, Account $account, float $requestedAmount): int
    {
        $score = 0;

        // Account age factor
        $accountAge = $account->created_at->diffInMonths(now());
        if ($accountAge < 6) $score += 20;
        elseif ($accountAge < 12) $score += 10;

        // Balance to loan ratio
        $balanceRatio = $account->balance / $requestedAmount;
        if ($balanceRatio < 0.1) $score += 30;
        elseif ($balanceRatio < 0.2) $score += 15;

        // Loan history
        $completedLoans = Loan::whereHas('account', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'completed')->count();

        if ($completedLoans === 0) $score += 15; // New customer
        elseif ($completedLoans >= 3) $score -= 10; // Good history

        return min(100, max(0, $score));
    }

    /**
     * Get interest rate adjustment based on risk.
     */
    protected function getInterestAdjustment(User $user, Account $account): float
    {
        $riskScore = $this->calculateRiskScore($user, $account, 1000000); // Use 1M as baseline

        // Adjust interest rate based on risk (percentage points)
        if ($riskScore >= 70) return 3.0; // High risk: +3%
        if ($riskScore >= 50) return 1.5; // Medium risk: +1.5%
        if ($riskScore >= 30) return 0.5; // Low-medium risk: +0.5%
        if ($riskScore <= 10) return -0.5; // Very low risk: -0.5%

        return 0; // Standard rate
    }
}
