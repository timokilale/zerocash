@extends('layouts.app')

@section('title', 'Transaction Details')
@section('page-title', 'Transaction Details')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Transactions
        </a>
        @if($transaction->status === 'pending')
            <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>
                Edit Transaction
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Transaction Details Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-receipt me-2"></i>
                    Transaction Information
                </h6>
                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }} fs-6">
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">
                            <i class="fas fa-hashtag me-1"></i>
                            Transaction Reference
                        </h6>
                        <div class="d-flex align-items-center">
                            <p class="h5 text-primary mb-0 me-2" id="referenceNumber">{{ $transaction->reference }}</p>
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyReference()" title="Copy Reference">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted">Use this reference for inquiries and tracking</small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Transaction Type</h6>
                        <p><span class="badge bg-info">{{ $transaction->transactionType->name }}</span></p>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Date & Time</h6>
                        <p>{{ $transaction->created_at->format('F d, Y \a\t H:i:s') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Processed By</h6>
                        <p>{{ $transaction->processed_by ? $transaction->processedBy->first_name . ' ' . $transaction->processedBy->last_name : 'System' }}</p>
                    </div>
                </div>

                @if($transaction->description)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $transaction->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Account Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Sender Account -->
                    <div class="col-md-6">
                        <div class="border-end pe-3">
                            <h6 class="text-muted">
                                <i class="fas fa-arrow-up text-danger me-1"></i>
                                From Account
                            </h6>
                            @if($transaction->senderAccount)
                                <div class="mt-2">
                                    <p class="mb-1"><strong>{{ $transaction->senderAccount->account_number }}</strong></p>
                                    <p class="mb-1">{{ $transaction->senderAccount->user->first_name }} {{ $transaction->senderAccount->user->last_name }}</p>
                                    <p class="mb-1 text-muted">{{ $transaction->senderAccount->accountType->name }}</p>
                                    <a href="{{ route('accounts.show', $transaction->senderAccount->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>
                                        View Account
                                    </a>
                                </div>
                            @else
                                <p class="text-muted">External Account</p>
                            @endif
                        </div>
                    </div>

                    <!-- Receiver Account -->
                    <div class="col-md-6">
                        <div class="ps-3">
                            <h6 class="text-muted">
                                <i class="fas fa-arrow-down text-success me-1"></i>
                                To Account
                            </h6>
                            @if($transaction->receiverAccount)
                                <div class="mt-2">
                                    <p class="mb-1"><strong>{{ $transaction->receiverAccount->account_number }}</strong></p>
                                    <p class="mb-1">{{ $transaction->receiverAccount->user->first_name }} {{ $transaction->receiverAccount->user->last_name }}</p>
                                    <p class="mb-1 text-muted">{{ $transaction->receiverAccount->accountType->name }}</p>
                                    <a href="{{ route('accounts.show', $transaction->receiverAccount->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>
                                        View Account
                                    </a>
                                </div>
                            @else
                                <p class="text-muted">External Account</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Amount Summary Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-calculator me-2"></i>
                    Amount Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Transaction Amount:</span>
                    <span class="h5 text-primary">TSh {{ number_format($transaction->amount, 2) }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Transaction Fee:</span>
                    <span class="h6 text-warning">TSh {{ number_format($transaction->fee_amount, 2) }}</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><strong>Total Amount:</strong></span>
                    <span class="h4 text-success">TSh {{ number_format($transaction->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Transaction Status Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Status Information
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted">Current Status:</span>
                    <br>
                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }} fs-6 mt-1">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                @if($transaction->status === 'completed')
                    <div class="mb-3">
                        <span class="text-muted">Completed At:</span>
                        <br>
                        <span>{{ $transaction->updated_at->format('M d, Y H:i:s') }}</span>
                    </div>
                @endif


            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="fas fa-tools me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($transaction->senderAccount)
                        <a href="{{ route('accounts.show', $transaction->senderAccount->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user me-1"></i>
                            View Sender Account
                        </a>
                    @endif

                    @if($transaction->receiverAccount)
                        <a href="{{ route('accounts.show', $transaction->receiverAccount->id) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user me-1"></i>
                            View Receiver Account
                        </a>
                    @endif

                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>
                        All Transactions
                    </a>

                    @if($transaction->status === 'completed')
                        <button class="btn btn-outline-info btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>
                            Print Receipt
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .card-header, .page-actions, .sidebar, .navbar {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function copyReference() {
    const referenceText = document.getElementById('referenceNumber').textContent;

    // Use the modern clipboard API if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(referenceText).then(function() {
            // Show success message
            showCopySuccess();
        }).catch(function(err) {
            // Fallback to older method
            fallbackCopyTextToClipboard(referenceText);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(referenceText);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess();
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }

    document.body.removeChild(textArea);
}

function showCopySuccess() {
    // Change button text temporarily
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check text-success"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');

    setTimeout(function() {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endpush
