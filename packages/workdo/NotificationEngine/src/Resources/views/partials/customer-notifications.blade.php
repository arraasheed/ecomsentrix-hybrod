<li class="notification-engine-customer-tab">
    <details>
        <summary class="block py-2 px-3 rounded-md cursor-pointer">
            <i class="fas fa-bell text-gray-500"></i>
            <span>{{ __('Notifications') }}</span>
            @if ($notifications->where('is_read', false)->count())
                <span class="ml-2 rounded-full bg-red-500 px-2 text-xs text-white">{{ $notifications->where('is_read', false)->count() }}</span>
            @endif
        </summary>
        <div class="mt-2 space-y-2 px-3">
            @forelse ($notifications as $notification)
                <div class="border-b pb-2 text-sm {{ $notification->is_read ? 'opacity-60' : '' }}">
                    @if ($notification->target_url)
                        <a href="{{ $notification->target_url }}" class="font-medium">{{ $notification->title }}</a>
                    @else
                        <span class="font-medium">{{ $notification->title }}</span>
                    @endif
                    <p>{{ $notification->message }}</p>
                    @if (!$notification->is_read)
                        <form method="POST" action="{{ route('notificationengine.customer.read', $notification) }}">
                            @csrf
                            <button type="submit" class="text-xs text-primary">{{ __('Mark as read') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No notifications') }}</p>
            @endforelse
        </div>
    </details>
</li>
