@extends('layouts.customer')

@section('title', 'Transaction History')

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

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-history me-2"></i>Transaction History</h2>
                    <p class="text-muted mb-0">View and manage your transaction history</p>
                </div>
                <div>
                    <a href="{{ route('customer.transactions.statement-options') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="btn btn-outline-success"
                       title="Choose format and download your complete bank statement with running balance">
                        <i class="fas fa-print me-1"></i>Print Bank Statement
                    </a>
                    <a href="{{ route('customer.transfers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>New Transfer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">TSh {{ number_format($summary['total_credit'], 2) }}</h4>
                            <p class="mb-0">Total Credits</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">TSh {{ number_format($summary['total_debit'], 2) }}</h4>
                            <p class="mb-0">Total Debits</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">TSh {{ number_format($summary['total_fees'], 2) }}</h4>
                            <p class="mb-0">Total Fees</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Transactions</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('customer.transactions') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="account_id" class="form-label">Account</label>
                                <select name="account_id" id="account_id" class="form-select">
                                    <option value="">All Accounts</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_number }} - {{ $account->accountType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="transaction_type" class="form-label">Type</label>
                                <select name="transaction_type" id="transaction_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="TRF" {{ request('transaction_type') == 'TRF' ? 'selected' : '' }}>Transfer</option>
                                    <option value="DEP" {{ request('transaction_type') == 'DEP' ? 'selected' : '' }}>Deposit</option>
                                    <option value="WTH" {{ request('transaction_type') == 'WTH' ? 'selected' : '' }}>Withdrawal</option>
                                    <option value="LRP" {{ request('transaction_type') == 'LRP' ? 'selected' : '' }}>Loan Repayment</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Description, Reference..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @if(request()->hasAny(['account_id', 'transaction_type', 'date_from', 'date_to', 'search']))
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="{{ route('customer.transactions') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Transactions ({{ $transactions->total() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Transaction #</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Balance Change</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        @php
                                            $isCredit = $accounts->pluck('id')->contains($transaction->receiver_account_id);
                                            $balanceChange = $isCredit ? $transaction->amount : -($transaction->amount + $transaction->fee_amount);
                                        @endphp
                                        <tr>
                                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <small class="text-muted">{{ $transaction->transaction_number }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->transactionType->code === 'TRF' ? 'primary' : ($transaction->transactionType->code === 'DEP' ? 'success' : ($transaction->transactionType->code === 'WTH' ? 'danger' : 'info')) }}">
                                                    {{ $transaction->transactionType->name }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>
                                                @if($transaction->reference)
                                                    <small class="text-muted">{{ $transaction->reference }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>TSh {{ number_format($transaction->amount, 2) }}</td>
                                            <td>TSh {{ number_format($transaction->fee_amount, 2) }}</td>
                                            <td>
                                                <span class="text-{{ $balanceChange >= 0 ? 'success' : 'danger' }} fw-bold">
                                                    {{ $balanceChange >= 0 ? '+' : '' }}TSh {{ number_format($balanceChange, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                @switch($transaction->status)
                                                    @case('completed')
                                                        <span class="badge bg-success">Completed</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge bg-danger">Failed</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-secondary">Cancelled</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-dark">{{ ucfirst($transaction->status) }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $transactions->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['account_id', 'transaction_type', 'date_from', 'date_to', 'search']))
                                    Try adjusting your filters or
                                    <a href="{{ route('customer.transactions') }}">clear all filters</a>.
                                @else
                                    Start by making your first transfer.
                                @endif
                            </p>
                            @if(!request()->hasAny(['account_id', 'transaction_type', 'date_from', 'date_to', 'search']))
                                <a href="{{ route('customer.transfers.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Make First Transfer
                                </a>
                            @endif
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
    .table th {
        border-top: none;
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>
@endpush
