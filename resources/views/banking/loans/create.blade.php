@extends('layouts.app')

@section('title', 'New Loan Application')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-hand-holding-usd text-primary me-2"></i>
                    New Loan Application
                </h1>
                <a href="{{ route('loans.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Loans
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Loan Application Details</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('loans.store') }}" method="POST" id="loanForm">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="account_id" class="form-label">Customer Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('account_id') is-invalid @enderror" 
                                                id="account_id" name="account_id" required>
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_number }} - {{ $account->user->name }} 
                                                    ({{ $account->accountType->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
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
                                                    {{ $loanType->name }} 
                                                    ({{ $loanType->base_interest_rate }}% base rate)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('loan_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="principal_amount" class="form-label">Principal Amount (TSh) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('principal_amount') is-invalid @enderror" 
                                               id="principal_amount" name="principal_amount" 
                                               value="{{ old('principal_amount') }}" 
                                               min="1000" step="0.01" required>
                                        @error('principal_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text" id="amountLimits"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="term_months" class="form-label">Loan Term (Months) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                               id="term_months" name="term_months" 
                                               value="{{ old('term_months') }}" 
                                               min="1" max="360" required>
                                        @error('term_months')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text" id="termLimits"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select @error('branch_id') is-invalid @enderror" 
                                                id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }} ({{ $branch->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-info" id="calculateBtn">
                                        <i class="fas fa-calculator me-1"></i>
                                        Calculate Loan Details
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Submit Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Loan Calculation Results -->
                    <div class="card shadow mb-4" id="calculationResults" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-calculator me-1"></i>
                                Loan Calculation
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Interest Rate</label>
                                <div class="h5 text-primary" id="calculatedRate">-</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Monthly Payment</label>
                                <div class="h5 text-success" id="calculatedPayment">-</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Interest</label>
                                <div class="h6 text-info" id="totalInterest">-</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Amount</label>
                                <div class="h6 text-warning" id="totalAmount">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Type Information -->
                    <div class="card shadow mb-4" id="loanTypeInfo" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Loan Type Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="loanTypeDetails"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update loan type information when loan type changes
    $('#loan_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const minAmount = selectedOption.data('min-amount');
            const maxAmount = selectedOption.data('max-amount');
            const minTerm = selectedOption.data('min-term');
            const maxTerm = selectedOption.data('max-term');
            const baseRate = selectedOption.data('base-rate');

            $('#amountLimits').text(`Amount range: TSh ${minAmount.toLocaleString()} - TSh ${maxAmount.toLocaleString()}`);
            $('#termLimits').text(`Term range: ${minTerm} - ${maxTerm} months`);
            
            $('#principal_amount').attr('min', minAmount).attr('max', maxAmount);
            $('#term_months').attr('min', minTerm).attr('max', maxTerm);

            // Show loan type info
            $('#loanTypeDetails').html(`
                <p><strong>Base Interest Rate:</strong> ${baseRate}%</p>
                <p><strong>Amount Range:</strong> TSh ${minAmount.toLocaleString()} - TSh ${maxAmount.toLocaleString()}</p>
                <p><strong>Term Range:</strong> ${minTerm} - ${maxTerm} months</p>
            `);
            $('#loanTypeInfo').show();
        } else {
            $('#amountLimits').text('');
            $('#termLimits').text('');
            $('#loanTypeInfo').hide();
        }
    });

    // Calculate loan details
    $('#calculateBtn').click(function() {
        const loanTypeId = $('#loan_type_id').val();
        const principalAmount = $('#principal_amount').val();
        const termMonths = $('#term_months').val();

        if (!loanTypeId || !principalAmount || !termMonths) {
            alert('Please fill in loan type, principal amount, and term months first.');
            return;
        }

        $.ajax({
            url: '{{ route("calculate.loan.details") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                loan_type_id: loanTypeId,
                principal_amount: principalAmount,
                term_months: termMonths
            },
            success: function(response) {
                $('#calculatedRate').text(response.interest_rate + '%');
                $('#calculatedPayment').text('TSh ' + response.monthly_payment.toLocaleString());
                $('#totalInterest').text('TSh ' + response.total_interest.toLocaleString());
                $('#totalAmount').text('TSh ' + response.total_amount.toLocaleString());
                $('#calculationResults').show();
            },
            error: function(xhr) {
                alert('Error calculating loan details. Please try again.');
            }
        });
    });
});
</script>
@endpush
@endsection
