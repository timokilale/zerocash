@extends('layouts.app')

@section('title', 'Loan Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs me-2"></i>Loan Settings
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#eligibilityPreviewModal">
                <i class="fas fa-search me-1"></i>Check Customer Eligibility
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('loan-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($settings as $category => $categorySettings)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-{{ $category === 'eligibility' ? 'user-check' : ($category === 'risk' ? 'shield-alt' : ($category === 'limits' ? 'ruler' : 'cog')) }} me-2"></i>
                        {{ ucfirst($category) }} Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($categorySettings as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label fw-bold">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                </label>

                                @if($setting->type === 'boolean')
                                    <!-- Hidden input to ensure unchecked checkboxes send a value -->
                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               id="{{ $setting->key }}"
                                               name="settings[{{ $setting->key }}]"
                                               value="1"
                                               {{ $setting->value ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $setting->key }}">
                                            {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                        </label>
                                    </div>
                                @elseif($setting->type === 'integer')
                                    <div class="input-group">
                                        <input type="number"
                                               class="form-control"
                                               id="{{ $setting->key }}"
                                               name="settings[{{ $setting->key }}]"
                                               value="{{ $setting->value }}"
                                               min="0">
                                        @if(str_contains($setting->key, 'percentage') || str_contains($setting->key, 'multiplier'))
                                            <span class="input-group-text">%</span>
                                        @endif
                                    </div>
                                @elseif($setting->type === 'decimal')
                                    <div class="input-group">
                                        <input type="number"
                                               class="form-control"
                                               id="{{ $setting->key }}"
                                               name="settings[{{ $setting->key }}]"
                                               value="{{ $setting->value }}"
                                               step="0.01"
                                               min="0">
                                        @if(str_contains($setting->key, 'percentage'))
                                            <span class="input-group-text">%</span>
                                        @endif
                                    </div>
                                @else
                                    <input type="text"
                                           class="form-control"
                                           id="{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}]"
                                           value="{{ $setting->value }}">
                                @endif

                                @if($setting->description)
                                    <div class="form-text">{{ $setting->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>

<!-- Eligibility Preview Modal -->
<div class="modal fade" id="eligibilityPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search me-2"></i>Check Customer Loan Eligibility
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="eligibilityForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_select" class="form-label">Customer</label>
                            <select class="form-select" id="customer_select" name="user_id" required>
                                <option value="">Select Customer</option>
                                @foreach(\App\Models\User::where('role', 'customer')->with('accounts')->get() as $customer)
                                    @if($customer->accounts->count() > 0)
                                        <option value="{{ $customer->id }}">
                                            {{ $customer->name }} ({{ $customer->accounts->first()->account_number }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="loan_amount" class="form-label">Loan Amount (TSh)</label>
                            <input type="number" class="form-control" id="loan_amount" name="amount"
                                   min="10000" max="10000000" step="1000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="loan_term" class="form-label">Term (Months)</label>
                            <input type="number" class="form-control" id="loan_term" name="term_months"
                                   min="1" max="60" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Check Eligibility
                    </button>
                </form>

                <div id="eligibilityResult" class="mt-4" style="display: none;">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('eligibilityForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const resultDiv = document.getElementById('eligibilityResult');

    // Show loading
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Checking eligibility...</div>';

    fetch('{{ route("loan-settings.eligibility-preview") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        let html = '';

        if (data.eligible) {
            html = `
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle me-2"></i>Eligible for Loan</h5>
                    <p><strong>Reason:</strong> ${data.reason}</p>
                    <p><strong>Details:</strong> ${data.details}</p>
                    ${data.risk_score ? `<p><strong>Risk Score:</strong> ${data.risk_score}/100</p>` : ''}
                    ${data.recommended_interest_adjustment ? `<p><strong>Interest Adjustment:</strong> ${data.recommended_interest_adjustment > 0 ? '+' : ''}${data.recommended_interest_adjustment}%</p>` : ''}
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle me-2"></i>Not Eligible for Loan</h5>
                    <p><strong>Reason:</strong> ${data.reason}</p>
                    <p><strong>Details:</strong> ${data.details}</p>
                </div>
            `;
        }

        resultDiv.innerHTML = html;
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger">Error checking eligibility. Please try again.</div>';
    });
});

// Update switch labels dynamically
document.querySelectorAll('.form-check-input').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const label = this.nextElementSibling;
        label.textContent = this.checked ? 'Enabled' : 'Disabled';
    });
});
</script>
@endpush
