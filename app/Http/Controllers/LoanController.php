<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Loan;
use App\Models\Account;
use App\Models\LoanType;
use App\Models\Branch;
use App\Models\User;

class LoanController extends Controller
{
    /**
     * Display a listing of loans.
     */
    public function index()
    {
        $loans = Loan::with(['account.user', 'loanType', 'branch'])
            ->latest()
            ->paginate(20);

        return view('banking.loans.index', compact('loans'));
    }

    /**
     * Show the form for creating a new loan.
     */
    public function create()
    {
        $accounts = Account::with(['user', 'accountType'])->get();
        $loanTypes = LoanType::all();
        $branches = Branch::all();

        return view('banking.loans.create', compact('accounts', 'loanTypes', 'branches'));
    }

    /**
     * Store a newly created loan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'loan_type_id' => 'required|exists:loan_types,id',
            'branch_id' => 'required|exists:branches,id',
            'principal_amount' => 'required|numeric|min:1000',
            'term_months' => 'required|integer|min:1|max:360',
        ]);

        // Validate loan amount against loan type limits
        $loanType = LoanType::find($request->loan_type_id);
        if ($request->principal_amount < $loanType->min_amount || $request->principal_amount > $loanType->max_amount) {
            return back()->withErrors([
                'principal_amount' => "Loan amount must be between TSh " . number_format($loanType->min_amount) . " and TSh " . number_format($loanType->max_amount)
            ])->withInput();
        }

        // Validate term against loan type limits
        if ($request->term_months < $loanType->min_term_months || $request->term_months > $loanType->max_term_months) {
            return back()->withErrors([
                'term_months' => "Loan term must be between {$loanType->min_term_months} and {$loanType->max_term_months} months"
            ])->withInput();
        }

        try {
            $loan = Loan::create([
                'account_id' => $request->account_id,
                'loan_type_id' => $request->loan_type_id,
                'branch_id' => $request->branch_id,
                'principal_amount' => $request->principal_amount,
                'term_months' => $request->term_months,
                'status' => 'pending',
            ]);

            return redirect()->route('loans.show', $loan->id)
                ->with('success', 'Loan application created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create loan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified loan.
     */
    public function show(string $id)
    {
        $loan = Loan::with(['account.user', 'loanType', 'branch'])
            ->findOrFail($id);

        return view('banking.loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the loan.
     */
    public function edit(string $id)
    {
        $loan = Loan::findOrFail($id);
        $accounts = Account::with(['user', 'accountType'])->get();
        $loanTypes = LoanType::all();
        $branches = Branch::all();

        return view('banking.loans.edit', compact('loan', 'accounts', 'loanTypes', 'branches'));
    }

    /**
     * Update the specified loan.
     */
    public function update(Request $request, string $id)
    {
        $loan = Loan::findOrFail($id);

        // Only allow updates if loan is still pending
        if ($loan->status !== 'pending') {
            return back()->withErrors(['error' => 'Cannot update loan that is not in pending status.']);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,active,completed,defaulted',
        ]);

        $loan->update([
            'status' => $request->status,
        ]);

        return redirect()->route('loans.show', $loan->id)
            ->with('success', 'Loan updated successfully!');
    }

    /**
     * Approve a loan.
     */
    public function approve(string $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending loans can be approved.']);
        }

        try {
            $loan->update(['status' => 'approved']);

            return redirect()->route('loans.show', $loan->id)
                ->with('success', 'Loan approved successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to approve loan: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a loan.
     */
    public function reject(string $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending loans can be rejected.']);
        }

        $loan->update(['status' => 'rejected']);

        return redirect()->route('loans.show', $loan->id)
            ->with('success', 'Loan rejected successfully.');
    }

    /**
     * Calculate loan details (interest rate, monthly payment).
     */
    public function calculateLoanDetails(Request $request)
    {
        $request->validate([
            'loan_type_id' => 'required|exists:loan_types,id',
            'principal_amount' => 'required|numeric|min:1000',
            'term_months' => 'required|integer|min:1|max:360',
        ]);

        $interestRate = Loan::calculateInterestRate(
            $request->loan_type_id,
            $request->principal_amount,
            $request->term_months
        );

        $monthlyPayment = Loan::calculateMonthlyPayment(
            $request->principal_amount,
            $interestRate,
            $request->term_months
        );

        $totalPayment = $monthlyPayment * $request->term_months;
        $totalInterest = $totalPayment - $request->principal_amount;

        return response()->json([
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'total_payment' => $totalPayment,
            'total_interest' => $totalInterest,
            'formatted_monthly_payment' => 'TSh ' . number_format($monthlyPayment, 2),
            'formatted_total_payment' => 'TSh ' . number_format($totalPayment, 2),
            'formatted_total_interest' => 'TSh ' . number_format($totalInterest, 2),
        ]);
    }

    /**
     * Customer loan application form.
     */
    public function customerApply()
    {
        if (Auth::user()->role !== 'customer') {
            abort(403);
        }

        $user = Auth::user();
        $account = $user->accounts()->first();

        if (!$account) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'You need an account to apply for a loan. Please contact customer service.');
        }

        $loanTypes = \App\Models\LoanType::all();
        $branches = \App\Models\Branch::all();

        return view('customer.loans.apply', compact('account', 'loanTypes', 'branches'));
    }

    /**
     * Store customer loan application.
     */
    public function customerStore(Request $request)
    {
        if (Auth::user()->role !== 'customer') {
            abort(403);
        }

        $user = Auth::user();
        $account = $user->accounts()->first();

        if (!$account) {
            return back()->with('error', 'You need an account to apply for a loan.');
        }

        $request->validate([
            'loan_type_id' => 'required|exists:loan_types,id',
            'principal_amount' => 'required|numeric|min:10000|max:10000000',
            'term_months' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string|max:500',
        ]);

        $loanType = \App\Models\LoanType::findOrFail($request->loan_type_id);

        // Validate amount against loan type limits
        if ($request->principal_amount < $loanType->min_amount || $request->principal_amount > $loanType->max_amount) {
            return back()->withErrors([
                'principal_amount' => "Loan amount must be between TSh " . number_format($loanType->min_amount, 2) . " and TSh " . number_format($loanType->max_amount, 2)
            ])->withInput();
        }

        // Validate term against loan type limits
        if ($request->term_months < $loanType->min_term_months || $request->term_months > $loanType->max_term_months) {
            return back()->withErrors([
                'term_months' => "Loan term must be between {$loanType->min_term_months} and {$loanType->max_term_months} months"
            ])->withInput();
        }

        try {
            $loan = Loan::create([
                'account_id' => $account->id,
                'loan_type_id' => $request->loan_type_id,
                'branch_id' => $account->branch_id,
                'principal_amount' => $request->principal_amount,
                'term_months' => $request->term_months,
                'purpose' => $request->purpose,
                'status' => 'pending',
            ]);

            return redirect()->route('customer.loans.show', $loan)
                ->with('success', 'Loan application submitted successfully! We will review your application and notify you soon.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to submit loan application: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Customer loan listing.
     */
    public function customerIndex()
    {
        if (Auth::user()->role !== 'customer') {
            abort(403);
        }

        $user = Auth::user();
        $loans = collect();

        foreach ($user->accounts as $account) {
            $loans = $loans->merge($account->loans()->with('loanType', 'branch')->get());
        }

        return view('customer.loans.index', compact('loans'));
    }

    /**
     * Customer loan details.
     */
    public function customerShow(Loan $loan)
    {
        if (Auth::user()->role !== 'customer') {
            abort(403);
        }

        // Check if loan belongs to the customer
        if (!Auth::user()->accounts->contains($loan->account_id)) {
            abort(403);
        }

        $loan->load('loanType', 'branch', 'account');

        return view('customer.loans.show', compact('loan'));
    }

    /**
     * Customer loan repayment form.
     */
    public function customerRepay(Loan $loan)
    {
        if (Auth::user()->role !== 'customer') {
            abort(403);
        }

        // Check if loan belongs to the customer
        if (!Auth::user()->accounts->contains($loan->account_id)) {
            abort(403);
        }

        if ($loan->status !== 'active') {
            return redirect()->route('customer.loans.show', $loan)
                ->with('error', 'Only active loans can be repaid.');
        }

        return view('customer.loans.repay', compact('loan'));
    }

    /**
     * Process customer loan repayment.
     */
    public function customerProcessRepayment(Request $request, Loan $loan)
    {
        if (Auth::user()->role !== 'customer') {
            abort(403);
        }

        // Check if loan belongs to the customer
        if (!Auth::user()->accounts->contains($loan->account_id)) {
            abort(403);
        }

        if ($loan->status !== 'active') {
            return back()->with('error', 'Only active loans can be repaid.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1000|max:' . $loan->outstanding_balance,
            'payment_method' => 'required|in:account_balance,mobile_money',
        ]);

        $account = $loan->account;
        $amount = $request->amount;

        // Check if customer has sufficient balance
        if ($request->payment_method === 'account_balance' && $account->balance < $amount) {
            return back()->withErrors([
                'amount' => 'Insufficient account balance. Your current balance is TSh ' . number_format($account->balance, 2)
            ]);
        }

        try {
            DB::transaction(function () use ($loan, $account, $amount, $request) {
                // Deduct from account if using account balance
                if ($request->payment_method === 'account_balance') {
                    $account->debit($amount);
                }

                // Update loan outstanding balance
                $loan->outstanding_balance -= $amount;

                // Check if loan is fully paid
                if ($loan->outstanding_balance <= 0) {
                    $loan->status = 'completed';
                    $loan->outstanding_balance = 0;
                }

                $loan->save();

                // Create transaction record
                $transaction = \App\Models\Transaction::create([
                    'sender_account_id' => $account->id,
                    'receiver_account_id' => null, // Bank account (system)
                    'transaction_type_id' => \App\Models\TransactionType::where('code', 'LRP')->first()->id ?? 1,
                    'amount' => $amount,
                    'description' => "Loan repayment for loan #{$loan->loan_number}",
                    'reference' => 'LRP-' . time(),
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);

                // Create notification
                \App\Models\Notification::create([
                    'user_id' => Auth::id(),
                    'title' => 'Loan Repayment Successful',
                    'message' => "Your loan repayment of TSh " . number_format($amount, 2) . " has been processed successfully.",
                    'type' => 'loan',
                    'data' => [
                        'loan_id' => $loan->id,
                        'transaction_id' => $transaction->id,
                        'amount' => $amount,
                    ],
                ]);
            });

            $message = 'Loan repayment of TSh ' . number_format($amount, 2) . ' processed successfully!';
            if ($loan->status === 'completed') {
                $message .= ' Congratulations! Your loan has been fully paid off.';
            }

            return redirect()->route('customer.loans.show', $loan)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to process repayment: ' . $e->getMessage()]);
        }
    }
}
