{{-- @extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Add New Station</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('stations.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name">Station Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location" value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="monthly_loss">Monthly Profit/Loss (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('monthly_loss') is-invalid @enderror"
                                   id="monthly_loss" name="monthly_loss" value="{{ old('monthly_loss', 0) }}" required>
                            @error('monthly_loss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deductions">Deductions (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('deductions') is-invalid @enderror"
                                   id="deductions" name="deductions" value="{{ old('deductions', 0) }}">
                            @error('deductions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Station
                            </button>
                            <a href="{{ route('stations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection --}}



@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0" id="new-station">Add New Station</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('stations.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name">Station Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location" value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mobile_number">Mobile Number</label>
                            <input type="tel" class="form-control @error('mobile_number') is-invalid @enderror"
                                   id="mobile_number" name="mobile_number"
                                   value="{{ old('mobile_number') }}"
                                   placeholder="e.g., 0712345678">
                            @error('mobile_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Optional. Include country code if needed.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="monthly_loss">Monthly Profit/Loss (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('monthly_loss') is-invalid @enderror"
                                   id="monthly_loss" name="monthly_loss" value="{{ old('monthly_loss', 0) }}" required>
                            @error('monthly_loss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deductions">Deductions (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('deductions') is-invalid @enderror"
                                   id="deductions" name="deductions" value="{{ old('deductions', 0) }}">
                            @error('deductions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Station
                            </button>
                            <a href="{{ route('stations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
