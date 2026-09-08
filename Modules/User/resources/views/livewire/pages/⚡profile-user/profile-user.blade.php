<div class="pb-20">
    <section class="border-b border-slate-200/70 bg-gradient-to-b from-cyan-500/5 to-transparent dark:border-white/10">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex flex-col gap-7 sm:flex-row sm:items-start">
                @if ($user->hasMedia('avatar'))
                    <img src="{{ $user->getFirstMediaUrl('avatar', 'avatar_large') }}" alt="{{ $user->username }}" class="size-28 rounded-3xl object-cover shadow-xl sm:size-36" />
                @else
                    <div class="grid size-28 shrink-0 place-items-center rounded-3xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-4xl font-black text-white shadow-xl sm:size-36">{{ mb_strtoupper(mb_substr($user->username, 0, 1)) }}</div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-black tracking-tight sm:text-4xl">{{ '@'.$user->username }}</h1>
                        @if ($user->hasAnyRole(['author', 'admin', 'super-user']))
                            <span class="rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-bold text-cyan-700 dark:text-cyan-300">{{ __('Verified author') }}</span>
                        @endif
                    </div>
                    @if ($user->bio)<p class="mt-4 max-w-3xl text-base leading-7 text-slate-700 dark:text-slate-200">{{ $user->bio }}</p>@endif
                    @if ($user->about)<p class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $user->about }}</p>@endif
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($user->skills as $skill)
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold dark:border-white/10 dark:bg-slate-900">{{ $skill->name }}</span>
                        @endforeach
                    </div>
                    <div class="mt-5 flex flex-wrap gap-4 text-sm">
                        @foreach (($user->social_links ?? []) as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-cyan-700 hover:underline dark:text-cyan-300">{{ ucfirst($network) }}</a>
                        @endforeach
                        @auth
                            @if (auth()->user()->is($user))
                                <flux:link href="{{ route('user.settings') }}" wire:navigate>{{ __('Edit profile') }}</flux:link>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Published work') }}</p>
                <h2 class="mt-1 text-2xl font-black">{{ __('Latest publications') }}</h2>
            </div>
            <span class="text-sm text-slate-500">{{ $this->posts->total() }}</span>
        </div>

        <div class="mt-7 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->posts as $post)
                @php($translation = $post->translation())
                <article wire:key="author-post-{{ $post->getKey() }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <a href="{{ route('blog.posts.show', $translation) }}" wire:navigate class="block aspect-video bg-gradient-to-br from-cyan-500/30 to-indigo-600/50">
                        @if ($post->hasMedia('featured_image'))<img src="{{ $post->getFirstMediaUrl('featured_image', 'card') }}" alt="{{ $translation?->title }}" class="size-full object-cover" />@endif
                    </a>
                    <div class="p-5">
                        <p class="text-xs font-bold uppercase text-cyan-700 dark:text-cyan-300">{{ __($post->type->value) }}</p>
                        <h3 class="mt-2 text-lg font-black leading-7"><a href="{{ route('blog.posts.show', $translation) }}" wire:navigate>{{ $translation?->title }}</a></h3>
                        <p class="mt-3 text-xs text-slate-500">{{ $post->views_count }} {{ __('views') }} · {{ $post->likes_count }} {{ __('likes') }}</p>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-12 text-center text-sm text-slate-500 dark:border-white/15">{{ __('No publications yet.') }}</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $this->posts->links() }}</div>
    </section>
</div>
