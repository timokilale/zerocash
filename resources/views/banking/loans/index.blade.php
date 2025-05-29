@extends('layouts.app')

@section('title', 'Loan Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-hand-holding-usd text-primary me-2"></i>
                    Loan Management
                </h1>
                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    New Loan Application
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Loans
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $loans->total() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Active Loans
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $loans->where('status', 'active')->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Approval
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $loans->where('status', 'pending')->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Amount
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        TSh {{ number_format($loans->sum('principal_amount'), 2) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loans Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Loans</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Loan Number</th>
                                    <th>Customer</th>
                                    <th>Account</th>
                                    <th>Loan Type</th>
                                    <th>Principal Amount</th>
                                    <th>Interest Rate</th>
                                    <th>Term</th>
                                    <th>Monthly Payment</th>
                                    <th>Outstanding</th>
                                    <th>Status</th>
                                    <th>Eligibility</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                <tr>
                                    <td>
                                        <a href="{{ route('loans.show', $loan) }}" class="text-decoration-none">
                                            {{ $loan->loan_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-initial bg-primary rounded-circle">
                                                    {{ substr($loan->account->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $loan->account->user->name }}</div>
                                                <small class="text-muted">{{ $loan->account->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $loan->account->account_number }}</td>
                                    <td>{{ $loan->loanType->name }}</td>
                                    <td>TSh {{ number_format($loan->principal_amount, 2) }}</td>
                                    <td>{{ $loan->interest_rate }}%</td>
                                    <td>{{ $loan->term_months }} months</td>
                                    <td>TSh {{ number_format($loan->monthly_payment, 2) }}</td>
                                    <td>TSh {{ number_format($loan->outstanding_balance, 2) }}</td>
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
                                        <span class="badge bg-{{ $statusClasses[$loan->status] ?? 'secondary' }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($loan->status === 'pending')
                                            <button class="btn btn-sm btn-outline-warning"
                                                    onclick="quickEligibilityCheck({{ $loan->account->user_id }}, {{ $loan->principal_amount }}, {{ $loan->term_months }}, this)"
                                                    title="Check eligibility">
                                                <i class="fas fa-shield-alt"></i>
                                            </button>
                                        @elseif($loan->status === 'rejected')
                                            <span class="text-muted">
                                                <i class="fas fa-times-circle" title="Rejected"></i>
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle" title="Processed"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($loan->status === 'pending')
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        data-bs-toggle="modal" data-bs-target="#quickApproveModal{{ $loan->id }}"
                                                        title="Approve loan">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal" data-bs-target="#quickRejectModal{{ $loan->id }}"
                                                        title="Reject loan">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            @if($loan->status === 'approved')
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal" data-bs-target="#quickDisburseModal{{ $loan->id }}"
                                                        title="Disburse loan">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-hand-holding-usd fa-3x mb-3"></i>
                                            <p>No loans found.</p>
                                            <a href="{{ route('loans.create') }}" class="btn btn-primary">
                                                Create First Loan Application
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $loans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Quick Action Modals for each loan -->
@foreach($loans as $loan)
    @if($loan->status === 'pending')
        <!-- Quick Approve Modal -->
        <div class="modal fade" id="quickApproveModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h6 class="modal-title">
                            <i class="fas fa-check-circle me-1"></i>Approve Loan
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-question-circle fa-3x text-success mb-3"></i>
                        <h6>Approve loan {{ $loan->loan_number }}?</h6>
                        <p class="text-muted mb-0">
                            <strong>{{ $loan->account->user->name }}</strong><br>
                            TSh {{ number_format($loan->principal_amount, 2) }}
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('loans.approve', $loan) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-check me-1"></i>Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Reject Modal -->
        <div class="modal fade" id="quickRejectModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h6 class="modal-title">
                            <i class="fas fa-times-circle me-1"></i>Reject Loan
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h6>Reject loan {{ $loan->loan_number }}?</h6>
                        <p class="text-muted mb-0">
                            <strong>{{ $loan->account->user->name }}</strong><br>
                            TSh {{ number_format($loan->principal_amount, 2) }}
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('loans.reject', $loan) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-ban me-1"></i>Reject
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($loan->status === 'approved')
        <!-- Quick Disburse Modal -->
        <div class="modal fade" id="quickDisburseModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h6 class="modal-title">
                            <i class="fas fa-money-bill-wave me-1"></i>Disburse Loan
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-hand-holding-usd fa-3x text-info mb-3"></i>
                        <h6>Disburse loan {{ $loan->loan_number }}?</h6>
                        <p class="text-muted mb-0">
                            <strong>{{ $loan->account->user->name }}</strong><br>
                            TSh {{ number_format($loan->principal_amount, 2) }}
                        </p>
                        <small class="text-muted">Amount will be transferred to account</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('loans.disburse', $loan) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-info">
                                <i class="fas fa-money-bill-wave me-1"></i>Disburse
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
function quickEligibilityCheck(userId, amount, termMonths, button) {
    const originalContent = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // Make AJAX request
    fetch('{{ route("loan-settings.eligibility-preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: new URLSearchParams({
            'user_id': userId,
            'amount': amount,
            'term_months': termMonths
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.eligible) {
            button.className = 'btn btn-sm btn-success';
            button.innerHTML = '<i class="fas fa-check-circle"></i>';
            button.title = `✅ Eligible (Risk: ${data.risk_score}/100, Interest: ${data.recommended_interest_adjustment > 0 ? '+' : ''}${data.recommended_interest_adjustment}%)`;
        } else {
            button.className = 'btn btn-sm btn-danger';
            button.innerHTML = '<i class="fas fa-times-circle"></i>';
            button.title = `❌ Not Eligible: ${data.reason}`;
        }
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        button.className = 'btn btn-sm btn-warning';
        button.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        button.title = 'Error checking eligibility';
        button.disabled = false;
    });
}
</script>
@endpush
