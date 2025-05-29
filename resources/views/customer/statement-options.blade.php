@extends('layouts.customer')

@section('title', 'Bank Statement Options')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-file-alt me-2"></i>Bank Statement Options</h2>
                    <p class="text-muted mb-0">Choose your preferred format for downloading your bank statement</p>
                </div>
                <div>
                    <a href="{{ route('customer.transactions') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Format Selection -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Select Statement Format
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- PDF Option -->
                        <div class="col-md-6 mb-4">
                            <div class="format-option h-100">
                                <div class="card border-2 border-danger h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <div class="format-icon mb-3">
                                            <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                        </div>
                                        <h4 class="card-title text-danger">PDF Format</h4>
                                        <p class="card-text flex-grow-1">
                                            Professional bank statement in PDF format. Perfect for:
                                        </p>
                                        <ul class="list-unstyled text-start mb-4">
                                            <li><i class="fas fa-check text-success me-2"></i>Official documentation</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Loan applications</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Easy printing</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Professional appearance</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Secure format</li>
                                        </ul>
                                        <a href="{{ route('customer.transactions.print-statement', array_merge($filters, ['format' => 'pdf'])) }}" 
                                           class="btn btn-danger btn-lg">
                                            <i class="fas fa-file-pdf me-2"></i>Download PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Excel Option -->
                        <div class="col-md-6 mb-4">
                            <div class="format-option h-100">
                                <div class="card border-2 border-success h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <div class="format-icon mb-3">
                                            <i class="fas fa-file-excel fa-4x text-success"></i>
                                        </div>
                                        <h4 class="card-title text-success">Excel Format</h4>
                                        <p class="card-text flex-grow-1">
                                            Spreadsheet format for data analysis. Perfect for:
                                        </p>
                                        <ul class="list-unstyled text-start mb-4">
                                            <li><i class="fas fa-check text-success me-2"></i>Data analysis</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Financial planning</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Custom calculations</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Sorting & filtering</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Import to other tools</li>
                                        </ul>
                                        <a href="{{ route('customer.transactions.print-statement', array_merge($filters, ['format' => 'excel'])) }}" 
                                           class="btn btn-success btn-lg">
                                            <i class="fas fa-file-excel me-2"></i>Download Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applied Filters Info -->
                    @if(count($filters) > 0)
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-filter me-2"></i>Applied Filters:</h6>
                            <div class="row">
                                @if(isset($filters['account_id']) && $filters['account_id'])
                                    <div class="col-md-6">
                                        <small><strong>Account:</strong> Specific account selected</small>
                                    </div>
                                @endif
                                @if(isset($filters['transaction_type']) && $filters['transaction_type'])
                                    <div class="col-md-6">
                                        <small><strong>Type:</strong> {{ $filters['transaction_type'] }}</small>
                                    </div>
                                @endif
                                @if(isset($filters['date_from']) && $filters['date_from'])
                                    <div class="col-md-6">
                                        <small><strong>From:</strong> {{ $filters['date_from'] }}</small>
                                    </div>
                                @endif
                                @if(isset($filters['date_to']) && $filters['date_to'])
                                    <div class="col-md-6">
                                        <small><strong>To:</strong> {{ $filters['date_to'] }}</small>
                                    </div>
                                @endif
                                @if(isset($filters['search']) && $filters['search'])
                                    <div class="col-md-6">
                                        <small><strong>Search:</strong> "{{ $filters['search'] }}"</small>
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted">Your statement will include only transactions matching these filters.</small>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="text-center mt-4">
                        <div class="btn-group" role="group">
                            <a href="{{ route('customer.transactions') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Transactions
                            </a>
                            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-primary">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Need Help Choosing?</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-danger">Choose PDF if you need:</h6>
                            <ul class="small">
                                <li>Official bank statement for applications</li>
                                <li>Document to print and keep as record</li>
                                <li>Professional-looking statement</li>
                                <li>File that won't be accidentally modified</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Choose Excel if you need:</h6>
                            <ul class="small">
                                <li>To analyze your spending patterns</li>
                                <li>To create charts or graphs</li>
                                <li>To import data into accounting software</li>
                                <li>To perform calculations on your data</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .format-option .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .format-option .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .format-icon {
        transition: transform 0.3s ease;
    }
    
    .format-option:hover .format-icon {
        transform: scale(1.1);
    }
    
    .card-body {
        min-height: 400px;
    }
    
    .btn-lg {
        padding: 12px 30px;
        font-size: 1.1rem;
    }
</style>
@endpush
