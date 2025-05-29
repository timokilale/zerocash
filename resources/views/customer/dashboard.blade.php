@extends('layouts.customer')

@section('title', 'ZeroCash - Customer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-hand-wave me-2"></i> Welcome, {{ auth()->user()->first_name }}!</h2>
                        <p class="mb-0">Complete banking services - Transfers, Loans, and more</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="account-info">
                            @if($accounts->count() > 0)
                                <h5 class="mb-0">Total Balance</h5>
                                <h3 class="text-success">TSh {{ number_format($totalBalance, 2) }}</h3>
                                <small class="text-muted">{{ $accounts->count() }} Account{{ $accounts->count() > 1 ? 's' : '' }}</small>
                            @else
                                <p class="text-muted">No accounts found</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="action-card">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-3x text-primary mb-3"></i>
                    <h5>Transfer Money</h5>
                    <p class="text-muted">Send money between accounts</p>
                    <a href="{{ route('customer.transfers.create') }}" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Transfer
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="action-card">
                <div class="card-body text-center">
                    <i class="fas fa-history fa-3x text-info mb-3"></i>
                    <h5>Transactions</h5>
                    <p class="text-muted">View transaction history</p>
                    <a href="{{ route('customer.transactions') }}" class="btn btn-info">
                        <i class="fas fa-list me-2"></i>View
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="action-card">
                <div class="card-body text-center">
                    <i class="fas fa-hand-holding-usd fa-3x text-warning mb-3"></i>
                    <h5>Apply for Loan</h5>
                    <p class="text-muted">Get instant loans</p>
                    <a href="{{ route('customer.loans.apply') }}" class="btn btn-warning">
                        <i class="fas fa-plus me-2"></i>Apply
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="action-card">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                    <h5>Repay Loans</h5>
                    <p class="text-muted">Make loan payments</p>
                    <a href="{{ route('customer.loans.index') }}" class="btn btn-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Repay
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
                    @if($activeLoans->count() > 0)
                        <div class="row">
                            @foreach($activeLoans as $loan)
                                <div class="col-md-4 mb-3">
                                    <div class="loan-summary-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ $loan->loanType->name }}</h6>
                                            <span class="badge bg-{{ $loan->status === 'approved' ? 'success' : ($loan->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-1">Loan #{{ $loan->loan_number }}</p>
                                        <p class="mb-1"><strong>Amount:</strong> TSh {{ number_format($loan->principal_amount, 2) }}</p>
                                        <p class="mb-1"><strong>Outstanding:</strong> TSh {{ number_format($loan->outstanding_balance, 2) }}</p>
                                        <p class="mb-2"><strong>Monthly Payment:</strong> TSh {{ number_format($loan->monthly_payment, 2) }}</p>
                                        @if(in_array($loan->status, ['approved', 'active']))
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
                            <h5 class="text-muted">No active loans</h5>
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

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transactions</h5>
                    <a href="{{ route('customer.transactions') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        @php
                                            $isCredit = $accounts->pluck('id')->contains($transaction->receiver_account_id);
                                            $amount = $isCredit ? $transaction->amount : -($transaction->amount + $transaction->fee_amount);
                                        @endphp
                                        <tr>
                                            <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->transactionType->code === 'TRF' ? 'primary' : ($transaction->transactionType->code === 'DEP' ? 'success' : 'info') }}">
                                                    {{ $transaction->transactionType->name }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>
                                                <span class="text-{{ $amount >= 0 ? 'success' : 'danger' }}">
                                                    {{ $amount >= 0 ? '+' : '' }}TSh {{ number_format(abs($amount), 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions yet</h5>
                            <p class="text-muted">Start by making a transfer or applying for a loan</p>
                        </div>
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
