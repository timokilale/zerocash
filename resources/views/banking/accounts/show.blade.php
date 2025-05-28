@extends('layouts.app')

@section('title', 'Account Details - ' . $account->account_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-university text-primary me-2"></i>
                    Account Details - {{ $account->account_number }}
                </h1>
                <div>
                    <a href="{{ route('accounts.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Accounts
                    </a>
                    <a href="{{ route('accounts.edit', $account) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        Edit Account
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Account Information -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Account Number:</td>
                                            <td class="text-primary fw-bold">{{ $account->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Account Type:</td>
                                            <td>{{ $account->accountType->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Current Balance:</td>
                                            <td class="text-success fw-bold fs-5">TSh {{ number_format($account->balance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'active' => 'success',
                                                        'inactive' => 'warning',
                                                        'frozen' => 'danger',
                                                        'closed' => 'secondary'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusClasses[$account->status] ?? 'secondary' }} fs-6">
                                                    {{ ucfirst($account->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Branch:</td>
                                            <td>{{ $account->branch->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Opened Date:</td>
                                            <td>{{ $account->opened_date->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Account Age:</td>
                                            <td>{{ $account->opened_date->diffForHumans() }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Last Updated:</td>
                                            <td>{{ $account->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Holder Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Account Holder Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="avatar avatar-xl">
                                        <div class="avatar-initial bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="fs-3 text-white">{{ substr($account->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Name:</td>
                                            <td>{{ $account->user->name }}</td>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $account->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>{{ $account->user->phone }}</td>
                                            <td class="fw-bold">Customer Since:</td>
                                            <td>{{ $account->user->created_at->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Address:</td>
                                            <td colspan="3">{{ $account->user->address }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                            <a href="{{ route('transactions.index', ['account' => $account->id]) }}" class="btn btn-sm btn-outline-primary">
                                View All Transactions
                            </a>
                        </div>
                        <div class="card-body">
                            @if($account->senderTransactions->count() > 0 || $account->receiverTransactions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
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
                                            @php
                                                $allTransactions = $account->senderTransactions->merge($account->receiverTransactions)
                                                    ->sortByDesc('created_at')
                                                    ->take(5);
                                            @endphp
                                            @foreach($allTransactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                                <td>{{ $transaction->transactionType->name }}</td>
                                                <td>{{ $transaction->description }}</td>
                                                <td class="{{ $transaction->receiver_account_id == $account->id ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->receiver_account_id == $account->id ? '+' : '-' }}TSh {{ number_format($transaction->amount, 2) }}
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
                                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No transactions found for this account.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Account Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Account Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Balance</label>
                                <div class="h4 text-success">TSh {{ number_format($account->balance, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Account Type</label>
                                <div class="h6 text-info">{{ $account->accountType->name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Account Status</label>
                                <div class="h6">
                                    <span class="badge bg-{{ $statusClasses[$account->status] ?? 'secondary' }} fs-6">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Account Age</label>
                                <div class="h6 text-warning">{{ $account->opened_date->diffForHumans() }}</div>
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
                                <a href="{{ route('customers.show', $account->user) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-user me-1"></i>
                                    View Customer
                                </a>
                                <a href="{{ route('transactions.transfer') }}?from_account={{ $account->id }}" class="btn btn-outline-success">
                                    <i class="fas fa-exchange-alt me-1"></i>
                                    Transfer Money
                                </a>
                                <a href="{{ route('deposits.create') }}?account={{ $account->id }}" class="btn btn-outline-info">
                                    <i class="fas fa-piggy-bank me-1"></i>
                                    Make Deposit
                                </a>
                                <a href="{{ route('accounts.edit', $account) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-1"></i>
                                    Edit Account
                                </a>
                                <button class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>
                                    Print Statement
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Account Statistics -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Account Statistics</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $totalTransactions = $account->senderTransactions->count() + $account->receiverTransactions->count();
                                $totalDeposits = $account->receiverTransactions->where('transactionType.code', 'DEP')->sum('amount');
                                $totalWithdrawals = $account->senderTransactions->where('transactionType.code', 'WTH')->sum('amount');
                            @endphp
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Transactions</label>
                                <div class="h6 text-primary">{{ $totalTransactions }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Deposits</label>
                                <div class="h6 text-success">TSh {{ number_format($totalDeposits, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Withdrawals</label>
                                <div class="h6 text-danger">TSh {{ number_format($totalWithdrawals, 2) }}</div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-muted">Net Flow</label>
                                <div class="h6 {{ ($totalDeposits - $totalWithdrawals) >= 0 ? 'text-success' : 'text-danger' }}">
                                    TSh {{ number_format($totalDeposits - $totalWithdrawals, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Branch Information</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">{{ $account->branch->name }}</h6>
                            <p class="text-muted mb-2">{{ $account->branch->address }}</p>
                            @if($account->branch->phone)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $account->branch->phone }}
                                </p>
                            @endif
                            @if($account->branch->email)
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $account->branch->email }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
