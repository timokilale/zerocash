@extends('layouts.customer')

@section('title', 'Apply for Loan - ZeroCash')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Apply for Loan</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer.loans.store') }}" method="POST" id="loanApplicationForm">
                        @csrf
                        
                        <!-- Account Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Your Account Information</h6>
                                    <p class="mb-1"><strong>Account Number:</strong> {{ $account->account_number }}</p>
                                    <p class="mb-1"><strong>Account Type:</strong> {{ $account->accountType->name }}</p>
                                    <p class="mb-0"><strong>Current Balance:</strong> TSh {{ number_format($account->balance, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Type -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="loan_type_id" class="form-label">Loan Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('loan_type_id') is-invalid @enderror" 
                                        id="loan_type_id" name="loan_type_id" required>
                                    <option value="">Select Loan Type</option>
                                    @foreach($loanTypes as $loanType)
                                        <option value="{{ $loanType->id }}" 
                                                data-min-amount="{{ $loanType->min_amount }}"
                                                data-max-amount="{{ $loanType->max_amount }}"
                                                data-min-term="{{ $loanType->min_term_months }}"
                                                data-max-term="{{ $loanType->max_term_months }}"
                                                data-base-rate="{{ $loanType->base_interest_rate }}"
                                                {{ old('loan_type_id') == $loanType->id ? 'selected' : '' }}>
                                            {{ $loanType->name }} ({{ $loanType->base_interest_rate }}% base rate)
                                        </option>
                                    @endforeach
                                </select>
                                @error('loan_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="loanTypeInfo"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="term_months" class="form-label">Loan Term (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                       id="term_months" name="term_months" value="{{ old('term_months') }}" 
                                       min="1" max="60" required>
                                @error('term_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="termInfo"></div>
                            </div>
                        </div>

                        <!-- Loan Amount -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="principal_amount" class="form-label">Loan Amount (TSh) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('principal_amount') is-invalid @enderror" 
                                       id="principal_amount" name="principal_amount" value="{{ old('principal_amount') }}" 
                                       min="10000" max="10000000" step="1000" required>
                                @error('principal_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="amountInfo"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('purpose') is-invalid @enderror" 
                                          id="purpose" name="purpose" rows="3" maxlength="500" required>{{ old('purpose') }}</textarea>
                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Briefly describe what you'll use this loan for</div>
                            </div>
                        </div>

                        <!-- Loan Calculation Preview -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light" id="loanPreview" style="display: none;">
                                    <div class="card-body">
                                        <h6><i class="fas fa-calculator me-2"></i>Loan Calculation Preview</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-1"><strong>Interest Rate:</strong></p>
                                                <span id="previewRate" class="text-primary">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1"><strong>Monthly Payment:</strong></p>
                                                <span id="previewMonthly" class="text-success">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1"><strong>Total Payment:</strong></p>
                                                <span id="previewTotal" class="text-info">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1"><strong>Total Interest:</strong></p>
                                                <span id="previewInterest" class="text-warning">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> 
                                        and understand that this is a cashless loan system.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-arrow-left me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i>Submit Application
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

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>ZeroCash Loan Terms</h6>
                <ul>
                    <li>All loans are processed cashlessly through your account</li>
                    <li>Interest rates are calculated automatically based on loan amount and term</li>
                    <li>Monthly payments are automatically calculated</li>
                    <li>Repayments can be made through your account balance or mobile money</li>
                    <li>Late payment fees may apply for overdue payments</li>
                    <li>Loan approval is subject to credit assessment</li>
                    <li>Funds will be transferred to your account upon approval</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update loan type information
    $('#loan_type_id').change(function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            const minAmount = selected.data('min-amount');
            const maxAmount = selected.data('max-amount');
            const minTerm = selected.data('min-term');
            const maxTerm = selected.data('max-term');
            
            $('#loanTypeInfo').html(`Amount: TSh ${minAmount.toLocaleString()} - TSh ${maxAmount.toLocaleString()}`);
            $('#termInfo').html(`Term: ${minTerm} - ${maxTerm} months`);
            
            // Update form limits
            $('#principal_amount').attr('min', minAmount).attr('max', maxAmount);
            $('#term_months').attr('min', minTerm).attr('max', maxTerm);
            
            calculateLoan();
        } else {
            $('#loanTypeInfo').html('');
            $('#termInfo').html('');
            $('#loanPreview').hide();
        }
    });

    // Calculate loan when inputs change
    $('#principal_amount, #term_months').on('input', calculateLoan);

    function calculateLoan() {
        const loanType = $('#loan_type_id').val();
        const amount = $('#principal_amount').val();
        const term = $('#term_months').val();

        if (loanType && amount && term) {
            $.post('{{ route("calculate.loan.details") }}', {
                _token: '{{ csrf_token() }}',
                loan_type_id: loanType,
                principal_amount: amount,
                term_months: term
            })
            .done(function(data) {
                $('#previewRate').text(data.interest_rate + '%');
                $('#previewMonthly').text(data.formatted_monthly_payment);
                $('#previewTotal').text(data.formatted_total_payment);
                $('#previewInterest').text(data.formatted_total_interest);
                $('#loanPreview').show();
            })
            .fail(function() {
                $('#loanPreview').hide();
            });
        } else {
            $('#loanPreview').hide();
        }
    }
});
</script>
@endpush
