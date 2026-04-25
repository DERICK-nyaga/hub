            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="airtime-payment"><i class="fas fa-history me-2"></i> Recent Payments</h5>
                        <a href="{{ route('payments.airtime.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Station</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentInternet as $payment)
                                        <tr>
                                            <td>{{ $payment->station->name }}</td>
                                            <td><i class="fas fa-wifi text-success me-1"></i> Internet</td>
                                            <td><strong>KES {{ number_format((float)$payment->total_due, 2) }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('M d') }}</td>
                                            <td>
                                                @if($payment->status == 'paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($payment->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-danger">{{ ucfirst($payment->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                    @forelse($recentAirtime as $payment)
                                        <tr>
                                            <td>{{ $payment->station->name }}</td>
                                            <td><i class="fas fa-phone-alt text-info me-1"></i> Airtime</td>
                                            <td><strong>KES {{ number_format((float)$payment->amount, 2) }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($payment->topup_date)->format('M d') }}</td>
                                            <td><span class="badge bg-success">Active</span></td>
                                        </tr>
                                    @endforeach

                                    @if($recentInternet->isEmpty() && $recentAirtime->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                <i class="fas fa-receipt fa-2x mb-2"></i><br>
                                                No recent payments
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
