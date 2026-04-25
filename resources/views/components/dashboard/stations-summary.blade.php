<div class="table-container mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-map-marked-alt me-2"></i>Stations Payment Summary</h5>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search stations...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Location</th>
                            <th>Internet</th>
                            <th>Airtime</th>
                            <th>Total Paid</th>
                            <th>Pending</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stations as $station)
                            @php
                                $internetTotal = $station->internetPayments->where('status', 'paid')->sum('total_due');
                                $internetPending = $station->internetPayments->where('status', 'pending')->sum('total_due');
                                $airtimeTotal = $station->airtimePayments->where('status', 'active')->sum('amount');
                            @endphp
                            <tr>
                                <td>{{ $station->name }}</td>
                                <td><small class="text-muted">{{ $station->location }}</small></td>
                                <td>
                                    KES {{ number_format((float)$internetTotal, 2) }}
                                    @if($internetPending > 0)
                                        <br><small class="text-warning">Pending: KES {{ number_format((float)$internetPending, 2) }}</small>
                                    @endif
                                </td>
                                <td>KES {{ number_format((float)$airtimeTotal, 2) }}</td>
                                <td class="text-success">KES {{ number_format((float)($internetTotal + $airtimeTotal), 2) }}</td>
                                <td>KES {{ number_format((float)$internetPending, 2) }}</td>
                                <td>
                                    @if($internetPending > 0)
                                        <span class="badge rounded-pill bg-warning">Pending</span>
                                    @else
                                        <span class="badge rounded-pill bg-success">Up to date</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('payments.station', $station->station_id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('payments.internet.create') }}?station_id={{ $station->station_id }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    <i class="fas fa-building fa-2x mb-2"></i><br>
                                    No stations found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($stations instanceof \Illuminate\Pagination\LengthAwarePaginator && $stations->hasPages())
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mt-3">
                        <li class="page-item {{ $stations->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $stations->previousPageUrl() }}">Previous</a>
                        </li>
                        @for ($i = 1; $i <= $stations->lastPage(); $i++)
                            <li class="page-item {{ $stations->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $stations->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        <li class="page-item {{ !$stations->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $stations->nextPageUrl() }}">Next</a>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
