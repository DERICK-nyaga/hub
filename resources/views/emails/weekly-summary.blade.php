<x-mail::message>
# Weekly Payment Summary

Hello {{ $user->name }},

Here's your weekly summary of pending payments and expiries:

## 📊 Summary Statistics
- **Airtime expiring soon:** {{ $stats['airtime_expiring_soon'] }}
- **Internet bills due soon:** {{ $stats['internet_due_soon'] }}
- **Internet bills overdue:** {{ $stats['internet_overdue'] }}
- **Upcoming scheduled payments:** {{ $stats['upcoming_schedules'] }}
- **Total items requiring attention:** {{ $stats['total_notifications'] }}

## ⚠️ Urgent Items
@if($stats['internet_overdue'] > 0)
You have **{{ $stats['internet_overdue'] }} overdue internet bill(s)** that require immediate attention!
@endif

@if($stats['airtime_expiring_soon'] > 0)
**{{ $stats['airtime_expiring_soon'] }} airtime payment(s)** will expire this week.
@endif

## ✅ Recommended Actions
1. Check the dashboard for detailed information
2. Process overdue payments first
3. Schedule upcoming payments
4. Renew expiring airtime

<x-mail::button :url="route('dashboard')">
Go to Dashboard
</x-mail::button>

Best regards,<br>
{{ config('app.name') }} Management System
</x-mail::message>
