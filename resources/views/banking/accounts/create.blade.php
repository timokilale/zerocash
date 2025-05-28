@extends('layouts.app')

@section('title', 'Create New Account')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-university text-primary me-2"></i>
                    Create New Account
                </h1>
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Accounts
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('accounts.store') }}" method="POST">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="user_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                                id="user_id" name="user_id" required>
                                            <option value="">Select Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ old('user_id') == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} - {{ $customer->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="account_type_id" class="form-label">Account Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('account_type_id') is-invalid @enderror" 
                                                id="account_type_id" name="account_type_id" required>
                                            <option value="">Select Account Type</option>
                                            @foreach($accountTypes as $accountType)
                                                <option value="{{ $accountType->id }}" {{ old('account_type_id') == $accountType->id ? 'selected' : '' }}>
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
                                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }} ({{ $branch->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="initial_deposit" class="form-label">Initial Deposit (TSh)</label>
                                        <input type="number" class="form-control @error('initial_deposit') is-invalid @enderror" 
                                               id="initial_deposit" name="initial_deposit" value="{{ old('initial_deposit', 0) }}" 
                                               min="0" step="0.01">
                                        @error('initial_deposit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Optional: Initial amount to deposit into the account</div>
                                    </div>
                                </div>

                                <!-- Account Information Notice -->
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> 
                                    <ul class="mb-0 mt-2">
                                        <li>Account number will be automatically generated</li>
                                        <li>Account will be created with "Active" status</li>
                                        <li>Opening date will be set to today</li>
                                        <li>If initial deposit is provided, a deposit transaction will be created</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Create Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Account Type Information Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4" id="accountTypeInfo" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-info-circle me-1"></i>
                                Account Type Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="accountTypeDetails"></div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card shadow mb-4" id="customerInfo" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-user me-1"></i>
                                Customer Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="customerDetails"></div>
                        </div>
                    </div>

                    <!-- Account Creation Tips -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">
                                <i class="fas fa-lightbulb me-1"></i>
                                Account Creation Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Verify customer identity documents
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Choose appropriate account type
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Consider minimum balance requirements
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Explain account features to customer
                                </li>
                            </ul>
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
    // Update account type information when account type changes
    $('#account_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const accountTypeName = selectedOption.text().split(' - ')[0];
            const accountTypeDescription = selectedOption.text().split(' - ')[1];
            
            $('#accountTypeDetails').html(`
                <h6 class="text-primary">${accountTypeName}</h6>
                <p class="text-muted">${accountTypeDescription}</p>
                <hr>
                <small class="text-muted">
                    <strong>Features:</strong><br>
                    • Automatic account number generation<br>
                    • Real-time balance tracking<br>
                    • Transaction history<br>
                    • Mobile banking access
                </small>
            `);
            $('#accountTypeInfo').show();
        } else {
            $('#accountTypeInfo').hide();
        }
    });

    // Update customer information when customer changes
    $('#user_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const customerInfo = selectedOption.text().split(' - ');
            const customerName = customerInfo[0];
            const customerEmail = customerInfo[1];
            
            $('#customerDetails').html(`
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-lg me-3">
                        <div class="avatar-initial bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <span class="text-white">${customerName.charAt(0)}</span>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-0">${customerName}</h6>
                        <small class="text-muted">${customerEmail}</small>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    This customer will be the account holder for the new account.
                </p>
            `);
            $('#customerInfo').show();
        } else {
            $('#customerInfo').hide();
        }
    });
});
</script>
@endpush
@endsection
