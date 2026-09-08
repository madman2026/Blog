<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function notifications(): LengthAwarePaginator
    {
        return auth()->user()->notifications()->latest()->paginate(20);
    }

    public function markAsRead(string $notificationId): void
    {
        auth()->user()->notifications()->whereKey($notificationId)->firstOrFail()->markAsRead();
        unset($this->notifications);
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        unset($this->notifications);
    }

    public function destination(DatabaseNotification $notification): string
    {
        if (isset($notification->data['url'])) {
            return (string) $notification->data['url'];
        }

        if (isset($notification->data['post_id'])) {
            return route('blog.manage.posts.edit', $notification->data['post_id']);
        }

        if (str_contains((string) ($notification->data['type'] ?? ''), 'author-application')) {
            return route('user.settings');
        }

        return route('dashboard');
    }
};
?>

<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Inbox') }}</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight">{{ __('Notifications') }}</h1>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <flux:button wire:click="markAllAsRead" size="sm" variant="ghost">{{ __('Mark all as read') }}</flux:button>
        @endif
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        <div class="divide-y divide-slate-100 dark:divide-white/10">
            @forelse ($this->notifications as $notification)
                <a
                    href="{{ $this->destination($notification) }}"
                    wire:navigate
                    wire:click="markAsRead('{{ $notification->getKey() }}')"
                    wire:key="notification-{{ $notification->getKey() }}"
                    class="group flex gap-4 p-5 transition hover:bg-slate-50 dark:hover:bg-white/5 {{ $notification->read_at ? 'opacity-70' : '' }}"
                >
                    <span class="mt-1 size-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300 dark:bg-slate-700' : 'bg-cyan-500' }}"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-bold text-slate-900 dark:text-white">{{ __($notification->data['message'] ?? 'Notification') }}</span>
                        @if (isset($notification->data['status']))<span class="mt-1 block text-xs font-semibold text-cyan-700 dark:text-cyan-300">{{ __($notification->data['status']) }}</span>@endif
                        <time class="mt-2 block text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</time>
                    </span>
                    <x-heroicon-o-chevron-left class="mt-2 size-4 shrink-0 text-slate-400 transition group-hover:-translate-x-1" />
                </a>
            @empty
                <div class="p-14 text-center text-sm text-slate-500">{{ __('Your notification inbox is empty.') }}</div>
            @endforelse
        </div>
    </div>

    <div class="mt-8">{{ $this->notifications->links() }}</div>
</div>
