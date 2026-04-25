<div class="row" id="row">
            @foreach([
                ['key' => 'total_stations', 'label' => 'Total Stations', 'icon' => 'building', 'color' => 'primary', 'subtext' => 'Active stations'],
                ['key' => 'total_internet_payments', 'label' => 'Internet Payments', 'icon' => 'wifi', 'color' => 'success', 'subtext' => 'Monthly total', 'format' => 'currency'],
                ['key' => 'total_airtime_payments', 'label' => 'Airtime Payments', 'icon' => 'phone-alt', 'color' => 'info', 'subtext' => 'Monthly total', 'format' => 'currency'],
                ['key' => 'upcoming_payments', 'label' => 'Upcoming Payments', 'icon' => 'clock', 'color' => 'warning', 'subtext' => 'Next 7 days'],
                ['key' => 'overdue_payments', 'label' => 'Overdue Payments', 'icon' => 'exclamation-triangle', 'color' => 'danger', 'subtext' => 'Need attention'],
                ['key' => 'paid_payments', 'label' => 'Paid Payments', 'icon' => 'check-circle', 'color' => 'purple', 'subtext' => 'This month'],
                ['key' => 'total_providers', 'label' => 'Internet Providers', 'icon' => 'network-wired', 'color' => 'teal', 'subtext' => 'Active vendors'],
                ['key' => 'scheduled_payments', 'label' => 'Scheduled', 'icon' => 'calendar-check', 'color' => 'indigo', 'subtext' => 'Recurring'],
                ['key' => 'top_station', 'label' => 'Top Station', 'icon' => 'trophy', 'color' => 'cyan', 'subtext' => 'Highest payments'],
                ['key' => 'total_payments', 'label' => 'Total Payments', 'icon' => 'file-invoice-dollar', 'color' => 'orange', 'subtext' => 'All time'],
                ['key' => 'average_payment', 'label' => 'Avg. Payment', 'icon' => 'chart-line', 'color' => 'gray', 'subtext' => 'Per station', 'format' => 'currency']
            ] as $card)
                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                    <div class="stat-card card-{{ $card['color'] }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-subtitle mb-2">{{ $card['label'] }}</h6>
                                    <h2 class="mb-0">
                                        @if(isset($card['format']) && $card['format'] === 'currency')
                                            KES {{ number_format($stats[$card['key']] ?? 0, 0) }}
                                        @else
                                            {{ $stats[$card['key']] ?? ($card['key'] === 'top_station' ? 'N/A' : 0) }}
                                        @endif
                                    </h2>
                                    <p class="card-text mb-0"><small>{{ $card['subtext'] }}</small></p>
                                </div>
                                <div class="icon-container">
                                    <i class="fas fa-{{ $card['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="progress mt-3">
                                <div class="progress-bar" role="progressbar" style="width: {{ $stats['progress_' . $card['key']] ?? 75 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
</div>
