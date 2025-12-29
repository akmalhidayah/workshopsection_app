@forelse($adminNotifications as $notif)
<a href="{{ route('admin.notifications.read', $notif->id) }}"
   class="block px-4 py-3 hover:bg-gray-100 transition">
    <div class="flex items-start gap-3">

        <!-- Indicator -->
        <span class="mt-1 w-2 h-2 rounded-full
            {{ $notif->priority === 'high' ? 'bg-red-600' : 'bg-blue-500' }}
            {{ $notif->is_read ? 'opacity-30' : '' }}">
        </span>

        <div class="flex-1">
            <p class="text-sm {{ !$notif->is_read ? 'font-semibold' : '' }}">
                {{ $notif->title }}
            </p>
            <p class="text-xs text-gray-500">
                {{ $notif->description }} • {{ $notif->created_at->diffForHumans() }}
            </p>
        </div>
    </div>
</a>
@empty
<div class="px-4 py-6 text-center text-sm text-gray-500">
    Tidak ada notifikasi
</div>
@endforelse
