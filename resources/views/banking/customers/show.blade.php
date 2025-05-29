@extends('layouts.app')

@section('title', 'Customer Details')
@section('page-title', 'Customer Details')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Customers
        </a>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>
            Edit Customer
        </a>
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'root')
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal">
                <i class="fas fa-trash me-1"></i>
                Delete Customer
            </button>
        @endif
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Customer Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    Customer Information
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 24px;">
                    {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                </div>

                <h4 class="mb-1">{{ $customer->first_name }} {{ $customer->last_name }}</h4>
                <p class="text-muted mb-3">{{ $customer->username }}</p>

                <div class="row text-start">
                    <div class="col-12 mb-2">
                        <strong>Customer ID:</strong>
                        <span class="float-end">{{ $customer->customer_id ?? 'N/A' }}</span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Email:</strong>
                        <span class="float-end">{{ $customer->email }}</span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Phone:</strong>
                        <span class="float-end">{{ $customer->phone }}</span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>NIDA:</strong>
                        <span class="float-end">
                            @if($customer->nida_number)
                                <span class="badge bg-success">{{ $customer->nida_number }}</span>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Status:</strong>
                        <span class="float-end">
                            @if($customer->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Date of Birth:</strong>
                        <span class="float-end">{{ $customer->date_of_birth ? $customer->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Gender:</strong>
                        <span class="float-end">{{ ucfirst($customer->gender ?? 'N/A') }}</span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Joined:</strong>
                        <span class="float-end">{{ $customer->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                @if($customer->address)
                    <hr>
                    <div class="text-start">
                        <strong>Address:</strong>
                        <p class="mt-2 text-muted">{{ $customer->address }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Accounts and Transactions -->
    <div class="col-lg-8">
        <!-- Account Summary -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-piggy-bank me-2"></i>
                    Accounts ({{ $customer->accounts->count() }})
                </h5>
                @if($customer->accounts->count() === 0)
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                        <i class="fas fa-plus me-1"></i>
                        Create Account
                    </button>
                @endif
            </div>
            <div class="card-body">
                @if($customer->accounts->count() > 0)
                    <div class="row">
                        @foreach($customer->accounts as $account)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ $account->accountType->name }}</h6>
                                            <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($account->status) }}
                                            </span>
                                        </div>
                                        <p class="card-text">
                                            <strong>Account #:</strong> {{ $account->account_number }}<br>
                                            <strong>Balance:</strong>
                                            <span class="text-success fw-bold">TSh {{ number_format($account->balance, 2) }}</span><br>
                                            <strong>Branch:</strong> {{ $account->branch->name }}
                                        </p>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('transfer.form') }}?account={{ $account->id }}" class="btn btn-outline-success">
                                                <i class="fas fa-exchange-alt"></i> Transfer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No accounts found</h6>
                        <p class="text-muted">This customer doesn't have any accounts yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Recent Transactions
                </h5>
            </div>
            <div class="card-body">
                @php
                    $recentTransactions = \App\Models\Transaction::whereHas('senderAccount', function($q) use ($customer) {
                        $q->where('user_id', $customer->id);
                    })->orWhereHas('receiverAccount', function($q) use ($customer) {
                        $q->where('user_id', $customer->id);
                    })->with(['senderAccount', 'receiverAccount', 'transactionType'])
                    ->latest()->take(10)->get();
                @endphp

                @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $transaction->transactionType->name }}</span>
                                        </td>
                                        <td>{{ $transaction->description }}</td>
                                        <td>
                                            @if($transaction->senderAccount && $transaction->senderAccount->user_id === $customer->id)
                                                <span class="text-danger">-TSh {{ number_format($transaction->total_amount, 2) }}</span>
                                            @else
                                                <span class="text-success">+TSh {{ number_format($transaction->amount, 2) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('transactions.index') }}?customer={{ $customer->id }}" class="btn btn-outline-primary btn-sm">
                            View All Transactions
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No transactions found</h6>
                        <p class="text-muted">This customer hasn't made any transactions yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Account Modal -->
<div class="modal fade" id="createAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('customers.create-account', $customer->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="account_type_id" class="form-label">Account Type</label>
                        <select name="account_type_id" class="form-select" required>
                            <option value="">Select Account Type</option>
                            @foreach(\App\Models\AccountType::all() as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} - {{ $type->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }} - {{ $branch->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="initial_deposit" class="form-label">Initial Deposit (Optional)</label>
                        <input type="number" name="initial_deposit" class="form-control"
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Customer Modal -->
@if(auth()->user()->role === 'admin' || auth()->user()->role === 'root')
<div class="modal fade" id="deleteCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning!</strong> This action will permanently delete the customer and all associated data.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Information:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Name:</strong> {{ $customer->first_name }} {{ $customer->last_name }}</li>
                            <li><strong>Email:</strong> {{ $customer->email }}</li>
                            <li><strong>Phone:</strong> {{ $customer->phone }}</li>
                            <li><strong>NIDA:</strong> {{ $customer->nida ?? 'Not provided' }}</li>
                            <li><strong>Status:</strong> {{ ucfirst($customer->status) }}</li>
                            <li><strong>Joined:</strong> {{ $customer->created_at->format('M d, Y') }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Associated Data:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Accounts:</strong> {{ $customer->accounts->count() }}</li>
                            @if($customer->accounts->count() > 0)
                                <li><strong>Total Balance:</strong> TSh {{ number_format($customer->accounts->sum('balance'), 2) }}</li>
                            @endif
                            @php
                                $transactionCount = \App\Models\Transaction::whereHas('senderAccount', function($q) use ($customer) {
                                    $q->where('user_id', $customer->id);
                                })->orWhereHas('receiverAccount', function($q) use ($customer) {
                                    $q->where('user_id', $customer->id);
                                })->count();

                                $loanCount = \App\Models\Loan::whereHas('account', function($q) use ($customer) {
                                    $q->where('user_id', $customer->id);
                                })->count();
                            @endphp
                            <li><strong>Transactions:</strong> {{ $transactionCount }}</li>
                            <li><strong>Loans:</strong> {{ $loanCount }}</li>
                            <li><strong>Notifications:</strong> {{ $customer->notifications->count() }}</li>
                        </ul>
                    </div>
                </div>

                @if($customer->accounts->count() > 0)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>This customer has {{ $customer->accounts->count() }} account(s) with a total balance of TSh {{ number_format($customer->accounts->sum('balance'), 2) }}.</strong>
                        <br>All accounts, transactions, loans, and related data will be permanently deleted.
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>What will be deleted:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Customer profile and personal information</li>
                        <li>All associated bank accounts</li>
                        <li>All transaction history</li>
                        <li>All loan records</li>
                        <li>All notifications</li>
                        <li>Any other related data</li>
                    </ul>
                </div>

                <p class="text-muted mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>This action cannot be undone.</strong> Please ensure you have backed up any necessary data before proceeding.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Customer Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .avatar-circle {
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
</style>
@endpush
