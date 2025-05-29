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
