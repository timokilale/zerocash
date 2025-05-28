@extends('layouts.app')

@section('title', 'Accounts')
@section('page-title', 'Account Management')

@section('page-actions')
    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>
        Create New Account
    </a>
@endsection

@section('content')
<div class="row">
    <!-- Summary Cards -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $accounts->total() }}</h4>
                        <p class="mb-0">Total Accounts</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-piggy-bank fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $accounts->where('status', 'active')->count() }}</h4>
                        <p class="mb-0">Active Accounts</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">TSh {{ number_format($accounts->sum('balance'), 2) }}</h4>
                        <p class="mb-0">Total Balance</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $accounts->where('status', 'inactive')->count() }}</h4>
                        <p class="mb-0">Inactive Accounts</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-pause-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-piggy-bank me-2"></i>
                    All Accounts
                </h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select class="form-select form-select-sm" id="typeFilter">
                        <option value="">All Types</option>
                        @foreach(\App\Models\AccountType::all() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                @if($accounts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Account Number</th>
                                    <th>Customer</th>
                                    <th>Account Type</th>
                                    <th>Branch</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                    <tr>
                                        <td>
                                            <strong>{{ $account->account_number }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    {{ strtoupper(substr($account->user->first_name, 0, 1)) }}{{ strtoupper(substr($account->user->last_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $account->user->first_name }} {{ $account->user->last_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $account->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $account->accountType->name }}</span>
                                        </td>
                                        <td>{{ $account->branch->name }}</td>
                                        <td>
                                            <strong class="text-success">
                                                TSh {{ number_format($account->balance, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @switch($account->status)
                                                @case('active')
                                                    <span class="badge bg-success">Active</span>
                                                    @break
                                                @case('inactive')
                                                    <span class="badge bg-warning">Inactive</span>
                                                    @break
                                                @case('suspended')
                                                    <span class="badge bg-danger">Suspended</span>
                                                    @break
                                                @case('closed')
                                                    <span class="badge bg-dark">Closed</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($account->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $account->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('accounts.show', $account->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('accounts.edit', $account->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($account->status !== 'closed')
                                                    <form method="POST" action="{{ route('accounts.toggle-status', $account->id) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-{{ $account->status === 'active' ? 'warning' : 'success' }}" 
                                                                title="{{ $account->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas fa-{{ $account->status === 'active' ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('transfer.form') }}?account={{ $account->id }}" 
                                                   class="btn btn-sm btn-outline-info" title="Transfer">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $accounts->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No accounts found</h5>
                        <p class="text-muted">Start by creating your first account.</p>
                        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Create First Account
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

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#statusFilter, #typeFilter').change(function() {
        // This would typically trigger an AJAX request to filter results
        // For now, we'll just reload the page with query parameters
        const status = $('#statusFilter').val();
        const type = $('#typeFilter').val();
        
        let url = new URL(window.location);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }
        
        window.location = url;
    });
});
</script>
@endpush
