@extends('layouts.app')

@section('title', 'Loan Details - ' . $loan->loan_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-hand-holding-usd text-primary me-2"></i>
                    Loan Details - {{ $loan->loan_number }}
                </h1>
                <div>
                    <a href="{{ route('loans.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Loans
                    </a>
                    @if($loan->status === 'pending')
                        <div class="btn-group">
                            <form action="{{ route('loans.approve', $loan) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" 
                                        onclick="return confirm('Are you sure you want to approve this loan?')">
                                    <i class="fas fa-check me-1"></i>
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('loans.reject', $loan) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to reject this loan?')">
                                    <i class="fas fa-times me-1"></i>
                                    Reject
                                </button>
                            </form>
                        </div>
                    @endif
                    @if($loan->status === 'approved')
                        <form action="{{ route('loans.disburse', $loan) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info"
                                    onclick="return confirm('Are you sure you want to disburse this loan?')">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                Disburse Loan
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Loan Information -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Loan Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Loan Number:</td>
                                            <td>{{ $loan->loan_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Loan Type:</td>
                                            <td>{{ $loan->loanType->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Principal Amount:</td>
                                            <td class="text-success fw-bold">TSh {{ number_format($loan->principal_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Interest Rate:</td>
                                            <td>{{ $loan->interest_rate }}% per annum</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Term:</td>
                                            <td>{{ $loan->term_months }} months</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Monthly Payment:</td>
                                            <td class="text-info fw-bold">TSh {{ number_format($loan->monthly_payment, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Outstanding Balance:</td>
                                            <td class="text-warning fw-bold">TSh {{ number_format($loan->outstanding_balance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'active' => 'success',
                                                        'completed' => 'secondary',
                                                        'defaulted' => 'danger',
                                                        'rejected' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusClasses[$loan->status] ?? 'secondary' }} fs-6">
                                                    {{ ucfirst($loan->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Branch:</td>
                                            <td>{{ $loan->branch->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Issued Date:</td>
                                            <td>{{ $loan->issued_date ? $loan->issued_date->format('M d, Y') : 'Not issued' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Due Date:</td>
                                            <td>{{ $loan->due_date ? $loan->due_date->format('M d, Y') : 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Auto Transferred:</td>
                                            <td>
                                                @if($loan->auto_transferred)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="avatar avatar-xl">
                                        <div class="avatar-initial bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="fs-3 text-white">{{ substr($loan->account->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Name:</td>
                                            <td>{{ $loan->account->user->name }}</td>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $loan->account->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>{{ $loan->account->user->phone }}</td>
                                            <td class="fw-bold">Account Number:</td>
                                            <td>{{ $loan->account->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Account Type:</td>
                                            <td>{{ $loan->account->accountType->name }}</td>
                                            <td class="fw-bold">Account Balance:</td>
                                            <td class="text-success fw-bold">TSh {{ number_format($loan->account->balance, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Loan Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Loan Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Loan Amount</label>
                                <div class="h5 text-primary">TSh {{ number_format($loan->principal_amount, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Interest</label>
                                <div class="h6 text-info">
                                    TSh {{ number_format(($loan->monthly_payment * $loan->term_months) - $loan->principal_amount, 2) }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Payable</label>
                                <div class="h6 text-warning">
                                    TSh {{ number_format($loan->monthly_payment * $loan->term_months, 2) }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Progress</label>
                                @php
                                    $paidAmount = $loan->principal_amount - $loan->outstanding_balance;
                                    $progressPercentage = $loan->principal_amount > 0 ? ($paidAmount / $loan->principal_amount) * 100 : 0;
                                @endphp
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $progressPercentage }}%" 
                                         aria-valuenow="{{ $progressPercentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($progressPercentage, 1) }}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Paid: TSh {{ number_format($paidAmount, 2) }} / 
                                    TSh {{ number_format($loan->principal_amount, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('customers.show', $loan->account->user) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-user me-1"></i>
                                    View Customer
                                </a>
                                <a href="{{ route('accounts.show', $loan->account) }}" class="btn btn-outline-info">
                                    <i class="fas fa-university me-1"></i>
                                    View Account
                                </a>
                                @if($loan->status === 'active')
                                    <button class="btn btn-outline-success" onclick="alert('Payment feature coming soon!')">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Record Payment
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Loan Type Details -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Loan Type Details</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{ $loan->loanType->name }}</strong></p>
                            <p class="text-muted">{{ $loan->loanType->description }}</p>
                            <hr>
                            <small class="text-muted">
                                <strong>Base Rate:</strong> {{ $loan->loanType->base_interest_rate }}%<br>
                                <strong>Amount Range:</strong> TSh {{ number_format($loan->loanType->min_amount) }} - TSh {{ number_format($loan->loanType->max_amount) }}<br>
                                <strong>Term Range:</strong> {{ $loan->loanType->min_term_months }} - {{ $loan->loanType->max_term_months }} months
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
