<?php

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
};

?>

<div class="contents">

    @auth

        <flux:sidebar
            sticky
            collapsible="mobile"
            class="
                lg:hidden

                border-e
                border-zinc-200/70

                bg-white/95

                shadow-xl
                shadow-zinc-950/5

                backdrop-blur-xl

                dark:border-white/10
                dark:bg-zinc-950/95
                dark:shadow-black/20
            "
        >

            {{-- Header --}}
            <flux:sidebar.header
                class="
                    border-b
                    border-zinc-200/70
                    pb-3

                    dark:border-white/10
                "
            >

                <flux:sidebar.brand
                    href="{{ route('home') }}"
                    name="{{ config('app.name') }}"
                    wire:navigate
                />

                <flux:sidebar.collapse
                    class="lg:hidden"
                    tooltip="{{ __('core::sidebar.actions.close') }}"
                />

            </flux:sidebar.header>


            {{-- Main Navigation --}}
            <flux:sidebar.nav class="pt-3">

                {{-- Dashboard --}}
                <flux:sidebar.item
                    icon="squares-2x2"

                    href="{{ route('dashboard.index') }}"

                    :current="request()->routeIs('dashboard.*')"

                    wire:navigate
                >
                    {{ __('core::sidebar.dashboard') }}
                </flux:sidebar.item>


                {{-- Search --}}
                <flux:sidebar.item
                    icon="magnifying-glass"

                    href="{{ route('search.index') }}"

                    :current="request()->routeIs('search.*')"

                    wire:navigate
                >
                    {{ __('core::sidebar.search') }}
                </flux:sidebar.item>


                {{-- Notifications --}}
                <flux:sidebar.item
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
                    {{ __('core::sidebar.notifications') }}
                </flux:sidebar.item>


                {{-- Documents --}}
                @can('documents.view')

                    <flux:sidebar.item
                        icon="document-text"

                        href="{{ route('documents.index') }}"

                        :current="request()->routeIs('documents.*')"

                        wire:navigate
                    >
                        {{ __('core::sidebar.documents') }}
                    </flux:sidebar.item>

                @endcan


                {{-- Calendar --}}
                @can('calendar.view')

                    <flux:sidebar.item
                        icon="calendar-days"

                        href="{{ route('calendar.index') }}"

                        :current="request()->routeIs('calendar.*')"

                        wire:navigate
                    >
                        {{ __('core::sidebar.calendar') }}
                    </flux:sidebar.item>

                @endcan


                {{-- Projects --}}
                @can('projects.view')

                    <flux:sidebar.item
                        icon="folder"

                        href="{{ route('projects.index') }}"

                        :current="request()->routeIs('projects.*')"

                        wire:navigate
                    >
                        {{ __('core::sidebar.projects') }}
                    </flux:sidebar.item>

                @endcan


                {{-- Administration --}}
                @canany([
                    'users.view',
                    'roles.view',
                    'permissions.view',
                ])

                    <flux:sidebar.group
                        expandable

                        icon="shield-check"

                        heading="{{ __('core::sidebar.management.title') }}"

                        :expanded="request()->routeIs('admin.*')"

                        class="grid"
                    >

                        {{-- Users --}}
                        @can('users.view')

                            <flux:sidebar.item
                                icon="users"

                                href="{{ route('admin.users.index') }}"

                                :current="request()->routeIs('admin.users.*')"

                                wire:navigate
                            >
                                {{ __('core::sidebar.management.users') }}
                            </flux:sidebar.item>

                        @endcan


                        {{-- Roles --}}
                        @can('roles.view')

                            <flux:sidebar.item
                                icon="identification"

                                href="{{ route('admin.roles.index') }}"

                                :current="request()->routeIs('admin.roles.*')"

                                wire:navigate
                            >
                                {{ __('core::sidebar.management.roles') }}
                            </flux:sidebar.item>

                        @endcan


                        {{-- Permissions --}}
                        @can('permissions.view')

                            <flux:sidebar.item
                                icon="key"

                                href="{{ route('admin.permissions.index') }}"

                                :current="request()->routeIs('admin.permissions.*')"

                                wire:navigate
                            >
                                {{ __('core::sidebar.management.permissions') }}
                            </flux:sidebar.item>

                        @endcan

                    </flux:sidebar.group>

                @endcanany

            </flux:sidebar.nav>


            <flux:sidebar.spacer />


            {{-- Secondary Navigation --}}
            <flux:sidebar.nav
                class="
                    border-t
                    border-zinc-200/70
                    pt-3

                    dark:border-white/10
                "
            >

                {{-- Profile --}}
                <flux:sidebar.item
                    icon="user"

                    href="{{ route('account.profile') }}"

                    :current="request()->routeIs('account.profile')"

                    wire:navigate
                >
                    {{ __('core::sidebar.profile') }}
                </flux:sidebar.item>


                {{-- Settings --}}
                <flux:sidebar.item
                    icon="cog-6-tooth"

                    href="{{ route('account.settings') }}"

                    :current="request()->routeIs('account.settings')"

                    wire:navigate
                >
                    {{ __('core::sidebar.settings') }}
                </flux:sidebar.item>


                {{-- Help --}}
                <flux:sidebar.item
                    icon="question-mark-circle"

                    href="{{ route('help.index') }}"

                    :current="request()->routeIs('help.*')"

                    wire:navigate
                >
                    {{ __('core::sidebar.help') }}
                </flux:sidebar.item>

            </flux:sidebar.nav>

        </flux:sidebar>

    @endauth

</div>
