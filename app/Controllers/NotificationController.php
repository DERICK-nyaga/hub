<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'all');

        $query = auth()->user()->notifications();

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = $query->latest()->paginate(20);
        $stats = $this->notificationService->getDashboardStats();

        return view('notifications.index', compact('notifications', 'stats', 'type'));
    }

    public function show(Notification $notification)
    {
        // Ensure user owns the notification
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        // Redirect to related item if possible
        if ($notification->related) {
            return redirect()->to($this->getRelatedItemRoute($notification));
        }

        return back()->with('info', 'Notification marked as read');
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService->getUnreadCount(auth()->id())
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $type = $request->get('type');

        $query = auth()->user()->notifications()->unread();

        if ($type) {
            $query->where('type', $type);
        }

        $query->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read');
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notification deleted');
    }

    public function cleanupOld(Request $request)
    {
        $days = $request->get('days', 30);

        $deleted = auth()->user()->notifications()
            ->where('created_at', '<', now()->subDays($days))
            ->whereNotNull('read_at')
            ->delete();

        return back()->with('success', "{$deleted} old notifications cleaned up");
    }

    public function checkExpiries(Request $request)
    {
        $results = $this->notificationService->checkAllExpiries();

        $total = $results['airtime']->count() +
                 $results['internet']->count() +
                 $results['overdue_payments']->count() +
                 $results['upcoming_schedules']->count();

        return response()->json([
            'success' => true,
            'message' => "Found {$total} items requiring attention",
            'counts' => [
                'airtime' => $results['airtime']->count(),
                'internet_due' => $results['internet']->count(),
                'internet_overdue' => $results['overdue_payments']->count(),
                'schedules' => $results['upcoming_schedules']->count()
            ]
        ]);
    }

    private function getRelatedItemRoute(Notification $notification)
    {
        switch ($notification->related_type) {
            case 'App\Models\AirtimePayment':
                $params = [];
                if ($notification->related && $notification->related->station_id) {
                    $params['station_id'] = $notification->related->station_id;
                }
                return route('payments.airtime.index', $params);

            case 'App\Models\InternetPayment':
                $params = [];
                if ($notification->related && $notification->related->station_id) {
                    $params['station_id'] = $notification->related->station_id;
                }
                if ($notification->related && $notification->related->status == 'overdue') {
                    $params['status'] = 'overdue';
                }
                return route('payments.internet.index', $params);

            case 'App\Models\PaymentSchedule':
                return route('payment-schedules.index');

            default:
                return route('dashboard');
        }
    }
}
