<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #333; font-size: 14px; text-transform: uppercase; }
        .stat-value { font-size: 28px; font-weight: bold; color: #007bff; }
        .stat-value.danger { color: #dc3545; }
        .stat-value.success { color: #28a745; }
        .stat-value.warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        .section { margin: 30px 0; }
        .section h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-success { background: #28a745; color: white; }
        .error { background: #ffe6e6; color: #dc3545; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Dashboard</h1>

    @if(isset($error))
        <div class="error">
            {{ $error }}
        </div>
    @else
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Stations</h3>
                <div class="stat-value">{{ $stats['total_stations'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <h3>Internet Overdue</h3>
                <div class="stat-value danger">{{ $stats['internet_overdue'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <h3>Airtime Active</h3>
                <div class="stat-value success">{{ $stats['airtime_active'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <h3>Airtime Expiring Soon</h3>
                <div class="stat-value warning">{{ $stats['airtime_expiring_soon'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <h3>Internet Due Soon</h3>
                <div class="stat-value warning">{{ $stats['internet_due_soon'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <h3>Top Station</h3>
                <div class="stat-value">{{ $stats['top_station'] ?? 'N/A' }}</div>
                <small>KSh {{ number_format($stats['top_station_total'] ?? 0, 2) }}</small>
            </div>
            <div class="stat-card">
                <h3>Total Airtime Payments</h3>
                <div class="stat-value">KSh {{ number_format($stats['total_airtime_payments'] ?? 0, 2) }}</div>
            </div>
            <div class="stat-card">
                <h3>Total Internet Payments</h3>
                <div class="stat-value">KSh {{ number_format($stats['total_internet_payments'] ?? 0, 2) }}</div>
            </div>
        </div>

        <!-- Stations Table -->
        <div class="section">
            <h2>Stations ({{ $stations->total() ?? 0 }})</h2>
            @if($stations ?? $stations->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Monthly Loss</th>
                        <th>Airtime</th>
                        <th>Internet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stations as $station)
                    <tr>
                        <td>{{ $station->name }}</td>
                        <td>{{ $station->location }}</td>
                        <td>{{ $station->mobile_number }}</td>
                        <td>KSh {{ number_format($station->monthly_loss, 2) }}</td>
                        <td>
                            @if(count($station->airtime_payments) > 0)
                                <span class="badge badge-success">{{ count($station->airtime_payments) }} active</span>
                            @else
                                <span>None</span>
                            @endif
                        </td>
                        <td>
                            @if(count($station->internet_payments) > 0)
                                <span class="badge badge-warning">{{ count($station->internet_payments) }} pending</span>
                            @else
                                <span>None</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($stations->hasPages())
                <div style="margin-top: 20px;">
                    {{ $stations->links() }}
                </div>
            @endif
            @else
            <p>No stations found</p>
            @endif
        </div>

        <!-- Overdue Internet Bills -->
        @if(isset($overdue_internet) && count($overdue_internet) > 0)
        <div class="section">
            <h2>Overdue Internet Bills ({{ count($overdue_internet) }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>Station</th>
                        <th>Provider</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdue_internet as $payment)
                    <tr>
                        <td>{{ $payment->station->name ?? 'N/A' }}</td>
                        <td>{{ $payment->provider->name ?? 'N/A' }}</td>
                        <td>{{ $payment->account_number }}</td>
                        <td>KSh {{ number_format($payment->total_due, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}</td>
                        <td><span class="badge badge-danger">Overdue</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Expiring Airtime -->
        @if(isset($expiring_airtime) && count($expiring_airtime) > 0)
        <div class="section">
            <h2>Expiring Airtime</h2>
            @foreach($expiring_airtime as $timeframe => $items)
                <h3>{{ $timeframe }} ({{ count($items) }})</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Mobile Number</th>
                            <th>Amount</th>
                            <th>Provider</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->station->name ?? 'N/A' }}</td>
                            <td>{{ $item->mobile_number }}</td>
                            <td>KSh {{ number_format($item->amount, 2) }}</td>
                            <td>{{ $item->network_provider }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->expected_expiry)->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
        @endif

        <!-- Recent Payments -->
        <div class="section">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h2>Recent Internet Payments</h2>
                    @if(isset($recent_internet) && count($recent_internet) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_internet as $payment)
                            <tr>
                                <td>{{ $payment->station->name ?? 'N/A' }}</td>
                                <td>KSh {{ number_format($payment->total_due, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}</td>
                                <td>
                                    @if($payment->due_date < now())
                                        <span class="badge badge-danger">Overdue</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p>No recent internet payments</p>
                    @endif
                </div>
                <div>
                    <h2>Recent Airtime Payments</h2>
                    @if(isset($recent_airtime) && count($recent_airtime) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Mobile</th>
                                <th>Amount</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_airtime as $payment)
                            <tr>
                                <td>{{ $payment->station->name ?? 'N/A' }}</td>
                                <td>{{ $payment->mobile_number }}</td>
                                <td>KSh {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p>No recent airtime payments</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</body>
</html>
