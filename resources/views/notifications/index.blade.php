<x-app-layout>
<h1 class="text-3xl font-bold mb-6">Notifications</h1>
<div class="space-y-4">
@forelse($notifications as $notification)
    <article class="p-5 rounded-xl border border-gray-700 bg-gray-900">
        <h2 class="font-semibold">{{ $notification->data['type'] }} @if(!$notification->read_at)<span class="text-blue-400">— Unread</span>@endif</h2>
        <p>{{ $notification->data['booking_number'] }} · {{ $notification->data['hotel'] }}</p>
        <p class="text-sm text-gray-400">{{ $notification->created_at->format('d.m.Y H:i') }}</p>
        <a class="text-blue-400" href="{{ route('bookings.show', $notification->data['booking_number']) }}">View booking</a>
        @if(!$notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="mt-2 px-4 py-2 bg-gray-700 rounded-lg">Mark as read</button></form>@endif
    </article>
@empty<p>No notifications yet.</p>@endforelse
</div>
{{ $notifications->links() }}
</x-app-layout>
