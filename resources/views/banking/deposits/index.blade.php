@extends('layouts.app')

@section('title', 'Deposit Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-piggy-bank text-primary me-2"></i>
                    Deposit Management
                </h1>
                <a href="{{ route('deposits.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    New Deposit
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
                                        Total Deposits
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $deposits->total() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
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
                                        Pending Authorization
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $deposits->where('status', 'pending')->count() }}
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
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Completed Deposits
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $deposits->where('status', 'completed')->count() }}
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
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Amount
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        TSh {{ number_format($deposits->where('status', 'completed')->sum('amount'), 2) }}
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

            <!-- Deposits Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Deposits</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Deposited By</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                <tr>
                                    <td>
                                        <a href="{{ route('deposits.show', $deposit) }}" class="text-decoration-none">
                                            #{{ $deposit->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-initial bg-primary rounded-circle">
                                                    {{ substr($deposit->account->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $deposit->account->user->name }}</div>
                                                <small class="text-muted">{{ $deposit->account->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $deposit->account->account_number }}</td>
                                    <td class="text-success fw-bold">TSh {{ number_format($deposit->amount, 2) }}</td>
                                    <td>
                                        @php
                                            $methodClasses = [
                                                'cash' => 'success',
                                                'check' => 'info',
                                                'transfer' => 'warning',
                                                'online' => 'primary'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $methodClasses[$deposit->deposit_method] ?? 'secondary' }}">
                                            {{ ucfirst($deposit->deposit_method) }}
                                        </span>
                                    </td>
                                    <td>{{ $deposit->reference ?? '-' }}</td>
                                    <td>{{ $deposit->depositedBy->name }}</td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusClasses[$deposit->status] ?? 'secondary' }}">
                                            {{ ucfirst($deposit->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $deposit->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('deposits.show', $deposit) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($deposit->status === 'pending')
                                                <a href="{{ route('deposits.edit', $deposit) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('deposits.authorize', $deposit) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" 
                                                            onclick="return confirm('Are you sure you want to authorize this deposit?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="rejectDeposit({{ $deposit->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-piggy-bank fa-3x mb-3"></i>
                                            <p>No deposits found.</p>
                                            <a href="{{ route('deposits.create') }}" class="btn btn-primary">
                                                Create First Deposit
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
                        {{ $deposits->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Deposit Modal -->
<div class="modal fade" id="rejectDepositModal" tabindex="-1" aria-labelledby="rejectDepositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectDepositModalLabel">Reject Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectDepositForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" 
                                  rows="3" required placeholder="Please provide a reason for rejecting this deposit..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function rejectDeposit(depositId) {
    const form = document.getElementById('rejectDepositForm');
    form.action = `/deposits/${depositId}/reject`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectDepositModal'));
    modal.show();
}
</script>
@endpush
@endsection
