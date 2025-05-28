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
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($loan->status === 'pending')
                                                <form action="{{ route('loans.approve', $loan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" 
                                                            onclick="return confirm('Are you sure you want to approve this loan?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('loans.reject', $loan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to reject this loan?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($loan->status === 'approved')
                                                <form action="{{ route('loans.disburse', $loan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-info"
                                                            onclick="return confirm('Are you sure you want to disburse this loan?')">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
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
