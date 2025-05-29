@extends('layouts.app')

@section('title', 'Loan Details - ' . $loan->loan_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-hand-holding-usd text-primary me-2"></i>
                    Loan Details - {{ $loan->loan_number }}
                </h1>
                <div>
                    <a href="{{ route('loans.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Loans
                    </a>
                    @if($loan->status === 'pending')
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveLoanModal">
                                <i class="fas fa-check me-1"></i>
                                Approve
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectLoanModal">
                                <i class="fas fa-times me-1"></i>
                                Reject
                            </button>
                        </div>
                    @endif
                    @if($loan->status === 'approved')
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#disburseLoanModal">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            Disburse Loan
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Loan Information -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Loan Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Loan Number:</td>
                                            <td>{{ $loan->loan_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Loan Type:</td>
                                            <td>{{ $loan->loanType->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Principal Amount:</td>
                                            <td class="text-success fw-bold">TSh {{ number_format($loan->principal_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Interest Rate:</td>
                                            <td>{{ $loan->interest_rate }}% per annum</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Term:</td>
                                            <td>{{ $loan->term_months }} months</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Monthly Payment:</td>
                                            <td class="text-info fw-bold">TSh {{ number_format($loan->monthly_payment, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Outstanding Balance:</td>
                                            <td class="text-warning fw-bold">TSh {{ number_format($loan->outstanding_balance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'active' => 'success',
                                                        'completed' => 'secondary',
                                                        'defaulted' => 'danger',
                                                        'rejected' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusClasses[$loan->status] ?? 'secondary' }} fs-6">
                                                    {{ ucfirst($loan->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Branch:</td>
                                            <td>{{ $loan->branch->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Application Date:</td>
                                            <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Issued Date:</td>
                                            <td>{{ $loan->issued_date ? $loan->issued_date->format('M d, Y') : 'Not issued' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Due Date:</td>
                                            <td>{{ $loan->due_date ? $loan->due_date->format('M d, Y') : 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Auto Transferred:</td>
                                            <td>
                                                @if($loan->auto_transferred)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="avatar avatar-xl">
                                        <div class="avatar-initial bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="fs-3 text-white">{{ substr($loan->account->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Name:</td>
                                            <td>{{ $loan->account->user->name }}</td>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $loan->account->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>{{ $loan->account->user->phone }}</td>
                                            <td class="fw-bold">Account Number:</td>
                                            <td>{{ $loan->account->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Account Type:</td>
                                            <td>{{ $loan->account->accountType->name }}</td>
                                            <td class="fw-bold">Account Balance:</td>
                                            <td class="text-success fw-bold">TSh {{ number_format($loan->account->balance, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Loan Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Loan Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Loan Amount</label>
                                <div class="h5 text-primary">TSh {{ number_format($loan->principal_amount, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Interest</label>
                                <div class="h6 text-info">
                                    TSh {{ number_format(($loan->monthly_payment * $loan->term_months) - $loan->principal_amount, 2) }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Payable</label>
                                <div class="h6 text-warning">
                                    TSh {{ number_format($loan->monthly_payment * $loan->term_months, 2) }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Progress</label>
                                @php
                                    $totalPayment = $loan->monthly_payment * $loan->term_months;
                                    $paidAmount = $totalPayment - $loan->outstanding_balance;
                                    $progressPercentage = $totalPayment > 0 ? ($paidAmount / $totalPayment) * 100 : 0;
                                @endphp
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $progressPercentage }}%"
                                         aria-valuenow="{{ $progressPercentage }}"
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($progressPercentage, 1) }}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Paid: TSh {{ number_format($paidAmount, 2) }} /
                                    TSh {{ number_format($totalPayment, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('customers.show', $loan->account->user) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-user me-1"></i>
                                    View Customer
                                </a>
                                <a href="{{ route('accounts.show', $loan->account) }}" class="btn btn-outline-info">
                                    <i class="fas fa-university me-1"></i>
                                    View Account
                                </a>
                                @if($loan->status === 'active')
                                    <button class="btn btn-outline-success" onclick="alert('Payment feature coming soon!')">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Record Payment
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Eligibility Check -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-warning">
                                <i class="fas fa-shield-alt me-2"></i>Eligibility Check
                            </h6>
                            <button class="btn btn-sm btn-outline-warning" onclick="checkEligibility()">
                                <i class="fas fa-sync-alt me-1"></i>Check Now
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="eligibilityResult">
                                <div class="text-center text-muted">
                                    <i class="fas fa-search fa-2x mb-2"></i>
                                    <p class="mb-0">Click "Check Now" to verify customer eligibility for this loan application.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Type Details -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Loan Type Details</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{ $loan->loanType->name }}</strong></p>
                            <p class="text-muted">{{ $loan->loanType->description }}</p>
                            <hr>
                            <small class="text-muted">
                                <strong>Base Rate:</strong> {{ $loan->loanType->base_interest_rate }}%<br>
                                <strong>Amount Range:</strong> TSh {{ number_format($loan->loanType->min_amount) }} - TSh {{ number_format($loan->loanType->max_amount) }}<br>
                                <strong>Term Range:</strong> {{ $loan->loanType->min_term_months }} - {{ $loan->loanType->max_term_months }} months
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Confirmation Modals -->
@if($loan->status === 'pending')
<!-- Approve Loan Modal -->
<div class="modal fade" id="approveLoanModal" tabindex="-1" aria-labelledby="approveLoanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveLoanModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approve Loan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> Approving this loan will make it eligible for disbursement.
                </div>

                <h6>Loan Details:</h6>
                <ul class="list-unstyled">
                    <li><strong>Customer:</strong> {{ $loan->account->user->name }}</li>
                    <li><strong>Loan Number:</strong> {{ $loan->loan_number }}</li>
                    <li><strong>Amount:</strong> TSh {{ number_format($loan->principal_amount, 2) }}</li>
                    <li><strong>Term:</strong> {{ $loan->term_months }} months</li>
                    <li><strong>Monthly Payment:</strong> TSh {{ number_format($loan->monthly_payment, 2) }}</li>
                </ul>

                <p class="text-muted mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone. Please ensure you have reviewed the customer's eligibility.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form action="{{ route('loans.approve', $loan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Yes, Approve Loan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Loan Modal -->
<div class="modal fade" id="rejectLoanModal" tabindex="-1" aria-labelledby="rejectLoanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectLoanModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Reject Loan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Rejecting this loan will permanently decline the application.
                </div>

                <h6>Loan Details:</h6>
                <ul class="list-unstyled">
                    <li><strong>Customer:</strong> {{ $loan->account->user->name }}</li>
                    <li><strong>Loan Number:</strong> {{ $loan->loan_number }}</li>
                    <li><strong>Amount:</strong> TSh {{ number_format($loan->principal_amount, 2) }}</li>
                    <li><strong>Term:</strong> {{ $loan->term_months }} months</li>
                </ul>

                <div class="mb-3">
                    <label for="rejectionReason" class="form-label">Reason for Rejection (Optional):</label>
                    <textarea class="form-control" id="rejectionReason" name="reason" rows="3"
                              placeholder="Enter reason for rejection..."></textarea>
                </div>

                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    The customer will be notified of this decision.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form action="{{ route('loans.reject', $loan) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="reason" id="hiddenRejectionReason">
                    <button type="submit" class="btn btn-danger" onclick="document.getElementById('hiddenRejectionReason').value = document.getElementById('rejectionReason').value">
                        <i class="fas fa-ban me-1"></i>Yes, Reject Loan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@if($loan->status === 'approved')
<!-- Disburse Loan Modal -->
<div class="modal fade" id="disburseLoanModal" tabindex="-1" aria-labelledby="disburseLoanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="disburseLoanModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Disburse Loan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Ready for Disbursement:</strong> This loan has been approved and is ready to be disbursed.
                </div>

                <h6>Disbursement Details:</h6>
                <ul class="list-unstyled">
                    <li><strong>Customer:</strong> {{ $loan->account->user->name }}</li>
                    <li><strong>Account Number:</strong> {{ $loan->account->account_number }}</li>
                    <li><strong>Loan Amount:</strong> TSh {{ number_format($loan->principal_amount, 2) }}</li>
                    <li><strong>Current Account Balance:</strong> TSh {{ number_format($loan->account->balance, 2) }}</li>
                    <li><strong>New Balance After Disbursement:</strong> TSh {{ number_format($loan->account->balance + $loan->principal_amount, 2) }}</li>
                </ul>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    The loan amount will be automatically transferred to the customer's account.
                </div>

                <p class="text-muted mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action will activate the loan and start the repayment schedule.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form action="{{ route('loans.disburse', $loan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-money-bill-wave me-1"></i>Yes, Disburse Loan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function checkEligibility() {
    const resultDiv = document.getElementById('eligibilityResult');
    const checkButton = document.querySelector('[onclick="checkEligibility()"]');

    // Show loading state
    checkButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking...';
    checkButton.disabled = true;

    resultDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Analyzing customer eligibility...</p>
        </div>
    `;

    // Make AJAX request to check eligibility
    fetch('{{ route("loan-settings.eligibility-preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: new URLSearchParams({
            'user_id': '{{ $loan->account->user_id }}',
            'amount': '{{ $loan->principal_amount }}',
            'term_months': '{{ $loan->term_months }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        let html = '';

        if (data.eligible) {
            html = `
                <div class="alert alert-success mb-0">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>✅ ELIGIBLE FOR LOAN</strong>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Risk Score</small>
                            <div class="fw-bold">${data.risk_score || 'N/A'}/100</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Interest Adjustment</small>
                            <div class="fw-bold ${data.recommended_interest_adjustment > 0 ? 'text-warning' : data.recommended_interest_adjustment < 0 ? 'text-success' : 'text-muted'}">
                                ${data.recommended_interest_adjustment > 0 ? '+' : ''}${data.recommended_interest_adjustment || 0}%
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <small class="text-muted">
                        <strong>Details:</strong> ${data.details}
                    </small>
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger mb-0">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <strong>❌ NOT ELIGIBLE</strong>
                    </div>
                    <hr class="my-2">
                    <div class="mb-2">
                        <small class="text-muted">Reason</small>
                        <div class="fw-bold">${data.reason}</div>
                    </div>
                    <hr class="my-2">
                    <small class="text-muted">
                        <strong>Details:</strong> ${data.details}
                    </small>
                </div>
            `;
        }

        resultDiv.innerHTML = html;

        // Reset button
        checkButton.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Check Again';
        checkButton.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error checking eligibility</strong>
                <br><small>Please try again or contact system administrator.</small>
            </div>
        `;

        // Reset button
        checkButton.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Check Now';
        checkButton.disabled = false;
    });
}

// Auto-check eligibility for pending loans when page loads
document.addEventListener('DOMContentLoaded', function() {
    @if($loan->status === 'pending')
        // Auto-check for pending loans after a short delay
        setTimeout(function() {
            checkEligibility();
        }, 1000);
    @endif
});
</script>
@endpush
