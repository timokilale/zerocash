@extends('layouts.customer')

@section('title', 'Repay Loan - ZeroCash')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Loan Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Loan Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Loan Type:</strong> {{ $loan->loanType->name }}</p>
                            <p><strong>Loan Number:</strong> {{ $loan->loan_number }}</p>
                            <p><strong>Principal Amount:</strong> TSh {{ number_format($loan->principal_amount, 2) }}</p>
                            <p><strong>Interest Rate:</strong> {{ $loan->interest_rate }}%</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Monthly Payment:</strong> TSh {{ number_format($loan->monthly_payment, 2) }}</p>
                            <p><strong>Outstanding Balance:</strong> <span class="text-warning fw-bold">TSh {{ number_format($loan->outstanding_balance, 2) }}</span></p>
                            <p><strong>Account Balance:</strong> <span class="text-success fw-bold">TSh {{ number_format($loan->account->balance, 2) }}</span></p>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mt-3">
                        @php
                            $paidPercentage = (($loan->principal_amount - $loan->outstanding_balance) / $loan->principal_amount) * 100;
                        @endphp
                        <label class="form-label">Loan Progress</label>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $paidPercentage }}%" 
                                 aria-valuenow="{{ $paidPercentage }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($paidPercentage, 1) }}% Paid
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repayment Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Make Repayment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer.loans.process-repayment', $loan) }}" method="POST" id="repaymentForm">
                        @csrf
                        
                        <!-- Payment Method -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check payment-method-card">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="account_balance" value="account_balance" checked>
                                            <label class="form-check-label" for="account_balance">
                                                <div class="payment-method-content">
                                                    <i class="fas fa-university fa-2x text-primary mb-2"></i>
                                                    <h6>Account Balance</h6>
                                                    <p class="text-muted small mb-0">Pay from your account balance</p>
                                                    <p class="text-success small mb-0">Available: TSh {{ number_format($loan->account->balance, 2) }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check payment-method-card">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="mobile_money" value="mobile_money">
                                            <label class="form-check-label" for="mobile_money">
                                                <div class="payment-method-content">
                                                    <i class="fas fa-mobile-alt fa-2x text-success mb-2"></i>
                                                    <h6>Mobile Money</h6>
                                                    <p class="text-muted small mb-0">Pay via mobile money</p>
                                                    <p class="text-info small mb-0">M-Pesa, Tigo Pesa, Airtel Money</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repayment Amount -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Repayment Amount (TSh) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount', $loan->monthly_payment) }}" 
                                       min="1000" max="{{ $loan->outstanding_balance }}" step="100" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum: TSh 1,000 | Maximum: TSh {{ number_format($loan->outstanding_balance, 2) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quick Amount Selection</label>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm quick-amount" 
                                            data-amount="{{ $loan->monthly_payment }}">
                                        Monthly Payment (TSh {{ number_format($loan->monthly_payment, 2) }})
                                    </button>
                                    @if($loan->outstanding_balance != $loan->monthly_payment)
                                        <button type="button" class="btn btn-outline-success btn-sm quick-amount" 
                                                data-amount="{{ $loan->outstanding_balance }}">
                                            Full Payment (TSh {{ number_format($loan->outstanding_balance, 2) }})
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light" id="paymentSummary">
                                    <div class="card-body">
                                        <h6><i class="fas fa-receipt me-2"></i>Payment Summary</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>Payment Amount:</strong></p>
                                                <span id="summaryAmount" class="text-primary">TSh {{ number_format($loan->monthly_payment, 2) }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>Remaining Balance:</strong></p>
                                                <span id="summaryRemaining" class="text-warning">TSh {{ number_format($loan->outstanding_balance - $loan->monthly_payment, 2) }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>Payment Method:</strong></p>
                                                <span id="summaryMethod" class="text-info">Account Balance</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm" name="confirm" required>
                                    <label class="form-check-label" for="confirm">
                                        I confirm that I want to make this loan repayment. This action cannot be undone.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('customer.loans.show', $loan) }}" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Loan
                                    </a>
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <i class="fas fa-credit-card me-1"></i>Process Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .payment-method-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }
    
    .payment-method-card:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    
    .payment-method-card input[type="radio"]:checked + label {
        border-color: #007bff;
        background-color: #e3f2fd;
    }
    
    .payment-method-content {
        text-align: center;
        padding: 10px;
    }
    
    .form-check-input {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    .quick-amount {
        font-size: 0.875rem;
    }
    
    .progress {
        height: 10px;
        border-radius: 5px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Quick amount selection
    $('.quick-amount').click(function() {
        const amount = $(this).data('amount');
        $('#amount').val(amount);
        updateSummary();
    });

    // Update summary when amount changes
    $('#amount').on('input', updateSummary);
    
    // Update summary when payment method changes
    $('input[name="payment_method"]').change(function() {
        updateSummary();
    });

    function updateSummary() {
        const amount = parseFloat($('#amount').val()) || 0;
        const outstanding = {{ $loan->outstanding_balance }};
        const remaining = Math.max(0, outstanding - amount);
        const method = $('input[name="payment_method"]:checked').next('label').find('h6').text();

        $('#summaryAmount').text('TSh ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#summaryRemaining').text('TSh ' + remaining.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#summaryMethod').text(method);
    }

    // Form validation
    $('#repaymentForm').submit(function(e) {
        const amount = parseFloat($('#amount').val());
        const accountBalance = {{ $loan->account->balance }};
        const paymentMethod = $('input[name="payment_method"]:checked').val();

        if (paymentMethod === 'account_balance' && amount > accountBalance) {
            e.preventDefault();
            alert('Insufficient account balance. Your current balance is TSh ' + accountBalance.toLocaleString('en-US', {minimumFractionDigits: 2}));
            return false;
        }

        if (!confirm('Are you sure you want to process this payment?')) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize summary
    updateSummary();
});
</script>
@endpush
