@extends('layouts.app')

@section('title', 'Employee Details - ' . $employee->user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user text-primary me-2"></i>
                    Employee Details - {{ $employee->user->name }}
                </h1>
                <div>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Employees
                    </a>
                    @if(!$employee->deleted_at)
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i>
                            Edit Employee
                        </a>
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to mark this employee as dormant?')">
                                <i class="fas fa-user-times me-1"></i>
                                Mark as Dormant
                            </button>
                        </form>
                    @else
                        <form action="{{ route('employees.restore', $employee->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Are you sure you want to restore this employee?')">
                                <i class="fas fa-user-check me-1"></i>
                                Restore Employee
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Employee Information -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="avatar avatar-xl mb-3">
                                        <div class="avatar-initial bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="fs-1 text-white">{{ substr($employee->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        @if($employee->deleted_at)
                                            <span class="badge bg-warning fs-6">Dormant</span>
                                        @else
                                            <span class="badge bg-success fs-6">Active</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Employee ID:</td>
                                            <td>{{ $employee->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Full Name:</td>
                                            <td>{{ $employee->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $employee->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>{{ $employee->user->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Address:</td>
                                            <td>{{ $employee->user->address }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Account Created:</td>
                                            <td>{{ $employee->user->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Employment Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Position:</td>
                                            <td>
                                                <span class="badge bg-info fs-6">{{ $employee->position }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Branch:</td>
                                            <td>{{ $employee->branch->name }} ({{ $employee->branch->code }})</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Monthly Salary:</td>
                                            <td class="text-success fw-bold">TSh {{ number_format($employee->salary, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Hire Date:</td>
                                            <td>{{ $employee->hire_date->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Years of Service:</td>
                                            <td>{{ $employee->hire_date->diffInYears(now()) }} years</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Annual Salary:</td>
                                            <td class="text-info fw-bold">TSh {{ number_format($employee->salary * 12, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($employee->deleted_at)
                    <div class="card shadow mb-4 border-warning">
                        <div class="card-header py-3 bg-warning">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Dormant Status Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                This employee was marked as dormant on {{ $employee->deleted_at->format('M d, Y \a\t g:i A') }}.
                                Dormant employees are soft-deleted and can be restored at any time.
                            </p>
                            <p class="text-muted mb-0">
                                <strong>Note:</strong> Dormant employees cannot log in to the system but their records are preserved for historical purposes.
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Employee Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Employee Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Employee Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Employee ID</label>
                                <div class="h5 text-primary">{{ $employee->employee_id }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Position</label>
                                <div class="h6 text-info">{{ $employee->position }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Monthly Salary</label>
                                <div class="h6 text-success">TSh {{ number_format($employee->salary, 2) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Service Duration</label>
                                <div class="h6 text-warning">
                                    {{ $employee->hire_date->diffForHumans(null, true) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">Branch Information</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">{{ $employee->branch->name }}</h6>
                            <p class="text-muted mb-2">{{ $employee->branch->address }}</p>
                            @if($employee->branch->phone)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $employee->branch->phone }}
                                </p>
                            @endif
                            @if($employee->branch->email)
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $employee->branch->email }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if(!$employee->deleted_at)
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit me-1"></i>
                                        Edit Employee
                                    </a>
                                    <button class="btn btn-outline-info" onclick="alert('Payroll feature coming soon!')">
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        View Payroll
                                    </button>
                                    <button class="btn btn-outline-success" onclick="alert('Performance feature coming soon!')">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Performance Review
                                    </button>
                                @else
                                    <form action="{{ route('employees.restore', $employee->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success w-100"
                                                onclick="return confirm('Are you sure you want to restore this employee?')">
                                            <i class="fas fa-user-check me-1"></i>
                                            Restore Employee
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Employment Timeline -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">Employment Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Hired</h6>
                                        <p class="timeline-text">{{ $employee->hire_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                @if($employee->deleted_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Marked Dormant</h6>
                                        <p class="timeline-text">{{ $employee->deleted_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-text {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endpush
@endsection
