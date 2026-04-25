<div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="airtime-payment"><i class="fas fa-wifi text-danger me-2"></i>Internet Bills - Due & Overdue</h5>
                        <div>
                            @if($stats['internet_overdue'] > 0)
                                <span class="badge bg-danger me-2">{{ $stats['internet_overdue'] }} overdue</span>
                            @endif
                            <span class="badge bg-warning">{{ $stats['internet_due_soon'] }} due soon</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($overdueInternet->isNotEmpty())
                            <div class="mb-3">
                                <h6 class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> Overdue</h6>
                                <div class="list-group">
                                    @foreach($overdueInternet as $payment)
                                        <div class="list-group-item list-group-item-danger">
                                            <div class="d-flex w-100 justify-content-between">
                                                <div>
                                                    <strong>{{ $payment->station->name ?? 'N/A' }}</strong>
                                                    <div class="small text-muted">
                                                        {{ $payment->provider->name ?? 'No Provider' }}
                                                        <span class="badge bg-secondary ms-2">{{ $payment->account_number ?? 'No Account' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div><strong>KES {{ number_format($payment->total_due, 2) }}</strong></div>
                                                    <small class="text-danger">Overdue by {{ now()->diffInDays($payment->due_date) }} days</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dueInternetGrouped->isNotEmpty())
                            @foreach($dueInternetGrouped as $timeframe => $payments)
                                @if($payments->isNotEmpty())
                                    <div class="mb-3">
                                        <h6 class="text-warning">{{ $timeframe }}</h6>
                                        <div class="list-group">
                                            @foreach($payments as $payment)
                                                <div class="list-group-item list-group-item-warning">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <div>
                                                            <strong>{{ $payment->station->name ?? 'N/A' }}</strong>
                                                            <div class="small text-muted">
                                                                {{ $payment->provider->name ?? 'No Provider' }}
                                                                <span class="badge bg-info ms-2">{{ $payment->account_number ?? 'No Account' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <div><strong>KES {{ number_format($payment->total_due, 2) }}</strong></div>
                                                            <small class="text-muted">Due: {{ $payment->due_date->format('M d, Y') }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        @if($overdueInternet->isEmpty() && (!$dueInternetGrouped->isNotEmpty() || $dueInternetGrouped->flatten()->isEmpty()))
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p>All internet bills are up to date</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
