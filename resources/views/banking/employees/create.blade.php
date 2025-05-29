@extends('layouts.app')

@section('title', 'Add New Employee')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-plus text-primary me-2"></i>
                    Add New Employee
                </h1>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Employees
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Employee Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                @if($prefilledUser ?? false)
                                    <!-- Pre-filled from existing user -->
                                    <input type="hidden" name="existing_user_id" value="{{ $prefilledUser->id }}">

                                    <div class="alert alert-success" role="alert">
                                        <i class="fas fa-user-check me-2"></i>
                                        <strong>Creating employee record for existing user:</strong> {{ $prefilledUser->first_name }} {{ $prefilledUser->last_name }}
                                    </div>

                                    <!-- Display existing user info (read-only) -->
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <h6 class="text-primary border-bottom pb-2">Existing User Information</h6>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" value="{{ $prefilledUser->first_name }} {{ $prefilledUser->last_name }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control" value="{{ $prefilledUser->email }}" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" value="{{ $prefilledUser->phone }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Role</label>
                                            <input type="text" class="form-control" value="{{ ucfirst($prefilledUser->role) }}" readonly>
                                        </div>
                                    </div>
                                @else
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
                                                    <h6 class="card-title">Profile Picture (Optional)</h6>
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3 text-center">
                                                            <div class="avatar-preview mb-3">
                                                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 2rem; font-weight: bold; border: 3px solid #007bff;">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-9">
                                                            <div class="mb-3">
                                                                <label for="avatar" class="form-label">Upload Profile Picture</label>
                                                                <input type="file"
                                                                       class="form-control @error('avatar') is-invalid @enderror"
                                                                       id="avatar"
                                                                       name="avatar"
                                                                       accept="image/*"
                                                                       onchange="previewCreateAvatar(this)">
                                                                @error('avatar')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    Supported formats: JPG, PNG, GIF. Maximum size: 2MB. Recommended: 400x400px square image.
                                                                </div>
                                                            </div>
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
                                                   id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                                   id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                   id="email" name="email" value="{{ old('email') }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                                   id="phone" name="phone" value="{{ old('phone') }}" required>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                               id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                                        @error('hire_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('address') is-invalid @enderror"
                                                  id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
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
                                               id="position" name="position" value="{{ old('position') }}"
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
                                                    {{ (old('branch_id') == $branch->id) || (($prefilledBranch ?? false) && $prefilledBranch->id == $branch->id) ? 'selected' : '' }}>
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
                                               id="salary" name="salary" value="{{ old('salary') }}"
                                               min="0" step="0.01" required>
                                        @error('salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Information Notice -->
                                @if(!($prefilledUser ?? false))
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Note:</strong> A user account will be automatically created for this employee with a randomly generated password.
                                        The employee will receive login credentials via email.
                                    </div>
                                @else
                                    <div class="alert alert-success" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Note:</strong> Creating employee record for existing user. No new user account will be created.
                                    </div>
                                @endif

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Create Employee
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
function previewCreateAvatar(input) {
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
</script>
@endpush

@endsection
