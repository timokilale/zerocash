@extends('layouts.app')

@section('title', 'Edit Account - ' . $account->account_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-university text-primary me-2"></i>
                    Edit Account - {{ $account->account_number }}
                </h1>
                <div>
                    <a href="{{ route('accounts.show', $account) }}" class="btn btn-secondary me-2">
                        <i class="fas fa-eye me-1"></i>
                        View Account
                    </a>
                    <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Accounts
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Edit Account Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('accounts.update', $account) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <!-- Account Information -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary border-bottom pb-2">Account Information</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="account_number" 
                                               value="{{ $account->account_number }}" readonly>
                                        <div class="form-text">Account number cannot be changed</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="account_type_id" class="form-label">Account Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('account_type_id') is-invalid @enderror" 
                                                id="account_type_id" name="account_type_id" required>
                                            <option value="">Select Account Type</option>
                                            @foreach($accountTypes as $accountType)
                                                <option value="{{ $accountType->id }}" 
                                                        {{ old('account_type_id', $account->account_type_id) == $accountType->id ? 'selected' : '' }}>
                                                    {{ $accountType->name }} - {{ $accountType->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('account_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select @error('branch_id') is-invalid @enderror" 
                                                id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" 
                                                        {{ old('branch_id', $account->branch_id) == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }} ({{ $branch->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" 
                                                id="status" name="status" required>
                                            <option value="active" {{ old('status', $account->status) == 'active' ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option value="inactive" {{ old('status', $account->status) == 'inactive' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                            <option value="frozen" {{ old('status', $account->status) == 'frozen' ? 'selected' : '' }}>
                                                Frozen
                                            </option>
                                            <option value="closed" {{ old('status', $account->status) == 'closed' ? 'selected' : '' }}>
                                                Closed
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="balance" class="form-label">Current Balance (TSh)</label>
                                        <input type="text" class="form-control" id="balance" 
                                               value="TSh {{ number_format($account->balance, 2) }}" readonly>
                                        <div class="form-text">Balance cannot be directly modified. Use transactions instead.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="opened_date" class="form-label">Opened Date</label>
                                        <input type="date" class="form-control" id="opened_date" 
                                               value="{{ $account->opened_date->format('Y-m-d') }}" readonly>
                                        <div class="form-text">Opening date cannot be changed</div>
                                    </div>
                                </div>

                                <!-- Customer Information (Read-only) -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary border-bottom pb-2">Account Holder Information</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_name" class="form-label">Customer Name</label>
                                        <input type="text" class="form-control" id="customer_name" 
                                               value="{{ $account->user->name }}" readonly>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="customer_email" class="form-label">Customer Email</label>
                                        <input type="email" class="form-control" id="customer_email" 
                                               value="{{ $account->user->email }}" readonly>
                                    </div>
                                </div>

                                <!-- Status Change Warning -->
                                <div class="alert alert-warning" role="alert" id="statusWarning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> <span id="statusWarningText"></span>
                                </div>

                                <!-- Current Information Display -->
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Current Information:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Account Number:</strong> {{ $account->account_number }}</li>
                                        <li><strong>Current Status:</strong> {{ ucfirst($account->status) }}</li>
                                        <li><strong>Account Age:</strong> {{ $account->opened_date->diffForHumans() }}</li>
                                        <li><strong>Last Updated:</strong> {{ $account->updated_at->format('M d, Y \a\t g:i A') }}</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Update Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Account Information Sidebar -->
                <div class="col-lg-4">
                    <!-- Account Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-info-circle me-1"></i>
                                Account Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Account Number</label>
                                <div class="h6 text-primary">{{ $account->account_number }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Balance</label>
                                <div class="h5 text-success">TSh {{ number_format($account->balance, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Status</label>
                                <div class="h6">
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
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-muted">Account Age</label>
                                <div class="h6 text-info">{{ $account->opened_date->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-shield-alt me-1"></i>
                                Status Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="badge bg-success me-2">Active</span>
                                <small class="text-muted">Account is fully operational</small>
                            </div>
                            <div class="mb-3">
                                <span class="badge bg-warning me-2">Inactive</span>
                                <small class="text-muted">Account is temporarily disabled</small>
                            </div>
                            <div class="mb-3">
                                <span class="badge bg-danger me-2">Frozen</span>
                                <small class="text-muted">Account is frozen due to security concerns</small>
                            </div>
                            <div class="mb-0">
                                <span class="badge bg-secondary me-2">Closed</span>
                                <small class="text-muted">Account is permanently closed</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('accounts.show', $account) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    View Account Details
                                </a>
                                <a href="{{ route('customers.show', $account->user) }}" class="btn btn-outline-info">
                                    <i class="fas fa-user me-1"></i>
                                    View Customer
                                </a>
                                <a href="{{ route('transactions.index', ['account' => $account->id]) }}" class="btn btn-outline-success">
                                    <i class="fas fa-exchange-alt me-1"></i>
                                    View Transactions
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
    // Show warning when status changes
    $('#status').change(function() {
        const selectedStatus = $(this).val();
        const currentStatus = '{{ $account->status }}';
        
        if (selectedStatus !== currentStatus) {
            let warningText = '';
            
            switch(selectedStatus) {
                case 'inactive':
                    warningText = 'Setting account to inactive will prevent the customer from performing transactions.';
                    break;
                case 'frozen':
                    warningText = 'Freezing the account will completely block all account activities. This action should only be taken for security reasons.';
                    break;
                case 'closed':
                    warningText = 'Closing the account is a permanent action. The customer will not be able to use this account anymore.';
                    break;
                case 'active':
                    warningText = 'Activating the account will restore full functionality.';
                    break;
            }
            
            if (warningText) {
                $('#statusWarningText').text(warningText);
                $('#statusWarning').show();
            }
        } else {
            $('#statusWarning').hide();
        }
    });
});
</script>
@endpush
@endsection
