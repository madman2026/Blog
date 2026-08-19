<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), ['fa', 'ar', 'he', 'ur']) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta name="color-scheme" content="light dark">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        @font-face {
            font-family: "Vazir";
            src: url('{{ asset('fonts/vasir.woff') }}') format('woff');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }

        html {
            font-family: "Vazir", ui-sans-serif, system-ui, sans-serif;
        }
    </style>

    @livewireStyles
    @fluxAppearance()
</head>

<body
    class="
        min-h-dvh
        min-w-0
        overflow-x-hidden

        bg-zinc-50
        text-zinc-950

        antialiased

        selection:bg-indigo-600
        selection:text-white

        dark:bg-zinc-950
        dark:text-zinc-100

        transition-colors
        duration-200
        ease-out

        motion-reduce:transition-none
    "
>
{{-- Background --}}
<div
    aria-hidden="true"
    class="
            pointer-events-none
            fixed
            inset-0
            -z-10
            overflow-hidden
        "
>
    <div
        class="
                absolute
                inset-0

                bg-gradient-to-br
                from-zinc-50
                via-white
                to-indigo-50/60

                dark:from-zinc-950
                dark:via-zinc-950
                dark:to-indigo-950/20
            "
    ></div>

    <div
        class="
                absolute
                -top-48
                -right-48

                size-[32rem]

                rounded-full

                bg-indigo-400/10
                blur-3xl

                dark:bg-indigo-500/5
            "
    ></div>

    <div
        class="
                absolute
                -bottom-48
                -left-48

                size-[28rem]

                rounded-full

                bg-violet-400/5
                blur-3xl

                dark:bg-violet-500/5
            "
    ></div>
</div>


{{-- Accessibility --}}
<a
    href="#main-content"
    class="
            fixed
            start-4
            top-4
            z-[100]

            -translate-y-20
            opacity-0

            rounded-lg
            bg-zinc-950
            px-4
            py-2.5

            text-sm
            font-medium
            text-white

            shadow-lg

            transition-[transform,opacity]
            duration-200
            ease-out

            focus:translate-y-0
            focus:opacity-100

            dark:bg-white
            dark:text-zinc-950
        "
>
    رفتن به محتوای اصلی
</a>


{{-- Header --}}
<livewire:core::header />


{{-- Sidebar --}}
<livewire:core::sidebar />


{{-- Page content --}}
<main
    id="main-content"
    tabindex="-1"
    class="
            relative
            min-w-0
            w-full

            outline-none
        "
>
    {{ $slot }}
</main>


@livewireScripts
@fluxScripts
</body>
</html>
