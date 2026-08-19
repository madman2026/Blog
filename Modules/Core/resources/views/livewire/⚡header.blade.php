<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function unreadNotificationsCount(): int
    {
        $user = auth()->user();

        if (! $user) {
            return 0;
        }

        return $user
            ->notifications()
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function avatarUrl(): ?string
    {
        $avatar = auth()->user()?->avatar;

        if (! $avatar) {
            return null;
        }

        if (Str::startsWith($avatar, [
            'http://',
            'https://',
            '/',
        ])) {
            return $avatar;
        }

        return Storage::disk('public')->url($avatar);
    }
};

?>

@php
    $user = auth()->user();
@endphp


<flux:header
    container
    class="
        sticky
        top-0
        z-40

        min-h-16

        border-b
        border-zinc-200/70

        bg-white/80

        shadow-xs
        shadow-zinc-950/5

        backdrop-blur-xl
        supports-[backdrop-filter]:bg-white/70

        dark:border-white/10
        dark:bg-zinc-950/80
        dark:shadow-black/10
        dark:supports-[backdrop-filter]:bg-zinc-950/70
    "
>

    {{-- Mobile Sidebar Toggle --}}
    @auth
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />
    @endauth


    {{-- Brand --}}
    <flux:brand
        href="{{ route('home') }}"
        name="{{ config('app.name') }}"
        wire:navigate
        class="shrink-0"
    >
        <x-slot
            name="logo"
            class="
                flex
                size-8
                items-center
                justify-center

                rounded-lg

                bg-indigo-600

                text-sm
                font-bold
                text-white

                shadow-sm
                shadow-indigo-600/20
            "
        >
            {{ Str::upper(Str::substr(config('app.name'), 0, 1)) }}
        </x-slot>
    </flux:brand>


    {{-- Desktop Navigation --}}
    <flux:navbar class="hidden lg:flex">

        {{-- Home --}}
        <flux:navbar.item
            icon="home"
            href="{{ route('home') }}"
            :current="request()->routeIs('home')"
            wire:navigate
        >
            {{ __('core::header.navigation.home') }}
        </flux:navbar.item>


        @auth

            {{-- Dashboard --}}
            <flux:navbar.item
                icon="squares-2x2"
                href="{{ route('dashboard.index') }}"
                :current="request()->routeIs('dashboard.*')"
                wire:navigate
            >
                {{ __('core::header.navigation.dashboard') }}
            </flux:navbar.item>


            {{-- Notifications --}}
            <flux:navbar.item
                icon="bell"
                href="{{ route('notifications.index') }}"
                :current="request()->routeIs('notifications.*')"

                :badge="
                    $this->unreadNotificationsCount > 0
                        ? (
                            $this->unreadNotificationsCount > 99
                                ? '99+'
                                : $this->unreadNotificationsCount
                        )
                        : null
                "

                wire:navigate
            >
                {{ __('core::header.navigation.notifications') }}
            </flux:navbar.item>


            {{-- Documents --}}
            @can('documents.view')

                <flux:navbar.item
                    icon="document-text"
                    href="{{ route('documents.index') }}"
                    :current="request()->routeIs('documents.*')"
                    wire:navigate
                >
                    {{ __('core::header.navigation.documents') }}
                </flux:navbar.item>

            @endcan


            {{-- Calendar --}}
            @can('calendar.view')

                <flux:navbar.item
                    icon="calendar-days"
                    href="{{ route('calendar.index') }}"
                    :current="request()->routeIs('calendar.*')"
                    wire:navigate
                >
                    {{ __('core::header.navigation.calendar') }}
                </flux:navbar.item>

            @endcan


            {{-- Projects --}}
            @can('projects.view')

                <flux:navbar.item
                    icon="folder"
                    href="{{ route('projects.index') }}"
                    :current="request()->routeIs('projects.*')"
                    wire:navigate
                >
                    {{ __('core::header.navigation.projects') }}
                </flux:navbar.item>

            @endcan


            {{-- Management --}}
            @canany([
                'users.view',
                'roles.view',
                'permissions.view',
            ])

                <flux:separator
                    vertical
                    variant="subtle"
                    class="my-2"
                />


                <flux:dropdown>

                    <flux:navbar.item
                        icon="shield-check"
                        icon:trailing="chevron-down"
                        :current="request()->routeIs('admin.*')"
                    >
                        {{ __('core::header.management.title') }}
                    </flux:navbar.item>


                    <flux:navmenu>

                        {{-- Users --}}
                        @can('users.view')

                            <flux:navmenu.item
                                icon="users"
                                href="{{ route('admin.users.index') }}"
                                wire:navigate
                            >
                                {{ __('core::header.management.users') }}
                            </flux:navmenu.item>

                        @endcan


                        {{-- Roles --}}
                        @can('roles.view')

                            <flux:navmenu.item
                                icon="identification"
                                href="{{ route('admin.roles.index') }}"
                                wire:navigate
                            >
                                {{ __('core::header.management.roles') }}
                            </flux:navmenu.item>

                        @endcan


                        {{-- Permissions --}}
                        @can('permissions.view')

                            <flux:navmenu.item
                                icon="key"
                                href="{{ route('admin.permissions.index') }}"
                                wire:navigate
                            >
                                {{ __('core::header.management.permissions') }}
                            </flux:navmenu.item>

                        @endcan

                    </flux:navmenu>

                </flux:dropdown>

            @endcanany

        @endauth

    </flux:navbar>


    <flux:spacer />


    {{-- Header Actions --}}
    <flux:navbar>

        @auth

            {{-- Search --}}
            <flux:navbar.item
                icon="magnifying-glass"
                href="{{ route('search.index') }}"
                :current="request()->routeIs('search.*')"
                label="{{ __('core::header.actions.search') }}"
                wire:navigate
            />


            {{-- Settings --}}
            <flux:navbar.item
                class="max-lg:hidden"

                icon="cog-6-tooth"

                href="{{ route('account.settings') }}"
                :current="request()->routeIs('account.settings')"

                label="{{ __('core::header.actions.settings') }}"

                wire:navigate
            />

        @endauth


        {{-- Help --}}
        <flux:navbar.item
            class="max-lg:hidden"

            icon="question-mark-circle"

            href="{{ route('help.index') }}"
            :current="request()->routeIs('help.*')"

            label="{{ __('core::header.actions.help') }}"

            wire:navigate
        />

    </flux:navbar>


    {{-- Authenticated Profile --}}
    @auth

        <div
            x-data
            class="ms-1"
        >

            <flux:dropdown
                position="bottom"
                align="end"
            >

                <flux:profile
                    circle

                    name="{{ $user->username }}"

                    :avatar="$this->avatarUrl"

                    avatar:name="{{ $user->username }}"
                />


                <flux:menu class="min-w-60">

                    {{-- User Information --}}
                    <div class="px-3 py-2.5">

                        <div
                            class="
                                flex
                                min-w-0
                                items-center
                                gap-3
                            "
                        >

                            <div class="min-w-0 flex-1">

                                <flux:heading
                                    size="sm"
                                    class="truncate"
                                >
                                    {{ $user->username }}
                                </flux:heading>


                                <flux:text
                                    size="sm"
                                    class="
                                        mt-0.5
                                        truncate
                                        text-zinc-500
                                    "
                                >
                                    {{ $user->email }}
                                </flux:text>

                            </div>


                            @if($user->roles->isNotEmpty())

                                <flux:badge
                                    size="sm"
                                    variant="pill"
                                    color="indigo"
                                >
                                    {{ $user->roles->first()->name }}
                                </flux:badge>

                            @endif

                        </div>

                    </div>


                    <flux:menu.separator />


                    {{-- Profile --}}
                    <flux:menu.item
                        icon="user"
                        href="{{ route('account.profile') }}"
                        wire:navigate
                    >
                        {{ __('core::header.profile.profile') }}
                    </flux:menu.item>


                    {{-- Settings --}}
                    <flux:menu.item
                        icon="cog-6-tooth"
                        href="{{ route('account.settings') }}"
                        wire:navigate
                    >
                        {{ __('core::header.profile.settings') }}
                    </flux:menu.item>


                    {{-- Notifications --}}
                    <flux:menu.item
                        icon="bell"

                        href="{{ route('notifications.index') }}"

                        :suffix="
                            $this->unreadNotificationsCount > 0
                                ? (
                                    $this->unreadNotificationsCount > 99
                                        ? '99+'
                                        : $this->unreadNotificationsCount
                                )
                                : null
                        "

                        wire:navigate
                    >
                        {{ __('core::header.profile.notifications') }}
                    </flux:menu.item>


                    {{-- Admin Panel --}}
                    @canany([
                        'users.view',
                        'roles.view',
                        'permissions.view',
                    ])

                        <flux:menu.separator />


                        <flux:menu.item
                            icon="shield-check"
                            href="{{ route('admin.index') }}"
                            wire:navigate
                        >
                            {{ __('core::header.profile.admin_panel') }}
                        </flux:menu.item>

                    @endcanany


                    <flux:menu.separator />


                    {{-- Logout --}}
                    <flux:menu.item
                        icon="arrow-right-start-on-rectangle"

                        variant="danger"

                        x-on:click="$refs.logoutForm.requestSubmit()"
                    >
                        {{ __('auth::logout.action') }}
                    </flux:menu.item>

                </flux:menu>

            </flux:dropdown>


            <form
                x-ref="logoutForm"

                method="POST"

                action="{{ route('auth.logout') }}"

                class="hidden"
            >
                @csrf
            </form>

        </div>

    @endauth


    {{-- Guest Actions --}}
    @guest

        <div class="flex items-center gap-2">

            <flux:button
                href="{{ route('auth.login') }}"

                variant="ghost"
                size="sm"

                wire:navigate
            >
                {{ __('auth::login.action') }}
            </flux:button>


            <flux:button
                href="{{ route('auth.register') }}"

                variant="primary"
                size="sm"

                wire:navigate
            >
                {{ __('auth::register.action') }}
            </flux:button>

        </div>

    @endguest

</flux:header>
