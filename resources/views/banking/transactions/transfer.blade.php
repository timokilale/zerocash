@extends('layouts.app')

@section('title', 'Transfer Money')
@section('page-title', 'Transfer Money')

@section('page-actions')
    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back to Transactions
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Money Transfer
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('transfer.process') }}" id="transferForm">
                    @csrf

                    <!-- Transfer Type -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Transfer Type</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="transfer_type" id="internal" value="internal" checked>
                                        <label class="form-check-label" for="internal">
                                            <strong>Internal Transfer</strong>
                                            <br>
                                            <small class="text-muted">Between ZeroCash accounts</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="transfer_type" id="external" value="external">
                                        <label class="form-check-label" for="external">
                                            <strong>External Transfer</strong>
                                            <br>
                                            <small class="text-muted">To other banks</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="transfer_type" id="mobile" value="mobile">
                                        <label class="form-check-label" for="mobile">
                                            <strong>Mobile Money</strong>
                                            <br>
                                            <small class="text-muted">To mobile wallets</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- From Account -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="sender_account_id" class="form-label">From Account *</label>
                            <select name="sender_account_id" id="sender_account_id"
                                    class="form-select @error('sender_account_id') is-invalid @enderror" required>
                                <option value="">Select Source Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}"
                                            data-balance="{{ $account->balance }}"
                                            {{ old('sender_account_id') == $account->id || request('account') == $account->id || request('from_account') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_number }} - {{ $account->user->first_name }} {{ $account->user->last_name }}
                                        ({{ $account->accountType->name }}) - Balance: TSh {{ number_format($account->balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sender_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="senderBalance" class="form-text"></div>
                        </div>
                    </div>

                    <!-- To Account (Internal Transfer) -->
                    <div id="internalFields">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="receiver_account_id" class="form-label">To Account *</label>
                                <select name="receiver_account_id" id="receiver_account_id"
                                        class="form-select @error('receiver_account_id') is-invalid @enderror">
                                    <option value="">Select Destination Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('receiver_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_number }} - {{ $account->user->first_name }} {{ $account->user->last_name }}
                                            ({{ $account->accountType->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('receiver_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- External Transfer Fields -->
                    <div id="externalFields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="external_bank" class="form-label">Bank Name *</label>
                                <input type="text" name="external_bank" id="external_bank"
                                       class="form-control" placeholder="e.g., CRDB Bank">
                            </div>
                            <div class="col-md-6">
                                <label for="external_account" class="form-label">Account Number *</label>
                                <input type="text" name="external_account" id="external_account"
                                       class="form-control" placeholder="Enter account number">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="external_name" class="form-label">Account Holder Name *</label>
                                <input type="text" name="external_name" id="external_name"
                                       class="form-control" placeholder="Enter account holder name">
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Money Fields -->
                    <div id="mobileFields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mobile_provider" class="form-label">Mobile Provider *</label>
                                <select name="mobile_provider" id="mobile_provider" class="form-select">
                                    <option value="">Select Provider</option>
                                    <option value="vodacom">Vodacom M-Pesa</option>
                                    <option value="tigo">Tigo Pesa</option>
                                    <option value="airtel">Airtel Money</option>
                                    <option value="halopesa">HaloPesa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mobile_number" class="form-label">Mobile Number *</label>
                                <input type="text" name="mobile_number" id="mobile_number"
                                       class="form-control" placeholder="+255...">
                            </div>
                        </div>
                    </div>

                    <!-- Amount and Description -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount (TSh) *</label>
                            <input type="number" name="amount" id="amount"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   value="{{ old('amount') }}" min="1" step="0.01" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="transaction_type_id" class="form-label">Transaction Type</label>
                            <select name="transaction_type_id" id="transaction_type_id" class="form-select">
                                @foreach(\App\Models\TransactionType::all() as $type)
                                    <option value="{{ $type->id }}"
                                            data-fee-type="{{ $type->fee_type }}"
                                            data-fee-amount="{{ $type->fee_amount }}"
                                            {{ $type->code === 'TRF' ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Enter transfer description">{{ old('description') }}</textarea>
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

                    <!-- Fee Calculation -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Transfer Summary</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-primary mb-0" id="displayAmount">TSh 0.00</h5>
                                        <small class="text-muted">Transfer Amount</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-warning mb-0" id="displayFee">TSh 0.00</h5>
                                        <small class="text-muted">Transaction Fee</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-success mb-0" id="displayTotal">TSh 0.00</h5>
                                        <small class="text-muted">Total Deduction</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane me-1"></i>
                            Process Transfer
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
    // Transfer type change
    $('input[name="transfer_type"]').change(function() {
        const type = $(this).val();

        $('#internalFields, #externalFields, #mobileFields').hide();

        if (type === 'internal') {
            $('#internalFields').show();
        } else if (type === 'external') {
            $('#externalFields').show();
        } else if (type === 'mobile') {
            $('#mobileFields').show();
        }

        calculateFee();
    });

    // Sender account change
    $('#sender_account_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const balance = selectedOption.data('balance');

        if (balance !== undefined) {
            $('#senderBalance').html(`<strong>Available Balance: TSh ${parseFloat(balance).toLocaleString()}</strong>`);
        } else {
            $('#senderBalance').html('');
        }
    });

    // Amount or transaction type change
    $('#amount, #transaction_type_id').on('input change', function() {
        calculateFee();
    });

    function calculateFee() {
        const amount = parseFloat($('#amount').val()) || 0;
        const typeOption = $('#transaction_type_id option:selected');
        const feeType = typeOption.data('fee-type');
        const feeAmount = parseFloat(typeOption.data('fee-amount')) || 0;

        let fee = 0;
        if (feeType === 'fixed') {
            fee = feeAmount;
        } else if (feeType === 'percentage') {
            fee = (amount * feeAmount) / 100;
        }

        const total = amount + fee;

        $('#displayAmount').text(`TSh ${amount.toLocaleString()}`);
        $('#displayFee').text(`TSh ${fee.toLocaleString()}`);
        $('#displayTotal').text(`TSh ${total.toLocaleString()}`);

        // Check if sender has sufficient balance
        const senderOption = $('#sender_account_id option:selected');
        const balance = parseFloat(senderOption.data('balance')) || 0;

        if (total > balance && amount > 0) {
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-exclamation-triangle me-1"></i> Insufficient Balance');
        } else {
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Process Transfer');
        }
    }

    // Initialize
    $('#sender_account_id').trigger('change');
    calculateFee();
});
</script>
@endpush
