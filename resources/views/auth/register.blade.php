@extends('layouts.auth')

@section('content')
<div class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-grey text-center py-4">
                    <h3 class="mb-0">{{ __('Create Your Account') }}</h3>
                    <p class="mb-0">{{ __('Join Flexcom Today') }}</p>
                </div>

                <div class="card-body p-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4">
                            <div class="form-floating">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                                       placeholder="{{ __('Name') }}">
                                <label for="name" class="text-muted">{{ __('Full Name') }}</label>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required autocomplete="email"
                                       placeholder="{{ __('Email Address') }}">
                                <label for="email" class="text-muted">{{ __('Email Address') }}</label>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

            <div class="mb-4">
                <label for="status" class="form-label">Role</label>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                    <option value="admin" @selected(old('role', $report->admin ?? '') === 'admin')>Admin</option>
                    <option value="manager" @selected(old('role', $report->manager ?? '') === 'manager')>Manager</option>
                    <option value="staff" @selected(old('role', $report->staff?? '') === 'staff')>Staff</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="new-password"
                                       placeholder="{{ __('Password') }}">
                                <label for="password" class="text-muted">{{ __('Password') }}</label>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <input id="password-confirm" type="password" class="form-control"
                                       name="password_confirmation" required autocomplete="new-password"
                                       placeholder="{{ __('Confirm Password') }}">
                                <label for="password-confirm" class="text-muted">{{ __('Confirm Password') }}</label>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 text-uppercase fw-bold">
                                {{ __('Register') }}
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="text-muted mb-0">{{ __('Already have an account?') }}
                                <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-bold">{{ __('Sign in') }}</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
