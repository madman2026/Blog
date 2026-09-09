@props(['title', 'description' => null])

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-[2rem] border border-slate-200/80 bg-white/80 shadow-sm backdrop-blur dark:border-white/10 dark:bg-slate-900/75">
        <div class="grid min-h-[38rem] lg:grid-cols-[15rem_minmax(0,1fr)]">
            <aside class="border-b border-slate-200 p-4 lg:border-b-0 lg:border-e dark:border-white/10">
                <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-cyan-500 p-4 text-white shadow-lg shadow-indigo-500/15">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">{{ __('Control center') }}</p>
                    <p class="mt-1 text-lg font-black">{{ __('Admin panel') }}</p>
                </div>

                <nav class="mt-4 grid grid-cols-2 gap-1 sm:grid-cols-3 lg:grid-cols-1" aria-label="{{ __('Admin navigation') }}">
                    @foreach ([
                        ['route' => 'admin.dashboard', 'icon' => 'squares-2x2', 'label' => __('Overview'), 'can' => \Modules\User\Enums\UserPermission::UsersViewAny->value],
                        ['route' => 'blog.manage.posts.index', 'icon' => 'document-text', 'label' => __('All posts'), 'can' => \Modules\User\Enums\UserPermission::PostsViewAny->value],
                        ['route' => 'admin.comments', 'icon' => 'chat-bubble-left-right', 'label' => __('Comments'), 'can' => \Modules\User\Enums\UserPermission::CommentsViewAny->value],
                        ['route' => 'admin.applications', 'icon' => 'user-plus', 'label' => __('Applications'), 'can' => \Modules\User\Enums\UserPermission::AuthorApplicationsReview->value],
                        ['route' => 'admin.taxonomies', 'icon' => 'tag', 'label' => __('Taxonomies'), 'can' => \Modules\User\Enums\UserPermission::TaxonomiesManage->value],
                        ['route' => 'admin.users', 'icon' => 'users', 'label' => __('Users'), 'can' => \Modules\User\Enums\UserPermission::UsersViewAny->value],
                    ] as $item)
                        @can($item['can'])
                            <a href="{{ route($item['route']) }}" wire:navigate @class([
                                'flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold transition',
                                'bg-slate-950 text-white shadow-sm dark:bg-white dark:text-slate-950' => request()->routeIs($item['route']),
                                'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white' => ! request()->routeIs($item['route']),
                            ])>
                                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="size-4" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endcan
                    @endforeach
                </nav>
            </aside>

            <section class="min-w-0 p-5 sm:p-7 lg:p-9">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">{{ __('Administration') }}</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight">{{ $title }}</h1>
                        @if ($description)
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $description }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="shrink-0">{{ $actions }}</div>
                    @endisset
                </div>

                <div class="mt-8">{{ $slot }}</div>
            </section>
        </div>
    </div>
</div>
