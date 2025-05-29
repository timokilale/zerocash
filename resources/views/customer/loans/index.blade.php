@extends('layouts.customer')

@section('title', 'My Loans - ZeroCash')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-hand-holding-usd me-2"></i>My Loans</h3>
                <a href="{{ route('customer.loans.apply') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Apply for New Loan
                </a>
            </div>

            @if($loans->count() > 0)
                <div class="row">
                    @foreach($loans as $loan)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card loan-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $loan->loanType->name }}</h6>
                                    <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'pending' ? 'warning' : ($loan->status === 'completed' ? 'secondary' : 'danger')) }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-2">Loan #{{ $loan->loan_number }}</p>

                                    <div class="loan-details">
                                        <div class="detail-row">
                                            <span class="label">Principal Amount:</span>
                                            <span class="value">TSh {{ number_format($loan->principal_amount, 2) }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Interest Rate:</span>
                                            <span class="value">{{ $loan->interest_rate }}%</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Term:</span>
                                            <span class="value">{{ $loan->term_months }} months</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Monthly Payment:</span>
                                            <span class="value text-info">TSh {{ number_format($loan->monthly_payment, 2) }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Outstanding:</span>
                                            <span class="value text-warning">TSh {{ number_format($loan->outstanding_balance, 2) }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Applied Date:</span>
                                            <span class="value">{{ $loan->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @if($loan->issued_date)
                                            <div class="detail-row">
                                                <span class="label">Issued Date:</span>
                                                <span class="value">{{ \Carbon\Carbon::parse($loan->issued_date)->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                        @if($loan->due_date)
                                            <div class="detail-row">
                                                <span class="label">Due Date:</span>
                                                <span class="value text-danger">{{ \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if($loan->status === 'active')
                                        <div class="progress mb-3">
                                            @php
                                                $totalPayment = $loan->monthly_payment * $loan->term_months;
                                                $paidAmount = $totalPayment - $loan->outstanding_balance;
                                                $paidPercentage = $totalPayment > 0 ? ($paidAmount / $totalPayment) * 100 : 0;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar"
                                                 style="width: {{ $paidPercentage }}%"
                                                 aria-valuenow="{{ $paidPercentage }}"
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ number_format($paidPercentage, 1) }}% Paid
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('customer.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                        @if($loan->status === 'active')
                                            <a href="{{ route('customer.loans.repay', $loan) }}" class="btn btn-sm btn-success flex-fill">
                                                <i class="fas fa-credit-card me-1"></i>Repay
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Loan Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Loan Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="summary-item">
                                            <h4 class="text-primary">{{ $loans->count() }}</h4>
                                            <p class="mb-0">Total Loans</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-item">
                                            <h4 class="text-success">{{ $loans->where('status', 'active')->count() }}</h4>
                                            <p class="mb-0">Active Loans</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-item">
                                            <h4 class="text-info">TSh {{ number_format($loans->sum('principal_amount'), 2) }}</h4>
                                            <p class="mb-0">Total Borrowed</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-item">
                                            <h4 class="text-warning">TSh {{ number_format($loans->sum('outstanding_balance'), 2) }}</h4>
                                            <p class="mb-0">Total Outstanding</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hand-holding-usd fa-5x text-muted mb-4"></i>
                        <h4 class="text-muted">No Loans Yet</h4>
                        <p class="text-muted mb-4">You haven't applied for any loans yet. Get started with your first loan application.</p>
                        <a href="{{ route('customer.loans.apply') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Apply for Your First Loan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .loan-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .loan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .loan-details {
        margin-bottom: 15px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 5px;
        border-bottom: 1px solid #f0f0f0;
    }

    .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .detail-row .label {
        font-weight: 500;
        color: #666;
        font-size: 0.9rem;
    }

    .detail-row .value {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .summary-item {
        padding: 20px;
        border-radius: 10px;
        background: #f8f9fa;
        margin-bottom: 15px;
    }

    .summary-item h4 {
        margin-bottom: 5px;
        font-weight: bold;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
    }

    .card-header .badge {
        background: rgba(255,255,255,0.2) !important;
        color: white !important;
    }
</style>
@endpush
