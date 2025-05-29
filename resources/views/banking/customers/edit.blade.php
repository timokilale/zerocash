@extends('layouts.app')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Customer
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i>
            All Customers
        </a>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Edit Customer Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Personal Information -->
                    <h6 class="mb-3">
                        <i class="fas fa-user me-2"></i>
                        Personal Information
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $customer->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $customer->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $customer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="text" name="phone" id="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $customer->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" name="username" id="username"
                                   class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $customer->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nida_number" class="form-label">NIDA Number</label>
                            <input type="text" name="nida_number" id="nida_number"
                                   class="form-control @error('nida_number') is-invalid @enderror"
                                   value="{{ old('nida_number', $customer->nida) }}"
                                   placeholder="e.g., 19900107-23106-00002-99">
                            @error('nida_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : '') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $customer->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $customer->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $customer->gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" rows="3"
                                  class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Enter full address">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $customer->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Password Change Section -->
                    <h6 class="mb-3">
                        <i class="fas fa-lock me-2"></i>
                        Change Password (Optional)
                    </h6>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Leave password fields empty if you don't want to change the password.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <div>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'root')
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash me-1"></i>
                                    Delete Customer
                                </button>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Update Customer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if(auth()->user()->role === 'admin' || auth()->user()->role === 'root')
<div class="modal fade" id="deleteModal" tabindex="-1">
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
                            <li><strong>Status:</strong> {{ ucfirst($customer->status) }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Associated Data:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Accounts:</strong> {{ $customer->accounts->count() }}</li>
                            @if($customer->accounts->count() > 0)
                                <li><strong>Total Balance:</strong> TSh {{ number_format($customer->accounts->sum('balance'), 2) }}</li>
                            @endif
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

                <p class="text-muted mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>This action cannot be undone.</strong> Please ensure you have backed up any necessary data.
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
