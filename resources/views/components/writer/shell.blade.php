@props(['title', 'description' => null])

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white/80 shadow-sm backdrop-blur dark:border-white/10 dark:bg-slate-900/75">
        <div class="border-b border-slate-200 bg-gradient-to-r from-cyan-500/10 via-transparent to-indigo-500/10 px-5 py-6 sm:px-8 dark:border-white/10">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-cyan-600 dark:text-cyan-400">{{ __('Writer workspace') }}</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight">{{ $title }}</h1>
                    @if ($description)
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $description }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div>{{ $actions }}</div>
                @endisset
            </div>

            <nav class="mt-6 flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('Writer navigation') }}">
                <flux:button href="{{ route('writer.dashboard') }}" size="sm" :variant="request()->routeIs('writer.dashboard') ? 'primary' : 'ghost'" icon="squares-2x2" wire:navigate>{{ __('Overview') }}</flux:button>
                <flux:button href="{{ route('blog.manage.posts.index') }}" size="sm" :variant="request()->routeIs('blog.manage.posts.index') ? 'primary' : 'ghost'" icon="document-text" wire:navigate>{{ __('My posts') }}</flux:button>
                @can('create', \Modules\Blog\Models\Post::class)
                    <flux:button href="{{ route('blog.manage.posts.create') }}" size="sm" :variant="request()->routeIs('blog.manage.posts.create') ? 'primary' : 'ghost'" icon="plus" wire:navigate>{{ __('New post') }}</flux:button>
                @endcan
            </nav>
        </div>

        <section class="p-5 sm:p-8">{{ $slot }}</section>
    </div>
</div>
