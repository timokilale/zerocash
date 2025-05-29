@extends('layouts.app')

@section('title', 'Branch Details')
@section('page-title', 'Branch: ' . $branch->name)

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>
            Edit Branch
        </a>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Branches
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Branch Information -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>
                        Branch Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Branch Name:</td>
                                    <td>{{ $branch->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Branch Code:</td>
                                    <td><span class="badge bg-primary">{{ $branch->code }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Address:</td>
                                    <td>{{ $branch->address }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Phone:</td>
                                    <td>{{ $branch->phone ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $branch->email ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Created:</td>
                                    <td>{{ $branch->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-primary">{{ $branch->employees_count }}</h4>
                            <small class="text-muted">Employees</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success">{{ $branch->accounts_count }}</h4>
                            <small class="text-muted">Accounts</small>
                        </div>
                        <div class="col-12">
                            <h4 class="text-warning">{{ $branch->loans_count }}</h4>
                            <small class="text-muted">Loans</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manager Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Branch Manager
                    </h5>
                </div>
                <div class="card-body">
                    @if($branch->manager)
                        <div class="text-center">
                            <h6 class="mb-1">{{ $branch->manager->first_name }} {{ $branch->manager->last_name }}</h6>
                            <p class="text-muted mb-2">{{ ucfirst($branch->manager->role) }}</p>
                            <p class="small mb-0">{{ $branch->manager->email }}</p>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <p class="mb-0">No manager assigned</p>
                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                Assign Manager
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Section -->
    @if($branch->employees->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Branch Employees ({{ $branch->employees->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Position</th>
                                    <th>Hire Date</th>
                                    <th>Status</th>
                                    @if($branch->manager)
                                        <th>Manager</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branch->employees as $employee)
                                    <tr class="{{ $employee->user->id === $branch->manager_id ? 'table-primary' : '' }}">
                                        <td>{{ $employee->employee_id }}</td>
                                        <td>
                                            <strong>{{ $employee->user->first_name }} {{ $employee->user->last_name }}</strong>
                                            @if($employee->user->id === $branch->manager_id)
                                                <i class="fas fa-crown text-warning ms-1" title="Branch Manager"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $employee->user->role === 'admin' || $employee->user->role === 'root' ? 'danger' : ($employee->user->role === 'manager' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($employee->user->role) }}
                                            </span>
                                        </td>
                                        <td>{{ $employee->position }}</td>
                                        <td>{{ $employee->hire_date->format('M d, Y') }}</td>
                                        <td>
                                            @if($employee->deleted_at)
                                                <span class="badge bg-secondary">Dormant</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        @if($branch->manager)
                                            <td>
                                                @if($employee->user->id === $branch->manager_id)
                                                    <span class="badge bg-primary">Yes</span>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Accounts Section -->
    @if($branch->accounts->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-piggy-bank me-2"></i>
                        Recent Accounts ({{ $branch->accounts->count() }} total)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Account Number</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branch->accounts->take(10) as $account)
                                    <tr>
                                        <td>{{ $account->account_number }}</td>
                                        <td>{{ $account->user->first_name }} {{ $account->user->last_name }}</td>
                                        <td>{{ $account->accountType->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($account->balance, 2) }} TZS</td>
                                        <td>
                                            <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($account->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($branch->accounts->count() > 10)
                        <div class="text-center mt-3">
                            <small class="text-muted">Showing 10 of {{ $branch->accounts->count() }} accounts</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Branch Section -->
    @if($branch->employees_count == 0 && $branch->accounts_count == 0 && $branch->loans_count == 0)
    <div class="row">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Danger Zone
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">This branch has no associated employees, accounts, or loans and can be safely deleted.</p>
                    <form method="POST" action="{{ route('branches.destroy', $branch->id) }}"
                          onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Delete Branch
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
