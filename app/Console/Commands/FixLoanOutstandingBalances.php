<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;

class FixLoanOutstandingBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:fix-outstanding-balances {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix outstanding balances for existing loans to include interest';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing loan outstanding balances...');

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        // Get all loans that might need fixing
        $loans = Loan::whereIn('status', ['pending', 'approved', 'active'])
            ->get();

        if ($loans->isEmpty()) {
            $this->info('✅ No loans found that need fixing.');
            return;
        }

        $this->info("Found {$loans->count()} loans to check...");

        $fixedCount = 0;
        $skippedCount = 0;

        foreach ($loans as $loan) {
            $currentBalance = $loan->outstanding_balance;
            $totalPayment = $loan->monthly_payment * $loan->term_months;

            // If current balance equals principal, it needs fixing
            if (abs($currentBalance - $loan->principal_amount) < 0.01) {
                $newBalance = $totalPayment; // Set to total payment amount

                $this->line("Loan #{$loan->loan_number}:");
                $this->line("  Principal: TSh " . number_format($loan->principal_amount, 2));
                $this->line("  Current Outstanding: TSh " . number_format($currentBalance, 2));
                $this->line("  Correct Outstanding: TSh " . number_format($newBalance, 2));
                $this->line("  Interest Added: TSh " . number_format($newBalance - $currentBalance, 2));

                if (!$dryRun) {
                    $loan->outstanding_balance = $newBalance;
                    $loan->save();
                    $this->info("  ✅ Fixed!");
                } else {
                    $this->warn("  🔍 Would be fixed");
                }

                $fixedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->info("\n📊 Summary:");
        $this->info("  Loans fixed: {$fixedCount}");
        $this->info("  Loans skipped (already correct): {$skippedCount}");

        if ($dryRun && $fixedCount > 0) {
            $this->warn("\n⚠️  Run without --dry-run to apply the changes");
        } elseif ($fixedCount > 0) {
            $this->info("\n✅ All loan outstanding balances have been fixed!");
        }
    }
}
