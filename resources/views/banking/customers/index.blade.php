@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customer Management')

@section('page-actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>
        Add New Customer
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    All Customers
                </h5>
                <span class="badge bg-primary">{{ $customers->total() }} Total</span>
            </div>
            <div class="card-body">
                @if($customers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>NIDA</th>
                                    <th>Accounts</th>
                                    <th>Total Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>
                                            <strong>{{ $customer->customer_id ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $customer->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>
                                            @if($customer->nida_number)
                                                <span class="badge bg-success">{{ $customer->nida_number }}</span>
                                            @else
                                                <span class="badge bg-warning">Not Verified</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $customer->accounts->count() }} Account(s)</span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                TSh {{ number_format($customer->accounts->sum('balance'), 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($customer->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('customers.show', $customer->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('customers.edit', $customer->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($customer->accounts->count() === 0)
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#createAccountModal{{ $customer->id }}"
                                                            title="Create Account">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Create Account Modal -->
                                    @if($customer->accounts->count() === 0)
                                        <div class="modal fade" id="createAccountModal{{ $customer->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Create Account for {{ $customer->first_name }} {{ $customer->last_name }}</h5>
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
                                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="branch_id" class="form-label">Branch</label>
                                                                <select name="branch_id" class="form-select" required>
                                                                    <option value="">Select Branch</option>
                                                                    @foreach(\App\Models\Branch::all() as $branch)
                                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
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
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $customers->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No customers found</h5>
                        <p class="text-muted">Start by adding your first customer to the system.</p>
                        <a href="{{ route('customers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Add First Customer
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
</style>
@endpush
