@extends('layouts.customer')

@section('title', 'Account Details')

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
                    <h2><i class="fas fa-piggy-bank me-2"></i>Account Details</h2>
                    <p class="text-muted mb-0">{{ $account->accountType->name }} - {{ $account->account_number }}</p>
                </div>
                <div>
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                    <a href="{{ route('customer.transfers.create') }}" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Transfer Money
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Summary -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Account Number:</strong></td>
                                    <td>{{ $account->account_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Account Type:</strong></td>
                                    <td>{{ $account->accountType->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Branch:</strong></td>
                                    <td>{{ $account->branch->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Opened Date:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($account->opened_date)->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current Balance:</strong></td>
                                    <td>
                                        <span class="text-success fw-bold fs-5">
                                            TSh {{ number_format($account->balance, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $account->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-wallet fa-3x mb-3"></i>
                    <h3>TSh {{ number_format($account->balance, 2) }}</h3>
                    <p class="mb-0">Available Balance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Trend Chart -->
    @if(count($balanceTrend) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Balance Trend (Last 30 Transactions)</h5>
                </div>
                <div class="card-body">
                    <canvas id="balanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Transactions ({{ $transactions->total() }})
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('customer.transactions') }}?account_id={{ $account->id }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View All
                        </a>
                        <a href="{{ route('customer.transactions.statement-options') }}?account_id={{ $account->id }}"
                           class="btn btn-sm btn-outline-success"
                           title="Choose format and download complete bank statement with running balance">
                            <i class="fas fa-print me-1"></i>Print Bank Statement
                        </a>
                    </div>
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
                                        <th>Amount</th>
                                        <th>Balance Change</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        @php
                                            $isCredit = $transaction->receiver_account_id === $account->id;
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
                                            <td>TSh {{ number_format($transaction->amount, 2) }}</td>
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
                            {{ $transactions->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions found</h5>
                            <p class="text-muted">This account hasn't had any transactions yet.</p>
                            <a href="{{ route('customer.transfers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Make First Transaction
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(count($balanceTrend) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('balanceChart').getContext('2d');
    const balanceData = @json($balanceTrend);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: balanceData.map(item => item.date),
            datasets: [{
                label: 'Account Balance',
                data: balanceData.map(item => item.balance),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'TSh ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Balance: TSh ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush

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

    .table-borderless td {
        border: none;
        padding: 0.5rem 0;
    }
</style>
@endpush
