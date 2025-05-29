@extends('layouts.app')

@section('title', 'Transactions')
@section('page-title', 'Transaction Management')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            New Transaction
        </a>
        <a href="{{ route('transfer.form') }}" class="btn btn-success">
            <i class="fas fa-exchange-alt me-1"></i>
            Transfer Money
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Summary Cards -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $transactions->total() }}</h4>
                        <p class="mb-0">Total Transactions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exchange-alt fa-2x"></i>
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
                        <h4 class="mb-0">{{ $transactions->where('status', 'completed')->count() }}</h4>
                        <p class="mb-0">Completed</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
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
                        <h4 class="mb-0">{{ $transactions->where('status', 'pending')->count() }}</h4>
                        <p class="mb-0">Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
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
                        <h4 class="mb-0">TSh {{ number_format($transactions->sum('amount'), 2) }}</h4>
                        <p class="mb-0">Total Volume</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    All Transactions
                </h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <select class="form-select form-select-sm" id="typeFilter">
                        <option value="">All Types</option>
                        @foreach(\App\Models\TransactionType::all() as $type)
                            <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @if(request()->hasAny(['status', 'type', 'account', 'customer', 'date_from', 'date_to']))
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>
                            Clear Filters
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Amount</th>
                                    <th>Fee</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <strong>{{ $transaction->reference }}</strong>
                                        </td>
                                        <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $transaction->transactionType->name }}</span>
                                        </td>
                                        <td>
                                            @if($transaction->senderAccount)
                                                <div>
                                                    <strong>{{ $transaction->senderAccount->account_number }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $transaction->senderAccount->user->first_name }} {{ $transaction->senderAccount->user->last_name }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->receiverAccount)
                                                <div>
                                                    <strong>{{ $transaction->receiverAccount->account_number }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $transaction->receiverAccount->user->first_name }} {{ $transaction->receiverAccount->user->last_name }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                TSh {{ number_format($transaction->amount, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="text-warning">
                                                TSh {{ number_format($transaction->fee_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                TSh {{ number_format($transaction->total_amount, 2) }}
                                            </strong>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('transactions.show', $transaction->id) }}"
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($transaction->status === 'pending')
                                                    <a href="{{ route('transactions.edit', $transaction->id) }}"
                                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
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
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No transactions found</h5>
                        <p class="text-muted">Start by creating your first transaction.</p>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Create First Transaction
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#statusFilter, #typeFilter').change(function() {
        const status = $('#statusFilter').val();
        const type = $('#typeFilter').val();

        let url = new URL(window.location);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }

        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }

        window.location = url;
    });
});
</script>
@endpush
