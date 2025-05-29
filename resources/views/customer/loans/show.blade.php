@extends('layouts.customer')

@section('title', 'Loan Details - ZeroCash')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-file-invoice-dollar me-2"></i>Loan Details</h3>
                <div>
                    @if($loan->status === 'active')
                        <a href="{{ route('customer.loans.repay', $loan) }}" class="btn btn-success">
                            <i class="fas fa-credit-card me-1"></i>Make Repayment
                        </a>
                    @endif
                    <a href="{{ route('customer.loans.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Loans
                    </a>
                </div>
            </div>

            <!-- Loan Status Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card status-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-1">{{ $loan->loanType->name }}</h4>
                                    <p class="text-muted mb-0">Loan #{{ $loan->loan_number }}</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge badge-status bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'pending' ? 'warning' : ($loan->status === 'completed' ? 'secondary' : 'danger')) }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Loan Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="label">Principal Amount:</span>
                                <span class="value">TSh {{ number_format($loan->principal_amount, 2) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Interest Rate:</span>
                                <span class="value">{{ $loan->interest_rate }}%</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Loan Term:</span>
                                <span class="value">{{ $loan->term_months }} months</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Monthly Payment:</span>
                                <span class="value text-info">TSh {{ number_format($loan->monthly_payment, 2) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Outstanding Balance:</span>
                                <span class="value text-warning">TSh {{ number_format($loan->outstanding_balance, 2) }}</span>
                            </div>
                            @if($loan->issued_date)
                                <div class="info-row">
                                    <span class="label">Issue Date:</span>
                                    <span class="value">{{ \Carbon\Carbon::parse($loan->issued_date)->format('M d, Y') }}</span>
                                </div>
                            @endif
                            <div class="info-row">
                                <span class="label">Application Date:</span>
                                <span class="value">{{ $loan->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Loan Progress</h5>
                        </div>
                        <div class="card-body">
                            @if($loan->status === 'active' || $loan->status === 'completed')
                                @php
                                    $paidAmount = $loan->principal_amount - $loan->outstanding_balance;
                                    $paidPercentage = ($paidAmount / $loan->principal_amount) * 100;
                                @endphp
                                
                                <div class="progress-container mb-3">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $paidPercentage }}%" 
                                             aria-valuenow="{{ $paidPercentage }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            {{ number_format($paidPercentage, 1) }}%
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="progress-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Amount Paid:</span>
                                        <span class="stat-value text-success">TSh {{ number_format($paidAmount, 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Remaining:</span>
                                        <span class="stat-value text-warning">TSh {{ number_format($loan->outstanding_balance, 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Progress:</span>
                                        <span class="stat-value text-info">{{ number_format($paidPercentage, 1) }}% Complete</span>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Loan Pending Approval</h6>
                                    <p class="text-muted">Your loan application is being reviewed. You'll be notified once it's approved.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Purpose -->
            @if($loan->purpose)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Loan Purpose</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $loan->purpose }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Account Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-university me-2"></i>Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <span class="label">Account Number:</span>
                                        <span class="value">{{ $loan->account->account_number }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Account Type:</span>
                                        <span class="value">{{ $loan->account->accountType->name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <span class="label">Current Balance:</span>
                                        <span class="value text-success">TSh {{ number_format($loan->account->balance, 2) }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Branch:</span>
                                        <span class="value">{{ $loan->branch->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            @if($loan->status === 'active')
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="mb-3">Quick Actions</h5>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('customer.loans.repay', $loan) }}" class="btn btn-success btn-lg">
                                        <i class="fas fa-credit-card me-2"></i>Make Payment
                                    </a>
                                    <a href="{{ route('customer.loans.apply') }}" class="btn btn-outline-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>Apply for Another Loan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .status-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }
    
    .badge-status {
        font-size: 1rem;
        padding: 8px 16px;
        border-radius: 20px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-row .label {
        font-weight: 500;
        color: #666;
    }
    
    .info-row .value {
        font-weight: 600;
        text-align: right;
    }
    
    .progress {
        height: 15px;
        border-radius: 10px;
        background-color: #e9ecef;
    }
    
    .progress-bar {
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .progress-stats {
        margin-top: 15px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .stat-label {
        font-weight: 500;
        color: #666;
    }
    
    .stat-value {
        font-weight: 600;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
    }
</style>
@endpush
