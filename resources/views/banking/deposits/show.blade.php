@extends('layouts.app')

@section('title', 'Deposit Details - #' . $deposit->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-piggy-bank text-primary me-2"></i>
                    Deposit Details - #{{ $deposit->id }}
                </h1>
                <div>
                    <a href="{{ route('deposits.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Deposits
                    </a>
                    @if($deposit->status === 'pending')
                        <div class="btn-group">
                            <a href="{{ route('deposits.edit', $deposit) }}" class="btn btn-warning me-2">
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                            <form action="{{ route('deposits.authorize', $deposit) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success me-2" 
                                        onclick="return confirm('Are you sure you want to authorize this deposit?')">
                                    <i class="fas fa-check me-1"></i>
                                    Authorize
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" onclick="rejectDeposit({{ $deposit->id }})">
                                <i class="fas fa-times me-1"></i>
                                Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Deposit Information -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Deposit Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Deposit ID:</td>
                                            <td>#{{ $deposit->id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Amount:</td>
                                            <td class="text-success fw-bold fs-5">TSh {{ number_format($deposit->amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Deposit Method:</td>
                                            <td>
                                                @php
                                                    $methodClasses = [
                                                        'cash' => 'success',
                                                        'check' => 'info',
                                                        'transfer' => 'warning',
                                                        'online' => 'primary'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $methodClasses[$deposit->deposit_method] ?? 'secondary' }} fs-6">
                                                    {{ ucfirst($deposit->deposit_method) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Reference:</td>
                                            <td>{{ $deposit->reference ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'warning',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusClasses[$deposit->status] ?? 'secondary' }} fs-6">
                                                    {{ ucfirst($deposit->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Deposited By:</td>
                                            <td>{{ $deposit->depositedBy->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Created Date:</td>
                                            <td>{{ $deposit->created_at->format('M d, Y \a\t g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Last Updated:</td>
                                            <td>{{ $deposit->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                        </tr>
                                        @if($deposit->notes)
                                        <tr>
                                            <td class="fw-bold">Notes:</td>
                                            <td>{{ $deposit->notes }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Account Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Customer & Account Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="avatar avatar-xl">
                                        <div class="avatar-initial bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="fs-3 text-white">{{ substr($deposit->account->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Customer Name:</td>
                                            <td>{{ $deposit->account->user->name }}</td>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $deposit->account->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>{{ $deposit->account->user->phone }}</td>
                                            <td class="fw-bold">Account Number:</td>
                                            <td>{{ $deposit->account->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Account Type:</td>
                                            <td>{{ $deposit->account->accountType->name }}</td>
                                            <td class="fw-bold">Account Status:</td>
                                            <td>
                                                <span class="badge bg-{{ $deposit->account->status === 'active' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($deposit->account->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Current Balance:</td>
                                            <td class="text-success fw-bold">TSh {{ number_format($deposit->account->balance, 2) }}</td>
                                            <td class="fw-bold">Balance After Deposit:</td>
                                            <td class="text-info fw-bold">
                                                @if($deposit->status === 'completed')
                                                    TSh {{ number_format($deposit->account->balance, 2) }}
                                                @else
                                                    TSh {{ number_format($deposit->account->balance + $deposit->amount, 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deposit Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Deposit Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Deposit Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Deposit Amount</label>
                                <div class="h4 text-success">TSh {{ number_format($deposit->amount, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Method</label>
                                <div class="h6 text-info">{{ ucfirst($deposit->deposit_method) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Status</label>
                                <div class="h6">
                                    <span class="badge bg-{{ $statusClasses[$deposit->status] ?? 'secondary' }} fs-6">
                                        {{ ucfirst($deposit->status) }}
                                    </span>
                                </div>
                            </div>
                            @if($deposit->reference)
                            <div class="mb-3">
                                <label class="form-label text-muted">Reference</label>
                                <div class="h6 text-warning">{{ $deposit->reference }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('customers.show', $deposit->account->user) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-user me-1"></i>
                                    View Customer
                                </a>
                                <a href="{{ route('accounts.show', $deposit->account) }}" class="btn btn-outline-info">
                                    <i class="fas fa-university me-1"></i>
                                    View Account
                                </a>
                                @if($deposit->status === 'pending')
                                    <a href="{{ route('deposits.edit', $deposit) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit me-1"></i>
                                        Edit Deposit
                                    </a>
                                @endif
                                <button class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>
                                    Print Receipt
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit Method Details -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Method Details</h6>
                        </div>
                        <div class="card-body">
                            @switch($deposit->deposit_method)
                                @case('cash')
                                    <p><strong>Cash Deposit</strong></p>
                                    <p class="text-muted">Physical cash deposit processed at the branch.</p>
                                    @break
                                @case('check')
                                    <p><strong>Check Deposit</strong></p>
                                    <p class="text-muted">Bank check or personal check deposit.</p>
                                    @if($deposit->reference)
                                        <p class="text-muted"><strong>Check #:</strong> {{ $deposit->reference }}</p>
                                    @endif
                                    @break
                                @case('transfer')
                                    <p><strong>Bank Transfer</strong></p>
                                    <p class="text-muted">Electronic transfer from another bank account.</p>
                                    @if($deposit->reference)
                                        <p class="text-muted"><strong>Transfer ID:</strong> {{ $deposit->reference }}</p>
                                    @endif
                                    @break
                                @case('online')
                                    <p><strong>Online Payment</strong></p>
                                    <p class="text-muted">Digital payment through online banking or mobile money.</p>
                                    @if($deposit->reference)
                                        <p class="text-muted"><strong>Transaction ID:</strong> {{ $deposit->reference }}</p>
                                    @endif
                                    @break
                            @endswitch
                        </div>
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
