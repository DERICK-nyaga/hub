@php
    $notificationService = app(App\Services\NotificationService::class);
    $unreadCount = $notificationService->getUnreadCount(auth()->id());
    $notifications = $notificationService->getUserNotifications(auth()->id(), 5);
@endphp

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
        <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end" style="width: 350px;">
        <li>
            <h6 class="dropdown-header">
                Notifications
                @if($unreadCount > 0)
                <span class="badge bg-danger ms-2">{{ $unreadCount }} new</span>
                @endif
            </h6>
        </li>
        <li><hr class="dropdown-divider"></li>

        @forelse($notifications as $notification)
        <li>
            <a class="dropdown-item d-flex align-items-center py-2"
               href="{{ route('notifications.show', $notification) }}"
               onclick="markNotificationAsRead({{ $notification->id }})">
                <div class="me-3">
                    @if($notification->type == 'airtime_expiry')
                    <i class="fas fa-phone-alt text-warning"></i>
                    @elseif($notification->type == 'internet_expiry')
                    <i class="fas fa-wifi text-info"></i>
                    @else
                    <i class="fas fa-bell text-primary"></i>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                    <div class="{{ is_null($notification->read_at) ? 'fw-bold' : '' }}">
                        {{ Str::limit($notification->message, 50) }}
                    </div>
                </div>
                @if(is_null($notification->read_at))
                <div class="ms-2">
                    <span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px;"></span>
                </div>
                @endif
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        @empty
        <li><a class="dropdown-item text-center text-muted py-3">No notifications</a></li>
        @endforelse

        <li>
            <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                View all notifications
            </a>
        </li>
    </ul>
</li>
