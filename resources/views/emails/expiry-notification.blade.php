<x-mail::message>
# Payment Expiry Notification

{{ $notification->message }}

**Details:**

@if($notification->type == 'airtime_expiry')
- **Type:** Airtime Renewal
- **Station:** {{ $notification->metadata['station_name'] }}
- **Mobile:** {{ $notification->metadata['mobile_number'] }}
- **Network:** {{ $notification->metadata['network_provider'] }}
- **Amount:** KES {{ number_format($notification->metadata['amount'], 2) }}
- **Expiry Date:** {{ \Carbon\Carbon::parse($notification->metadata['expiry_date'])->format('d/m/Y') }}
- **Days Remaining:** {{ $notification->metadata['days_remaining'] }}

@elseif(in_array($notification->type, ['internet_due', 'internet_overdue']))
- **Type:** Internet Bill
- **Station:** {{ $notification->metadata['station_name'] }}
- **Provider:** {{ $notification->metadata['vendor_name'] }}
- **Account:** {{ $notification->metadata['account_number'] }}
- **Amount Due:** KES {{ number_format($notification->metadata['total_due'], 2) }}
- **Due Date:** {{ \Carbon\Carbon::parse($notification->metadata['due_date'])->format('d/m/Y') }}
- **Billing Month:** {{ \Carbon\Carbon::parse($notification->metadata['billing_month'])->format('F Y') }}
- **Status:** {{ $notification->type == 'internet_overdue' ? 'OVERDUE' : 'DUE SOON' }}

@elseif($notification->type == 'payment_schedule')
- **Type:** Scheduled Payment
- **Station:** {{ $notification->metadata['station_name'] }}
- **Payment Type:** {{ ucfirst($notification->metadata['payment_type']) }}
- **Amount:** KES {{ number_format($notification->metadata['scheduled_amount'], 2) }}
- **Schedule Date:** {{ \Carbon\Carbon::parse($notification->metadata['scheduled_date'])->format('d/m/Y') }}
- **Frequency:** {{ ucfirst($notification->metadata['frequency']) }}
@endif

<x-mail::button :url="$notification->related_url ?? route('dashboard')">
View Details
</x-mail::button>

**Action Required:** {{ $notification->type == 'internet_overdue' ? 'URGENT - Payment is overdue!' : 'Please review and take appropriate action.' }}

Thanks,<br>
{{ config('app.name') }} Billing System
</x-mail::message>
