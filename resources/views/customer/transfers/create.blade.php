@extends('layouts.customer')

@section('title', 'Transfer Money')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-exchange-alt text-primary me-2"></i>
                    Transfer Money
                </h1>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Dashboard
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Money
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('customer.transfers.store') }}" id="transferForm">
                                @csrf

                                <!-- From Account -->
                                <div class="mb-3">
                                    <label for="from_account_id" class="form-label">From Your Account</label>
                                    <select class="form-select @error('from_account_id') is-invalid @enderror"
                                            id="from_account_id" name="from_account_id" required>
                                        <option value="">Select your account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                    data-balance="{{ $account->balance }}"
                                                    data-account-number="{{ $account->account_number }}"
                                                    {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_number }} - {{ $account->accountType->name }}
                                                - Balance: TSh {{ number_format($account->balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- To Account Number -->
                                <div class="mb-3">
                                    <label for="to_account_number" class="form-label">To Account Number</label>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control @error('to_account_number') is-invalid @enderror"
                                               id="to_account_number"
                                               name="to_account_number"
                                               value="{{ old('to_account_number') }}"
                                               placeholder="Enter recipient account number"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" id="verifyAccountBtn">
                                            <i class="fas fa-search"></i> Verify
                                        </button>
                                    </div>
                                    @error('to_account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <!-- Account verification result -->
                                    <div id="accountVerificationResult" class="mt-2" style="display: none;">
                                        <div class="alert alert-success border-0 shadow-sm">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong class="text-success">Account Verified Successfully!</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted">Account Number:</small><br>
                                                    <strong id="verifiedAccountNumber" class="text-primary"></strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted">Account Type:</small><br>
                                                    <strong id="accountTypeName" class="text-info"></strong>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <small class="text-muted">Account Holder:</small><br>
                                                    <strong id="accountHolderName" class="text-dark"></strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted">Status:</small><br>
                                                    <span id="accountStatus" class="badge bg-success">Active</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Amount -->
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (TSh)</label>
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           value="{{ old('amount') }}"
                                           min="1"
                                           step="0.01"
                                           placeholder="Enter amount to transfer"
                                           required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              placeholder="Enter transfer description (optional)">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Auto-Generated Reference Info -->
                                <div class="mb-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Reference Number:</strong> A unique reference number will be automatically generated for this transfer for easy tracking and identification.
                                    </div>
                                </div>

                                <!-- Transfer Summary -->
                                <div class="card bg-light mb-3" id="transferSummary" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="card-title">Transfer Summary</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Amount:</strong> <span id="summaryAmount">TSh 0</span></p>
                                                <p class="mb-1"><strong>Transfer Fee:</strong> <span id="summaryFee">TSh 0</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Total Debit:</strong> <span id="summaryTotal">TSh 0</span></p>
                                                <p class="mb-0"><strong>Available Balance:</strong> <span id="summaryBalance">TSh 0</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                        <i class="fas fa-paper-plane me-1"></i>
                                        Process Transfer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #accountVerificationResult {
        animation: slideInDown 0.3s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-outline-success:hover {
        color: #fff;
        background-color: #198754;
        border-color: #198754;
    }

    .toast {
        min-width: 300px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Verify account when button is clicked
    $('#verifyAccountBtn').click(function() {
        const accountNumber = $('#to_account_number').val();

        if (!accountNumber) {
            alert('Please enter an account number first');
            return;
        }

        console.log('Starting account verification for:', accountNumber);

        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
        $(this).prop('disabled', true);

        // Make AJAX request to verify account
        $.ajax({
            url: '{{ route("customer.transfers.account-details") }}',
            method: 'GET',
            data: {
                account_number: accountNumber,
                _token: '{{ csrf_token() }}'
            },
            timeout: 10000,
        })
        .done(function(response) {
            console.log('Account verification response:', response);

            if (response.success && response.account) {
                // Populate verification details
                $('#verifiedAccountNumber').text(response.account.account_number);
                $('#accountHolderName').text(response.account.user.name);
                $('#accountTypeName').text(response.account.account_type.name);
                $('#accountStatus').text(response.account.status.charAt(0).toUpperCase() + response.account.status.slice(1));

                // Update status badge color based on account status
                const statusBadge = $('#accountStatus');
                statusBadge.removeClass('bg-success bg-warning bg-danger');
                if (response.account.status === 'active') {
                    statusBadge.addClass('bg-success');
                } else if (response.account.status === 'inactive') {
                    statusBadge.addClass('bg-warning');
                } else {
                    statusBadge.addClass('bg-danger');
                }

                // Show verification result with animation
                $('#accountVerificationResult').slideDown(300);
                updateTransferSummary();

                // Change verify button to show success
                $('#verifyAccountBtn').html('<i class="fas fa-check text-success"></i> Verified');
                $('#verifyAccountBtn').removeClass('btn-outline-secondary').addClass('btn-outline-success');

                // Show success toast notification
                showSuccessToast('Account verified successfully! You can now proceed with the transfer.');
            } else {
                alert('Account not found or inactive: ' + (response.message || 'Unknown error'));
                $('#accountVerificationResult').slideUp(300);
                resetVerifyButton();
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Account verification failed:', {xhr: xhr, status: status, error: error});

            let errorMessage = 'Error verifying account. Please try again.';
            if (status === 'timeout') {
                errorMessage = 'Request timed out. Please check your connection and try again.';
            } else if (xhr.status === 401) {
                errorMessage = 'Authentication required. Please refresh the page and try again.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
            }

            alert(errorMessage);
            $('#accountVerificationResult').slideUp(300);
            resetVerifyButton();
        })
        .always(function() {
            $('#verifyAccountBtn').prop('disabled', false);
        });
    });

    // Update transfer summary when amount or account changes
    $('#from_account_id, #amount').on('change keyup', function() {
        updateTransferSummary();
    });

    // Reset verification when account number changes
    $('#to_account_number').on('input', function() {
        $('#accountVerificationResult').slideUp(300);
        resetVerifyButton();
    });

    // Verify account when account number field loses focus
    $('#to_account_number').on('blur', function() {
        if ($(this).val() && $(this).val().length >= 10) {
            $('#verifyAccountBtn').click();
        }
    });

    function updateTransferSummary() {
        const fromAccount = $('#from_account_id option:selected');
        const amount = parseFloat($('#amount').val()) || 0;
        const balance = parseFloat(fromAccount.data('balance')) || 0;

        if (amount > 0 && fromAccount.val()) {
            // Calculate fee (simplified - you may want to make an AJAX call for accurate fee calculation)
            const fee = Math.max(500, amount * 0.01); // Minimum 500 TSh or 1% of amount
            const total = amount + fee;

            $('#summaryAmount').text('TSh ' + amount.toLocaleString());
            $('#summaryFee').text('TSh ' + fee.toLocaleString());
            $('#summaryTotal').text('TSh ' + total.toLocaleString());
            $('#summaryBalance').text('TSh ' + balance.toLocaleString());

            $('#transferSummary').show();

            // Enable/disable submit button based on balance
            if (total <= balance && $('#to_account_number').val()) {
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#submitBtn').prop('disabled', true);
            }
        } else {
            $('#transferSummary').hide();
            $('#submitBtn').prop('disabled', true);
        }
    }

    // Helper function to reset verify button
    function resetVerifyButton() {
        $('#verifyAccountBtn').html('<i class="fas fa-search"></i> Verify');
        $('#verifyAccountBtn').removeClass('btn-outline-success').addClass('btn-outline-secondary');
    }

    // Helper function to show success toast
    function showSuccessToast(message) {
        // Remove any existing toasts
        $('.toast-container .toast').remove();

        // Create toast HTML
        const toastHtml = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Add toast container if it doesn't exist
        if (!$('.toast-container').length) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>');
        }

        // Add toast to container
        $('.toast-container').append(toastHtml);

        // Show toast
        const toast = new bootstrap.Toast($('.toast-container .toast').last()[0]);
        toast.show();
    }
});
</script>
@endpush
