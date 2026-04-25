@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
<div class="dashboard-container">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>

            <form action="{{ route('settings.notifications.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Notification Channels</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="channels[]" value="system"
                               id="channel_system" {{ in_array('system', $channels) ? 'checked' : '' }}>
                        <label class="form-check-label" for="channel_system">
                            <i class="fas fa-desktop me-1"></i> In-system notifications
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="channels[]" value="email"
                               id="channel_email" {{ in_array('email', $channels) ? 'checked' : '' }}>
                        <label class="form-check-label" for="channel_email">
                            <i class="fas fa-envelope me-1"></i> Email notifications
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="channels[]" value="sms"
                               id="channel_sms" {{ in_array('sms', $channels) ? 'checked' : '' }}>
                        <label class="form-check-label" for="channel_sms">
                            <i class="fas fa-sms me-1"></i> SMS notifications
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number (for SMS)</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                           value="{{ old('phone', auth()->user()->phone) }}" placeholder="+2547XXXXXXXX">
                    <div class="form-text">Required for SMS notifications</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notification Types</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="types[]" value="airtime_expiry"
                               id="type_airtime" checked>
                        <label class="form-check-label" for="type_airtime">
                            Airtime expiry reminders
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="types[]" value="internet_expiry"
                               id="type_internet" checked>
                        <label class="form-check-label" for="type_internet">
                            Internet expiry reminders
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="types[]" value="bill_expiry"
                               id="type_bills" checked>
                        <label class="form-check-label" for="type_bills">
                            Bill payment reminders
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Days before expiry to notify</label>
                    <select class="form-select" name="days_threshold">
                        <option value="1">1 day before</option>
                        <option value="2">2 days before</option>
                        <option value="3" selected>3 days before</option>
                        <option value="5">5 days before</option>
                        <option value="7">1 week before</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Preferences</button>
            </form>
        </div>
    </div>
</div>
@endsection
