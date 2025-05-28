@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users text-primary me-2"></i>
                    Employee Management
                </h1>
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Add New Employee
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Employees
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $employees->total() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Active Employees
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $employees->where('deleted_at', null)->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Dormant Employees
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $employees->whereNotNull('deleted_at')->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-times fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Payroll
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        TSh {{ number_format($employees->where('deleted_at', null)->sum('salary'), 2) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Employees</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Branch</th>
                                    <th>Salary</th>
                                    <th>Hire Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                <tr class="{{ $employee->deleted_at ? 'table-secondary' : '' }}">
                                    <td>
                                        <a href="{{ route('employees.show', $employee) }}" class="text-decoration-none">
                                            {{ $employee->employee_id }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-initial bg-primary rounded-circle">
                                                    {{ substr($employee->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $employee->user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $employee->user->email }}</td>
                                    <td>{{ $employee->user->phone }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $employee->position }}</span>
                                    </td>
                                    <td>{{ $employee->branch->name }}</td>
                                    <td>TSh {{ number_format($employee->salary, 2) }}</td>
                                    <td>{{ $employee->hire_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($employee->deleted_at)
                                            <span class="badge bg-warning">Dormant</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(!$employee->deleted_at)
                                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to mark this employee as dormant?')">
                                                        <i class="fas fa-user-times"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('employees.restore', $employee->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                                            onclick="return confirm('Are you sure you want to restore this employee?')">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p>No employees found.</p>
                                            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                                                Add First Employee
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
