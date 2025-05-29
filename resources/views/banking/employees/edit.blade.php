@extends('layouts.app')

@section('title', 'Edit Employee - ' . $employee->user->first_name . ' ' . $employee->user->last_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-edit text-primary me-2"></i>
                    Edit Employee - {{ $employee->user->first_name }} {{ $employee->user->last_name }}
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
                            <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary border-bottom pb-2">Personal Information</h6>
                                    </div>
                                </div>

                                <!-- Avatar Upload Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">Profile Picture</h6>
                                                <div class="row align-items-center">
                                                    <div class="col-md-3 text-center">
                                                        <div class="avatar-preview mb-3">
                                                            @if($employee->user->avatar_url)
                                                                <img src="{{ $employee->user->avatar_url }}"
                                                                     alt="Current Avatar"
                                                                     class="avatar-image rounded-circle"
                                                                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #007bff;">
                                                            @else
                                                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 2rem; font-weight: bold; border: 3px solid #007bff;">
                                                                    {{ $employee->user->avatar_initials }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="mb-3">
                                                            <label for="avatar" class="form-label">Upload New Profile Picture</label>
                                                            <input type="file"
                                                                   class="form-control @error('avatar') is-invalid @enderror"
                                                                   id="avatar"
                                                                   name="avatar"
                                                                   accept="image/*"
                                                                   onchange="previewAvatar(this)">
                                                            @error('avatar')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <div class="form-text">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Supported formats: JPG, PNG, GIF. Maximum size: 2MB. Recommended: 400x400px square image.
                                                            </div>
                                                        </div>
                                                        @if($employee->user->avatar)
                                                            <div class="mb-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="remove_avatar" name="remove_avatar" value="1">
                                                                    <label class="form-check-label text-danger" for="remove_avatar">
                                                                        <i class="fas fa-trash me-1"></i>
                                                                        Remove current profile picture
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name', $employee->user->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                               id="last_name" name="last_name" value="{{ old('last_name', $employee->user->last_name) }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email', $employee->user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone', $employee->user->phone) }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
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
@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const preview = document.querySelector('.avatar-preview');
            preview.innerHTML = `
                <img src="${e.target.result}"
                     alt="Avatar Preview"
                     class="avatar-image rounded-circle"
                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #007bff;">
            `;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Handle remove avatar checkbox
document.getElementById('remove_avatar')?.addEventListener('change', function() {
    if (this.checked) {
        const preview = document.querySelector('.avatar-preview');
        const initials = '{{ $employee->user->avatar_initials }}';
        preview.innerHTML = `
            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                 style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 2rem; font-weight: bold; border: 3px solid #007bff;">
                ${initials}
            </div>
        `;
        // Clear file input
        document.getElementById('avatar').value = '';
    }
});
</script>
@endpush

@endsection
