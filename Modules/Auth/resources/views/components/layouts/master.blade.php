<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full scroll-smooth"
    dir='rtl'
>

<head>

    {{-- Meta --}}
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    {{-- SEO --}}
    <title>
        {{ $title ?? __('Authentication') }} · {{ config('app.name') }}
    </title>

    <meta
        name="description"
        content="{{ $description ?? config('app.name') }}"
    >

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    {{-- Theme --}}
    <meta
        name="theme-color"
        content="#ffffff"
    >

    {{-- Favicon --}}
    <link
        rel="icon"
        href="{{ Vite::asset('resources/images/favicon.ico') }}"
    >
    <style>
        @font-face {
            font-family: "vasir";
            src: url({{ asset("fonts/vasir.woff")}}) format("woff");
            font-weight: normal;
            font-style: normal;
        }
    </style>
    @livewireStyles()
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
    @fluxAppearance
    @stack('styles')

</head>

<body
    class="
        min-h-screen
        bg-gradient-to-br
        from-zinc-50
        via-white
        to-indigo-50/50
        text-zinc-900
        antialiased
        font-vasir
        selection:bg-indigo-600
        selection:text-white
    "
>
    <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/80 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80">

        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">

            @auth
            <a
                href="{{ route('user.profile' , auth('web')->id()) }}"
                wire:navigate
                class="flex items-center gap-3"
            >

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                    <x-heroicon-o-command-line class="h-6 w-6"/>
                </div>

                <div>

                    <p class="font-semibold tracking-tight">
                        {{ config('app.name') }}
                    </p>

                    <p class="text-xs text-zinc-500">
                        v{{ config('app.version') }}
                    </p>

                </div>

            </a>                
            @endauth


            {{-- Navigation --}}
            <nav class="hidden items-center gap-1 lg:flex">

                @auth
                    <flux:button
                        variant="ghost"
                        href="{{ route('user.profile' , auth('web')->id()) }}"
                        wire:navigate
                    >

                        داشبورد
                    </flux:button>
                @endauth

                <flux:button
                    variant="ghost"
                    href="#"
                >
                    پروژه‌ها
                </flux:button>

                <flux:button
                    variant="ghost"
                    href="#"
                >
                    کاربران
                </flux:button>

                <flux:button
                    variant="ghost"
                    href="#"
                >
                    تنظیمات
                </flux:button>

            </nav>

            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun" />
                <flux:radio value="dark" icon="moon" />
                <flux:radio value="system" icon="computer-desktop" />
            </flux:radio.group>

        </div>

    </header>
    
    <main
        class="flex min-h-screen items-center justify-center px-6 py-12"
    >

        {{ $slot }}

    </main>
    @livewireScripts()
    <x-toaster-hub />
    
    @stack('scripts')
    @fluxScripts

</body>

</html>