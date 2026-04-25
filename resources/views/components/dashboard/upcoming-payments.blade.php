            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="airtime-payment"><i class="fas fa-clock me-2"></i> Upcoming Payments (Next 7 Days)</h5>
                        <a href="{{ route('payments.upcoming') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Station</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($upcomingInternet as $payment)
                                        <tr>
                                            <td>{{ $payment->station->name }}</td>
                                            <td><i class="fas fa-wifi text-success me-1"></i> Internet</td>
                                            <td><strong>KES {{ number_format((float)$payment->total_due, 2) }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('M d') }}</td>
                                            <td><span class="badge bg-warning">Pending</span></td>
                                        </tr>
                                    @endforeach

                                    @forelse($upcomingAirtime as $payment)
                                        <tr>
                                            <td>{{ $payment->station->name }}</td>
                                            <td><i class="fas fa-phone-alt text-info me-1"></i> Airtime</td>
                                            <td><strong>KES {{ number_format((float)$payment->amount, 2) }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($payment->expected_expiry)->format('M d') }}</td>
                                            <td><span class="badge bg-info">Active</span></td>
                                        </tr>
                                    @endforeach

                                    @if($upcomingInternet->isEmpty() && $upcomingAirtime->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                                No upcoming payments in the next 7 days
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
