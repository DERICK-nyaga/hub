@extends('layouts.app')

@section('title', 'New Airtime Payment')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-phone-alt me-2"></i>New Airtime Payment</h2>
                    <p class="text-muted mb-0">Add airtime topup for station mobile numbers</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('payments.airtime.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Station Count</h6>
                                <h4 class="mb-0">{{ $stations->count() }}</h4>
                            </div>
                            <i class="fas fa-building fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Active Topups</h6>
                                <h4 class="mb-0">{{ $activeTopups }}</h4>
                            </div>
                            <i class="fas fa-signal fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Expiring Soon</h6>
                                <h4 class="mb-0">{{ $expiringSoon }}</h4>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">This Month</h6>
                                <h4 class="mb-0">KES {{ number_format($monthlyTotal, 0) }}</h4>
                            </div>
                            <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0" id="airtime-payment"><i class="fas fa-plus-circle me-2"></i> Airtime Payment Details</h5>
                    </div>
                    <div class="card-body">
                        @if(session('old_data'))
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Renewing airtime payment. Please review and update the details below.
                            </div>
                        @endif

                        <form action="{{ route('payments.airtime.store') }}" method="POST" id="airtimeForm">
                            @csrf

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label for="station_id" class="form-label">Station *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <select class="form-select @error('station_id') is-invalid @enderror"
                                                id="station_id" name="station_id" required
                                                data-live-search="true">
                                            <option value="">Select Station</option>
                                            @foreach($stations as $station)
                                                <option value="{{ $station->station_id }}"
                                                        {{ old('station_id') == $station->station_id ? 'selected' : '' }}
                                                        data-mobile="{{ $station->contact_phone }}"
                                                        data-location="{{ $station->location }}">
                                                    {{ $station->name }} - {{ $station->location }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="showStationDetails()" title="View Station Info">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </div>
                                    @error('station_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="mobile_number" class="form-label">Mobile Number *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                        <input type="text" class="form-control @error('mobile_number') is-invalid @enderror"
                                            id="mobile_number" name="mobile_number"
                                            value="{{ old('mobile_number') }}"
                                            placeholder="e.g., 0712345678" required
                                            list="mobileSuggestions">
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="document.getElementById('mobile_number').value =
                                                        document.getElementById('station_id').options[
                                                        document.getElementById('station_id').selectedIndex
                                                        ].getAttribute('data-mobile') || ''"
                                                title="Use Station Contact">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>

                                    <datalist id="mobileSuggestions">
                                        @foreach($recentNumbers as $number)
                                            <option value="{{ $number }}">{{ $number }}</option>
                                        @endforeach
                                    </datalist>

                                    <small class="text-muted">
                                        Select a station first, then click the copy button to use its contact number.
                                    </small>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label for="amount" class="form-label">Amount (KES) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                        <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                               id="amount" name="amount" value="{{ old('amount') }}" required>
                                        <span class="input-group-text">.00</span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">Quick select:</small>
                                        @php
                                            $quickAmounts = [100,200,250,500, 1000, 2000, 3000, 5000, 10000];
                                        @endphp
                                        @foreach($quickAmounts as $quickAmount)
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1 mb-1"
                                                    onclick="setAmount({{ $quickAmount }})">
                                                KES {{ number_format($quickAmount) }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="topup_date" class="form-label">Topup Date *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" class="form-control @error('topup_date') is-invalid @enderror"
                                               id="topup_date" name="topup_date"
                                               value="{{ old('topup_date', date('Y-m-d')) }}" required>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="setDate('today')" title="Today">
                                            Today
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="setDate('yesterday')" title="Yesterday">
                                            Yesterday
                                        </button>
                                    </div>
                                    @error('topup_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="network_provider" class="form-label">Network Provider *</label>
                                    <select class="form-select @error('network_provider') is-invalid @enderror"
                                            id="network_provider" name="network_provider" required>
                                        <option value="">Select Provider</option>
                                        <option value="Safaricom" {{ old('network_provider') == 'Safaricom' ? 'selected' : '' }}>
                                            <i class="fas fa-wifi me-2 text-success"></i> Safaricom
                                        </option>
                                        <option value="Airtel" {{ old('network_provider') == 'Airtel' ? 'selected' : '' }}>
                                            <i class="fas fa-signal me-2 text-danger"></i> Airtel
                                        </option>
                                        <option value="Telkom" {{ old('network_provider') == 'Telkom' ? 'selected' : '' }}>
                                            <i class="fas fa-broadcast-tower me-2 text-info"></i> Telkom
                                        </option>
                                        <option value="Faiba" {{ old('network_provider') == 'Faiba' ? 'selected' : '' }}>
                                            <i class="fas fa-tower-cell me-2 text-warning"></i> Faiba
                                        </option>
                                    </select>
                                    <div class="mt-2" id="networkInfo" style="display: none;">
                                        <small class="text-muted" id="networkDescription"></small>
                                    </div>
                                    @error('network_provider')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="transaction_id" class="form-label">Transaction ID</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                        <input type="text" class="form-control @error('transaction_id') is-invalid @enderror"
                                               id="transaction_id" name="transaction_id"
                                               value="{{ old('transaction_id', 'TXN' . date('YmdHis')) }}"
                                               placeholder="e.g., TXN12345678">
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="generateTransactionId()" title="Generate New ID">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Leave blank to auto-generate</small>
                                    @error('transaction_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expected Expiry</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                        <input type="date" class="form-control" id="expected_expiry"
                                               name="expected_expiry" readonly>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="showExpiryOptions()" title="Customize Expiry">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="expiryOption"
                                                   id="expiry30" value="30" checked onclick="setExpiryDays(30)">
                                            <label class="form-check-label" for="expiry30">30 days</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="expiryOption"
                                                   id="expiry60" value="60" onclick="setExpiryDays(60)">
                                            <label class="form-check-label" for="expiry60">60 days</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="expiryOption"
                                                   id="expiry90" value="90" onclick="setExpiryDays(90)">
                                            <label class="form-check-label" for="expiry90">90 days</label>
                                        </div>
                                    </div>
                                    <small class="text-muted" id="expiryText">Auto-calculated: 30 days from topup date</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method">
                                        <option value="">Select Method</option>
                                        <option value="M-Pesa" {{ old('payment_method') == 'M-Pesa' ? 'selected' : '' }}>
                                            M-Pesa
                                        </option>
                                        <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>
                                            Bank Transfer
                                        </option>
                                        <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>
                                            Cash
                                        </option>
                                        <option value="Airtime Money" {{ old('payment_method') == 'Airtime Money' ? 'selected' : '' }}>
                                            Airtime Money
                                        </option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <div class="input-group mb-2">
                                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                                  id="notes" name="notes" rows="3"
                                                  placeholder="Enter any notes about this airtime topup...">{{ old('notes') }}</textarea>
                                    </div>
                                    <div>
                                        <small class="text-muted">Quick notes:</small>
                                        @php
                                            $quickNotes = [
                                                'Monthly airtime allocation',
                                                'Emergency topup',
                                                'SMS',
                                                'Additional data bundle',
                                                'Special project allocation',
                                                'Meeting/conference credit'
                                            ];
                                        @endphp
                                        @foreach($quickNotes as $note)
                                            <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1"
                                                    onclick="addNote('{{ $note }}')">
                                                {{ $note }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="accordion mb-4" id="advancedOptions">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#advancedCollapse">
                                            <i class="fas fa-cogs me-2"></i> Advanced Options
                                        </button>
                                    </h2>
                                    <div id="advancedCollapse" class="accordion-collapse collapse"
                                         data-bs-parent="#advancedOptions">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="auto_renew" id="auto_renew" value="1"
                                                               {{ old('auto_renew') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="auto_renew">
                                                            <i class="fas fa-redo me-1"></i> Set up auto-renewal
                                                        </label>
                                                        <small class="text-muted d-block">
                                                            Automatically renew this airtime every month
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="send_notification" id="send_notification" value="1"
                                                               {{ old('send_notification', true) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="send_notification">
                                                            <i class="fas fa-bell me-1"></i> Send notification
                                                        </label>
                                                        <small class="text-muted d-block">
                                                            Notify station contact about this topup
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0" id="airtime-payment"><i class="fas fa-file-invoice me-2"></i> Payment Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="summaryPreview">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Station:</strong> <span id="summaryStation">-</span></p>
                                            <p class="mb-1"><strong>Mobile:</strong> <span id="summaryMobile">-</span></p>
                                            <p class="mb-1"><strong>Network:</strong> <span id="summaryNetwork">-</span></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Amount:</strong> <span id="summaryAmount" class="text-success">KES 0.00</span></p>
                                            <p class="mb-1"><strong>Topup Date:</strong> <span id="summaryDate">-</span></p>
                                            <p class="mb-1"><strong>Expiry:</strong> <span id="summaryExpiry">-</span></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Transaction ID:</strong> <span id="summaryTxn">-</span></p>
                                            <p class="mb-1"><strong>Payment Method:</strong> <span id="summaryMethod">-</span></p>
                                            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="clearForm()">
                                                <i class="fas fa-broom me-1"></i> Clear Form
                                            </button>
                                            <button type="button" class="btn btn-outline-info"
                                                    onclick="saveAsDraft()">
                                                <i class="fas fa-save me-1"></i> Save as Draft
                                            </button>
                                        </div>
                                        <div>
                                            <a href="{{ route('payments.airtime.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Save Airtime Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0" id="airtime-payment"><i class="fas fa-history me-2"></i> Recent Airtime Topups</h6>
                    </div>
                    <div class="card-body">
                        @if($recentPayments->count() > 0)
                            <div class="list-group">
                                @foreach($recentPayments as $recent)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $recent->station->name }}</h6>
                                        <small>{{ \Carbon\Carbon::parse($recent->topup_date)->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>{{ $recent->mobile_number }}</strong> -
                                        KES {{ number_format($recent->amount, 2) }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $recent->network_provider == 'Safaricom' ? 'wifi' : 'signal' }} me-1"></i>
                                        {{ $recent->network_provider }} •
                                        Expires: {{ \Carbon\Carbon::parse($recent->expected_expiry)->format('M d') }}
                                    </small>
                                </div>
                                @endforeach
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('payments.airtime.index') }}" class="btn btn-sm btn-outline-primary">
                                    View All <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-phone-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent airtime payments</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0" id="airtime-payment"><i class="fas fa-address-book me-2"></i> Station Contacts</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($stations->take(5) as $station)
                            <div class="list-group-item">
                                <h6 class="mb-1">{{ $station->name }}</h6>
                                <p class="mb-1 text-muted">{{ $station->location }}</p>
                                <small>
                                    <i class="fas fa-user me-1"></i> {{ $station->contact_person }}<br>
                                    <i class="fas fa-phone me-1"></i> {{ $station->contact_phone }}<br>
                                    <i class="fas fa-envelope me-1"></i> {{ $station->contact_email }}
                                </small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Station Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="stationModalBody">

                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .stat-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        #summaryPreview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0d6efd;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset(path: 'js/airtime.js') }}"></script>

    <script>
    $(document).ready(function() {
        @if(session('old_data'))
        const oldData = @json(session('old_data'));
        if (oldData.station_id) {
            $('#station_id').val(oldData.station_id).trigger('change');
        }
        if (oldData.mobile_number) {
            $('#mobile_number').val(oldData.mobile_number).trigger('input');
        }
        if (oldData.network_provider) {
            $('#network_provider').val(oldData.network_provider).trigger('change');
        }
        if (oldData.notes) {
            $('#notes').val(oldData.notes);
        }
        @endif
    });
</script>
    @endpush
@vite('resources/js/airtime.js')

@endsection
