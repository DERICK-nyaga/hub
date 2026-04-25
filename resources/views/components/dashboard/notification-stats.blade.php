        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center">
                            @foreach([
                                ['key' => 'airtime_expiring_soon', 'icon' => 'phone-alt', 'label' => 'Airtime Expiring', 'color' => 'warning'],
                                ['key' => 'internet_due_soon', 'icon' => 'wifi', 'label' => 'Internet Due Soon', 'color' => 'info'],
                                ['key' => 'internet_overdue', 'icon' => 'exclamation-triangle', 'label' => 'Overdue Bills', 'color' => '#FFFFFF', 'bg_danger' => true],
                                ['key' => 'upcoming_schedules', 'icon' => 'calendar-alt', 'label' => 'Upcoming Schedules', 'color' => 'primary']
                            ] as $stat)
                                <div class="col-md-3">
                                    <div class="p-3 border rounded {{ isset($stat['bg_danger']) ? 'bg-danger text-white' : '' }}">
                                        <h5 class="text-{{ $stat['color'] }}">
                                            <i class="fas fa-{{ $stat['icon'] }}"></i> {{ $stats[$stat['key']] ?? 0 }}
                                        </h5>
                                        <small>{{ $stat['label'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
