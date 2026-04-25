<div class="row mb-4">
            <div class="col-12">
                <div class="card quick-actions-card">
                    <div class="card-header">
                        <h5 class="mb-0" id="airtime-payment"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach([
                                ['route' => 'payments.internet.create', 'icon' => 'wifi', 'label' => 'New Internet', 'subtext' => 'Add payment', 'color' => 'primary'],
                                ['route' => 'payments.airtime.create', 'icon' => 'phone-alt', 'label' => 'New Airtime', 'subtext' => 'Add topup', 'color' => 'success'],
                                ['route' => 'stations.create', 'icon' => 'plus-circle', 'label' => 'Add Station', 'subtext' => 'New station', 'color' => 'info'],
                                ['route' => 'internet-providers.create', 'icon' => 'network-wired', 'label' => 'Add Provider', 'subtext' => 'Internet vendor', 'color' => 'warning'],
                                ['route' => 'payments.upcoming', 'icon' => 'clock', 'label' => 'Upcoming', 'subtext' => 'View due payments', 'color' => 'danger'],
                                ['route' => 'payments.overdue', 'icon' => 'exclamation-triangle', 'label' => 'Overdue', 'subtext' => 'View overdue', 'color' => 'purple']
                            ] as $action)
                                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                                    <a href="{{ route($action['route']) }}" class="btn btn-outline-{{ $action['color'] }} w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center">
                                        <i class="fas fa-{{ $action['icon'] }} fa-2x mb-2"></i>
                                        <span>{{ $action['label'] }}</span>
                                        <small class="text-muted">{{ $action['subtext'] }}</small>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
