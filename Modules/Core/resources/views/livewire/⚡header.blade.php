<?php

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function unreadNotificationsCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    #[On('notification-received')]
    public function refreshNotifications(): void
    {
        unset($this->unreadNotificationsCount);
    }
};
?>

<header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/85 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/85">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" wire:navigate class="group flex shrink-0 items-center gap-2.5">
            <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-sm font-black text-white shadow-lg shadow-indigo-500/20 transition group-hover:scale-105">
                {{ Str::upper(Str::substr(config('app.name'), 0, 1)) }}
            </span>
            <span class="hidden text-base font-black tracking-tight text-slate-950 sm:block dark:text-white">{{ config('app.name') }}</span>
        </a>

        <nav class="ms-2 hidden items-center gap-1 md:flex" aria-label="{{ __('Main navigation') }}">
            <flux:navbar.item href="{{ route('home') }}" :current="request()->routeIs('home')" wire:navigate>
                {{ __('Home') }}
            </flux:navbar.item>
            <flux:navbar.item href="{{ route('blog.posts.index') }}" :current="request()->routeIs('blog.posts.*')" wire:navigate>
                {{ __('Articles') }}
            </flux:navbar.item>
            @can('viewAny', \Modules\Blog\Models\Post::class)
                <flux:navbar.item href="{{ route('writer.dashboard') }}" :current="request()->routeIs('writer.*') || request()->routeIs('blog.manage.*')" wire:navigate>
                    {{ __('Writer studio') }}
                </flux:navbar.item>
            @endcan
        </nav>

        <div class="ms-auto flex items-center gap-1.5">
            <form method="POST" action="{{ route('locale.update', app()->getLocale() === 'fa' ? 'en' : 'fa') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" class="font-mono uppercase">
                    {{ app()->getLocale() === 'fa' ? 'EN' : 'FA' }}
                </flux:button>
            </form>

            <flux:button
                variant="ghost"
                size="sm"
                icon="moon"
                aria-label="{{ __('Toggle color theme') }}"
                x-data
                x-on:click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
            />

            @auth
                <flux:button href="{{ route('notifications.index') }}" variant="ghost" size="sm" icon="bell" wire:navigate>
                    @if ($this->unreadNotificationsCount)
                        <span class="ms-1 rounded-full bg-cyan-500 px-1.5 text-[10px] font-bold text-slate-950">{{ min($this->unreadNotificationsCount, 99) }}</span>
                    @endif
                </flux:button>

                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" size="sm" icon:trailing="chevron-down">
                        {{ auth()->user()->username }}
                    </flux:button>
                    <flux:menu>
                        <flux:menu.item icon="user" href="{{ route('user.profile', auth()->user()) }}" wire:navigate>{{ __('Profile') }}</flux:menu.item>
                        <flux:menu.item icon="cog-6-tooth" href="{{ route('user.settings') }}" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        <flux:menu.item icon="squares-2x2" href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</flux:menu.item>
                        @can(\Modules\User\Enums\UserPermission::PostsViewAny->value)
                            <flux:menu.item icon="pencil-square" href="{{ route('writer.dashboard') }}" wire:navigate>{{ __('Writer panel') }}</flux:menu.item>
                        @endcan
                        @can(\Modules\User\Enums\UserPermission::UsersViewAny->value)
                            <flux:menu.item icon="shield-check" href="{{ route('admin.dashboard') }}" wire:navigate>{{ __('Admin panel') }}</flux:menu.item>
                        @endcan
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">{{ __('Log out') }}</flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <flux:button href="{{ route('auth.login') }}" variant="ghost" size="sm" wire:navigate>{{ __('Log in') }}</flux:button>
                <flux:button href="{{ route('auth.register') }}" variant="primary" size="sm" wire:navigate>{{ __('Join') }}</flux:button>
            @endauth
        </div>
    </div>

    <nav class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 pb-2 md:hidden" aria-label="{{ __('Mobile navigation') }}">
        <flux:navbar.item href="{{ route('home') }}" :current="request()->routeIs('home')" wire:navigate>{{ __('Home') }}</flux:navbar.item>
        <flux:navbar.item href="{{ route('blog.posts.index') }}" :current="request()->routeIs('blog.posts.*')" wire:navigate>{{ __('Articles') }}</flux:navbar.item>
        @can('viewAny', \Modules\Blog\Models\Post::class)
            <flux:navbar.item href="{{ route('writer.dashboard') }}" :current="request()->routeIs('writer.*')" wire:navigate>{{ __('Studio') }}</flux:navbar.item>
        @endcan
        @can(\Modules\User\Enums\UserPermission::UsersViewAny->value)
            <flux:navbar.item href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.*')" wire:navigate>{{ __('Admin') }}</flux:navbar.item>
        @endcan
    </nav>
</header>
