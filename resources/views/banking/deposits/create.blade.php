@extends('layouts.app')

@section('title', 'New Deposit')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-piggy-bank text-primary me-2"></i>
                    New Deposit
                </h1>
                <a href="{{ route('deposits.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Deposits
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Deposit Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('deposits.store') }}" method="POST">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="account_id" class="form-label">Customer Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('account_id') is-invalid @enderror" 
                                                id="account_id" name="account_id" required>
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" 
                                                        data-customer="{{ $account->user->name }}"
                                                        data-balance="{{ $account->balance }}"
                                                        {{ old('account_id') == $account->id ? 'selected' : '' }}>
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
                                               id="amount" name="amount" value="{{ old('amount') }}" 
                                               min="1" step="0.01" required>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="deposit_method" class="form-label">Deposit Method <span class="text-danger">*</span></label>
                                        <select class="form-select @error('deposit_method') is-invalid @enderror" 
                                                id="deposit_method" name="deposit_method" required>
                                            <option value="">Select Method</option>
                                            <option value="cash" {{ old('deposit_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="check" {{ old('deposit_method') == 'check' ? 'selected' : '' }}>Check</option>
                                            <option value="transfer" {{ old('deposit_method') == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="online" {{ old('deposit_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                        </select>
                                        @error('deposit_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="reference" class="form-label">Reference Number</label>
                                        <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                               id="reference" name="reference" value="{{ old('reference') }}" 
                                               placeholder="Check number, transaction ID, etc.">
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
                                                  placeholder="Additional notes about this deposit...">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Deposit Information Notice -->
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Important:</strong> This deposit will be created with "Pending" status and will require authorization before being processed.
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Create Deposit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Account Information Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4" id="accountInfo" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-university me-1"></i>
                                Account Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Customer Name</label>
                                <div class="h6 text-primary" id="customerName">-</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Balance</label>
                                <div class="h6 text-success" id="currentBalance">-</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">New Balance (After Deposit)</label>
                                <div class="h5 text-info" id="newBalance">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit Method Information -->
                    <div class="card shadow mb-4" id="methodInfo" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Method Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="methodDetails"></div>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">
                                <i class="fas fa-lightbulb me-1"></i>
                                Quick Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Verify customer identity before processing
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Double-check the deposit amount
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Keep reference numbers for tracking
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    All deposits require authorization
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
    // Update account information when account changes
    $('#account_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const customerName = selectedOption.data('customer');
            const currentBalance = selectedOption.data('balance');
            
            $('#customerName').text(customerName);
            $('#currentBalance').text('TSh ' + parseFloat(currentBalance).toLocaleString());
            $('#accountInfo').show();
            
            // Update new balance when amount changes
            updateNewBalance();
        } else {
            $('#accountInfo').hide();
        }
    });

    // Update new balance when amount changes
    $('#amount').on('input', function() {
        updateNewBalance();
    });

    // Update method information when deposit method changes
    $('#deposit_method').change(function() {
        const method = $(this).val();
        if (method) {
            let methodDetails = '';
            switch(method) {
                case 'cash':
                    methodDetails = `
                        <p><strong>Cash Deposit</strong></p>
                        <p class="text-muted">Physical cash deposit at the branch. Verify bills and count carefully.</p>
                        <p class="text-muted"><strong>Required:</strong> Customer ID verification</p>
                    `;
                    break;
                case 'check':
                    methodDetails = `
                        <p><strong>Check Deposit</strong></p>
                        <p class="text-muted">Bank check or personal check deposit. Verify check details and endorsement.</p>
                        <p class="text-muted"><strong>Required:</strong> Check number in reference field</p>
                    `;
                    break;
                case 'transfer':
                    methodDetails = `
                        <p><strong>Bank Transfer</strong></p>
                        <p class="text-muted">Electronic transfer from another bank account.</p>
                        <p class="text-muted"><strong>Required:</strong> Transfer reference number</p>
                    `;
                    break;
                case 'online':
                    methodDetails = `
                        <p><strong>Online Payment</strong></p>
                        <p class="text-muted">Digital payment through online banking or mobile money.</p>
                        <p class="text-muted"><strong>Required:</strong> Transaction ID</p>
                    `;
                    break;
            }
            $('#methodDetails').html(methodDetails);
            $('#methodInfo').show();
        } else {
            $('#methodInfo').hide();
        }
    });

    function updateNewBalance() {
        const selectedOption = $('#account_id').find('option:selected');
        const amount = parseFloat($('#amount').val()) || 0;
        
        if (selectedOption.val() && amount > 0) {
            const currentBalance = parseFloat(selectedOption.data('balance'));
            const newBalance = currentBalance + amount;
            $('#newBalance').text('TSh ' + newBalance.toLocaleString());
        } else {
            $('#newBalance').text('-');
        }
    }
});
</script>
@endpush
@endsection
