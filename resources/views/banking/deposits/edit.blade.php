@extends('layouts.app')

@section('title', 'Edit Deposit - #' . $deposit->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-piggy-bank text-primary me-2"></i>
                    Edit Deposit - #{{ $deposit->id }}
                </h1>
                <div>
                    <a href="{{ route('deposits.show', $deposit) }}" class="btn btn-secondary me-2">
                        <i class="fas fa-eye me-1"></i>
                        View Deposit
                    </a>
                    <a href="{{ route('deposits.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Deposits
                    </a>
                </div>
            </div>

            @if($deposit->status !== 'pending')
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This deposit has already been processed and cannot be edited.
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Edit Deposit Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('deposits.update', $deposit) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="account_id" class="form-label">Customer Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('account_id') is-invalid @enderror" 
                                                id="account_id" name="account_id" required 
                                                {{ $deposit->status !== 'pending' ? 'disabled' : '' }}>
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" 
                                                        data-customer="{{ $account->user->name }}"
                                                        data-balance="{{ $account->balance }}"
                                                        {{ old('account_id', $deposit->account_id) == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_number }} - {{ $account->user->name }} 
                                                    ({{ $account->accountType->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="amount" class="form-label">Deposit Amount (TSh) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount', $deposit->amount) }}" 
                                               min="1" step="0.01" required 
                                               {{ $deposit->status !== 'pending' ? 'readonly' : '' }}>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="deposit_method" class="form-label">Deposit Method <span class="text-danger">*</span></label>
                                        <select class="form-select @error('deposit_method') is-invalid @enderror" 
                                                id="deposit_method" name="deposit_method" required
                                                {{ $deposit->status !== 'pending' ? 'disabled' : '' }}>
                                            <option value="">Select Method</option>
                                            <option value="cash" {{ old('deposit_method', $deposit->deposit_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="check" {{ old('deposit_method', $deposit->deposit_method) == 'check' ? 'selected' : '' }}>Check</option>
                                            <option value="transfer" {{ old('deposit_method', $deposit->deposit_method) == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="online" {{ old('deposit_method', $deposit->deposit_method) == 'online' ? 'selected' : '' }}>Online Payment</option>
                                        </select>
                                        @error('deposit_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="reference" class="form-label">Reference Number</label>
                                        <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                               id="reference" name="reference" value="{{ old('reference', $deposit->reference) }}" 
                                               placeholder="Check number, transaction ID, etc."
                                               {{ $deposit->status !== 'pending' ? 'readonly' : '' }}>
                                        @error('reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" name="notes" rows="3" 
                                                  placeholder="Additional notes about this deposit..."
                                                  {{ $deposit->status !== 'pending' ? 'readonly' : '' }}>{{ old('notes', $deposit->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Current Information Display -->
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Current Information:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Deposit ID:</strong> #{{ $deposit->id }}</li>
                                        <li><strong>Status:</strong> {{ ucfirst($deposit->status) }}</li>
                                        <li><strong>Created:</strong> {{ $deposit->created_at->format('M d, Y \a\t g:i A') }}</li>
                                        <li><strong>Deposited By:</strong> {{ $deposit->depositedBy->name }}</li>
                                    </ul>
                                </div>

                                @if($deposit->status === 'pending')
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            Update Deposit
                                        </button>
                                    </div>
                                @else
                                    <div class="alert alert-warning" role="alert">
                                        <i class="fas fa-lock me-2"></i>
                                        This deposit cannot be edited because it has already been {{ $deposit->status }}.
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Deposit Information Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4" id="accountInfo">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-university me-1"></i>
                                Account Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Customer Name</label>
                                <div class="h6 text-primary">{{ $deposit->account->user->name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Account Number</label>
                                <div class="h6 text-info">{{ $deposit->account->account_number }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Balance</label>
                                <div class="h6 text-success">TSh {{ number_format($deposit->account->balance, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Balance After Deposit</label>
                                <div class="h5 text-info" id="newBalance">
                                    @if($deposit->status === 'completed')
                                        TSh {{ number_format($deposit->account->balance, 2) }}
                                    @else
                                        TSh {{ number_format($deposit->account->balance + $deposit->amount, 2) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit Status -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Deposit Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Status</label>
                                <div class="h6">
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
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Deposit Amount</label>
                                <div class="h5 text-success">TSh {{ number_format($deposit->amount, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Deposit Method</label>
                                <div class="h6 text-info">{{ ucfirst($deposit->deposit_method) }}</div>
                            </div>
                            @if($deposit->reference)
                            <div class="mb-0">
                                <label class="form-label text-muted">Reference</label>
                                <div class="h6 text-warning">{{ $deposit->reference }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('deposits.show', $deposit) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    View Deposit Details
                                </a>
                                <a href="{{ route('customers.show', $deposit->account->user) }}" class="btn btn-outline-info">
                                    <i class="fas fa-user me-1"></i>
                                    View Customer
                                </a>
                                <a href="{{ route('accounts.show', $deposit->account) }}" class="btn btn-outline-success">
                                    <i class="fas fa-university me-1"></i>
                                    View Account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update new balance when amount changes (only if deposit is pending)
    @if($deposit->status === 'pending')
    $('#amount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const currentBalance = {{ $deposit->account->balance }};
        const newBalance = currentBalance + amount;
        $('#newBalance').text('TSh ' + newBalance.toLocaleString());
    });
    @endif
});
</script>
@endpush
@endsection
