@extends('layouts.app')

@section('title', 'Branches')
@section('page-title', 'Branch Management')

@section('page-actions')
    <a href="{{ route('branches.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>
        Add New Branch
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $branches->total() }}</h4>
                                    <p class="mb-0">Total Branches</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $branches->sum('employees_count') }}</h4>
                                    <p class="mb-0">Total Employees</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-tie fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $branches->sum('accounts_count') }}</h4>
                                    <p class="mb-0">Total Accounts</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-piggy-bank fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $branches->sum('loans_count') }}</h4>
                                    <p class="mb-0">Total Loans</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-hand-holding-usd fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branches Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>
                        All Branches
                    </h5>
                </div>
                <div class="card-body">
                    @if($branches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Manager</th>
                                        <th>Contact</th>
                                        <th>Statistics</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $branch)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $branch->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $branch->name }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ Str::limit($branch->address, 50) }}</small>
                                            </td>
                                            <td>
                                                @if($branch->manager)
                                                    <div>
                                                        <strong>{{ $branch->manager->first_name }} {{ $branch->manager->last_name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ ucfirst($branch->manager->role) }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No Manager</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($branch->phone)
                                                    <div><i class="fas fa-phone me-1"></i>{{ $branch->phone }}</div>
                                                @endif
                                                @if($branch->email)
                                                    <div><i class="fas fa-envelope me-1"></i>{{ $branch->email }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <small><i class="fas fa-user-tie me-1"></i>{{ $branch->employees_count }} Employees</small>
                                                    <small><i class="fas fa-piggy-bank me-1"></i>{{ $branch->accounts_count }} Accounts</small>
                                                    <small><i class="fas fa-hand-holding-usd me-1"></i>{{ $branch->loans_count }} Loans</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('branches.show', $branch->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('branches.edit', $branch->id) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($branch->employees_count == 0 && $branch->accounts_count == 0 && $branch->loans_count == 0)
                                                        <form method="POST" action="{{ route('branches.destroy', $branch->id) }}" 
                                                              class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $branches->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No branches found</h5>
                            <p class="text-muted">Start by creating your first branch.</p>
                            <a href="{{ route('branches.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Create First Branch
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
