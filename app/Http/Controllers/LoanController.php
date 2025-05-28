<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
                ->with('success', 'Loan approved successfully! Funds will be transferred automatically.');

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
}
