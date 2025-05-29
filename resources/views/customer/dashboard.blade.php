@extends('layouts.customer')

@section('title', 'ZeroCash - Customer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-hand-wave me-2"></i> Welcome, {{ $user->first_name }}!</h2>
                        <p class="mb-0">Manage your loans cashlessly with ZeroCash Banking</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="account-info">
                            @if($account)
                                <h5 class="mb-0">Account Balance</h5>
                                <h3 class="text-success">TSh {{ number_format($account->balance, 2) }}</h3>
                                <small class="text-muted">Account: {{ $account->account_number }}</small>
                            @else
                                <p class="text-muted">No account found</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="action-card">
                <div class="card-body text-center">
                    <i class="fas fa-hand-holding-usd fa-3x text-primary mb-3"></i>
                    <h4>Apply for Loan</h4>
                    <p class="text-muted">Get instant loans with competitive rates</p>
                    <a href="{{ route('customer.loans.apply') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Apply Now
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="action-card">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                    <h4>Repay Loans</h4>
                    <p class="text-muted">Make cashless loan repayments</p>
                    <a href="{{ route('customer.loans.index') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-money-bill-wave me-2"></i>Repay Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Your Loans</h5>
                </div>
                <div class="card-body">
                    @if($loans->count() > 0)
                        <div class="row">
                            @foreach($loans as $loan)
                                <div class="col-md-4 mb-3">
                                    <div class="loan-summary-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ $loan->loanType->name }}</h6>
                                            <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-1">Loan #{{ $loan->loan_number }}</p>
                                        <p class="mb-1"><strong>Amount:</strong> TSh {{ number_format($loan->principal_amount, 2) }}</p>
                                        <p class="mb-1"><strong>Outstanding:</strong> TSh {{ number_format($loan->outstanding_balance, 2) }}</p>
                                        <p class="mb-2"><strong>Monthly Payment:</strong> TSh {{ number_format($loan->monthly_payment, 2) }}</p>
                                        @if($loan->status === 'active')
                                            <a href="{{ route('customer.loans.repay', $loan) }}" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-credit-card me-1"></i>Repay
                                            </a>
                                        @endif
                                        <a href="{{ route('customer.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-hand-holding-usd fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No loans yet</h5>
                            <p class="text-muted">Apply for your first loan to get started</p>
                            <a href="{{ route('customer.loans.apply') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Apply for Loan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notifications -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Notifications</h5>
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        @foreach($notifications as $notification)
                            <div class="notification-item">
                                <div class="d-flex">
                                    <div class="notification-icon">
                                        <i class="fas fa-{{ $notification->type === 'loan' ? 'hand-holding-usd' : ($notification->type === 'transaction' ? 'exchange-alt' : 'info-circle') }}"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6 class="mb-1">{{ $notification->title }}</h6>
                                        <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No notifications</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
    }
    
    .action-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }
    
    .action-card:hover {
        transform: translateY(-5px);
    }
    
    .loan-summary-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        border-left: 4px solid #007bff;
    }
    
    .notification-item {
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        background: #e3f2fd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #1976d2;
    }
    
    .notification-content {
        flex: 1;
    }
</style>
@endpush
