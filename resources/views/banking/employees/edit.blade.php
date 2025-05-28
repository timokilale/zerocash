@extends('layouts.app')

@section('title', 'Edit Employee - ' . $employee->user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-edit text-primary me-2"></i>
                    Edit Employee - {{ $employee->user->name }}
                </h1>
                <div>
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary me-2">
                        <i class="fas fa-eye me-1"></i>
                        View Employee
                    </a>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Employees
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Edit Employee Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('employees.update', $employee) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary border-bottom pb-2">Personal Information</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $employee->user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $employee->user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $employee->user->phone) }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                               id="hire_date" name="hire_date" value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" required>
                                        @error('hire_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                                  id="address" name="address" rows="3" required>{{ old('address', $employee->user->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Employment Information -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary border-bottom pb-2">Employment Information</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                               id="position" name="position" value="{{ old('position', $employee->position) }}" 
                                               placeholder="e.g., Teller, Manager, Loan Officer" required>
                                        @error('position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select @error('branch_id') is-invalid @enderror" 
                                                id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" 
                                                        {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }} ({{ $branch->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="salary" class="form-label">Monthly Salary (TSh) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('salary') is-invalid @enderror" 
                                               id="salary" name="salary" value="{{ old('salary', $employee->salary) }}" 
                                               min="0" step="0.01" required>
                                        @error('salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Current Information Display -->
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Current Information:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Employee ID:</strong> {{ $employee->employee_id }}</li>
                                        <li><strong>Account Created:</strong> {{ $employee->user->created_at->format('M d, Y') }}</li>
                                        <li><strong>Years of Service:</strong> {{ $employee->hire_date->diffInYears(now()) }} years</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Update Employee
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
