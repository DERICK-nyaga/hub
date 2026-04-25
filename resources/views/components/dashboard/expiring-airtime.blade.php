            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="airtime-payment"><i class="fas fa-phone-alt text-warning me-2"></i>Airtime Expiring This Week</h5>
                        <span class="badge bg-warning">{{ $stats['airtime_expiring_soon'] ?? 0 }}</span>
                    </div>
                    <div class="card-body">
                        @if($expiringAirtimeGrouped->isNotEmpty())
                            @foreach($expiringAirtimeGrouped as $timeframe => $payments)
                                @if($payments->isNotEmpty())
                                    <div class="mb-3">
                                        <h6 class="text-muted">{{ $timeframe }}</h6>
                                        <div class="list-group">
                                            @foreach($payments as $payment)
                                                <div class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <div>
                                                            <strong>{{ $payment->station->name ?? 'N/A' }}</strong>
                                                            <div class="small text-muted">
                                                                {{ $payment->mobile_number }}
                                                                <span class="badge bg-info ms-2">{{ $payment->network_provider }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <div><strong>KES {{ number_format($payment->amount, 2) }}</strong></div>
                                                            <small class="text-muted">Expires: {{ $payment->expected_expiry->format('M d, Y') }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p>No airtime expiring this week</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
