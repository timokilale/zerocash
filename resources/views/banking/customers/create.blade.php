@extends('layouts.app')

@section('title', 'Add New Customer')
@section('page-title', 'Add New Customer')

@section('page-actions')
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back to Customers
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Customer Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.store') }}" id="customerForm">
                    @csrf
                    
                    <!-- NIDA Integration Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>NIDA Integration:</strong> Enter a valid NIDA number to auto-populate customer details.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="nida_number" class="form-label">NIDA Number</label>
                            <input type="text" name="nida_number" id="nida_number" 
                                   class="form-control @error('nida_number') is-invalid @enderror" 
                                   value="{{ old('nida_number') }}" 
                                   placeholder="e.g., 19900107-23106-00002-99">
                            @error('nida_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" id="verifyNida" class="btn btn-info d-block w-100">
                                <i class="fas fa-search me-1"></i>
                                Verify NIDA
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Personal Information -->
                    <h6 class="mb-3">
                        <i class="fas fa-user me-2"></i>
                        Personal Information
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="first_name" 
                                   class="form-control @error('first_name') is-invalid @enderror" 
                                   value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" 
                                   class="form-control @error('last_name') is-invalid @enderror" 
                                   value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="text" name="phone" id="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}" placeholder="+255..." required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" 
                                   class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" rows="3" 
                                  class="form-control @error('address') is-invalid @enderror" 
                                  placeholder="Enter full address">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Account Creation -->
                    <h6 class="mb-3">
                        <i class="fas fa-piggy-bank me-2"></i>
                        Initial Account (Optional)
                    </h6>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="create_account" name="create_account" 
                               {{ old('create_account') ? 'checked' : '' }}>
                        <label class="form-check-label" for="create_account">
                            Create an account for this customer
                        </label>
                    </div>

                    <div id="accountFields" style="display: {{ old('create_account') ? 'block' : 'none' }};">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="account_type_id" class="form-label">Account Type</label>
                                <select name="account_type_id" id="account_type_id" class="form-select">
                                    <option value="">Select Account Type</option>
                                    @foreach($accountTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('account_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} - {{ $type->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }} - {{ $branch->location }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="initial_deposit" class="form-label">Initial Deposit</label>
                            <input type="number" name="initial_deposit" id="initial_deposit" 
                                   class="form-control" min="0" step="0.01" 
                                   value="{{ old('initial_deposit', '0') }}" placeholder="0.00">
                            <div class="form-text">Minimum deposit may be required based on account type.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Create Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle account creation fields
    $('#create_account').change(function() {
        if ($(this).is(':checked')) {
            $('#accountFields').slideDown();
        } else {
            $('#accountFields').slideUp();
        }
    });

    // NIDA Verification
    $('#verifyNida').click(function() {
        const nidaNumber = $('#nida_number').val();
        
        if (!nidaNumber) {
            alert('Please enter a NIDA number first.');
            return;
        }

        const button = $(this);
        const originalText = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin me-1"></i> Verifying...');
        button.prop('disabled', true);

        $.post('/api/test-nida', {
            nida_number: nidaNumber
        })
        .done(function(response) {
            if (response.success && response.customer) {
                // Populate form with NIDA data
                $('#first_name').val(response.customer.first_name || '');
                $('#last_name').val(response.customer.last_name || '');
                $('#date_of_birth').val(response.customer.date_of_birth || '');
                $('#gender').val(response.customer.gender || '');
                
                alert('NIDA verification successful! Customer details have been populated.');
            } else {
                alert('NIDA verification failed: ' + (response.message || 'Unknown error'));
            }
        })
        .fail(function() {
            alert('NIDA verification service is currently unavailable.');
        })
        .always(function() {
            button.html(originalText);
            button.prop('disabled', false);
        });
    });
});
</script>
@endpush
