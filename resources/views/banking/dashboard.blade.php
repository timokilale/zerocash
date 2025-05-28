@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Banking Dashboard')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i>
            Add Customer
        </a>
        <a href="{{ route('accounts.create') }}" class="btn btn-success">
            <i class="fas fa-piggy-bank me-1"></i>
            Create Account
        </a>
        <a href="{{ route('transfer.form') }}" class="btn btn-warning">
            <i class="fas fa-exchange-alt me-1"></i>
            Transfer
        </a>
    </div>
@endsection

@section('content')
<!-- Welcome Section -->
<div class="welcome-section mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-hand-wave me-2"></i> Welcome back, {{ $user->first_name }}!</h2>
            <p class="mb-0">Role: <strong>{{ ucfirst($user->role) }}</strong> | Last login: {{ now()->format('M d, Y H:i') }}</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="text-white">
                <h5 class="mb-0">System Status</h5>
                <span class="badge bg-success">All Systems Operational</span>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['total_customers']) }}</h4>
                        <p class="mb-0">Total Customers</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['total_accounts']) }}</h4>
                        <p class="mb-0">Total Accounts</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-piggy-bank fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['total_transactions']) }}</h4>
                        <p class="mb-0">Transactions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exchange-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['total_loans']) }}</h4>
                        <p class="mb-0">Total Loans</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hand-holding-usd fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-dark text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0">TSh {{ number_format($stats['total_balance'], 2) }}</h5>
                        <p class="mb-0">Total Balance</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0">{{ number_format($stats['active_loans']) }}</h5>
                        <p class="mb-0">Active Loans</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0">{{ number_format($stats['pending_transactions']) }}</h5>
                        <p class="mb-0">Pending Transactions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Implemented -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Implemented Features
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-money-bill-transfer text-success me-2"></i>
                                <strong>Automatic Loan Disbursement</strong>
                                <p class="mb-0 text-muted small">Loans automatically transfer to customer accounts when approved</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-calculator text-success me-2"></i>
                                <strong>Transaction Fee Calculation</strong>
                                <p class="mb-0 text-muted small">Automatic fee calculation based on transaction type and amount</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                <strong>Deposit Authorization</strong>
                                <p class="mb-0 text-muted small">Only employees, CEO, and administrators can perform deposits</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-bell text-success me-2"></i>
                                <strong>Automatic Notifications</strong>
                                <p class="mb-0 text-muted small">Real-time notifications for transactions and registrations</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                <strong>Interest Rate Algorithm</strong>
                                <p class="mb-0 text-muted small">Dynamic interest rate calculation based on loan amount and term</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-hashtag text-success me-2"></i>
                                <strong>Account Number Generation</strong>
                                <p class="mb-0 text-muted small">Automatic unique account number generation for new accounts</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-key text-success me-2"></i>
                                <strong>Employee Password Generation</strong>
                                <p class="mb-0 text-muted small">Secure automatic password generation for new employees</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-user-key text-success me-2"></i>
                                <strong>Customer Password Generation</strong>
                                <p class="mb-0 text-muted small">Automatic password generation for new customer accounts</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-id-card text-success me-2"></i>
                                <strong>NIDA Integration</strong>
                                <p class="mb-0 text-muted small">Customer registration via National Identification Authority</p>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <i class="fas fa-user-slash text-success me-2"></i>
                                <strong>Employee Soft Deletion</strong>
                                <p class="mb-0 text-muted small">Employees moved to dormant state instead of permanent deletion</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
    }
</style>
@endpush
