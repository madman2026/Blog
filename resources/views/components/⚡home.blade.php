<?php

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;

new class extends Component
{
    /** @return Collection<int, Post> */
    #[Computed]
    public function posts(): Collection
    {
        return Post::query()
            ->published()
            ->with(['translations', 'author.media', 'media'])
            ->withCount(['likes', 'views'])
            ->latest('published_at')
            ->limit(7)
            ->get();
    }

    /** @return Collection<int, Category> */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->with('translations')
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderByDesc('posts_count')
            ->limit(8)
            ->get();
    }
};
?>

<div class="pb-20">
    <section class="relative isolate overflow-hidden border-b border-slate-200/70 dark:border-white/10">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_80%_20%,rgba(34,211,238,.18),transparent_34%),radial-gradient(circle_at_15%_70%,rgba(99,102,241,.16),transparent_32%)]"></div>
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-[1.1fr_.9fr] lg:items-center lg:px-8">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-xs font-bold text-cyan-700 dark:text-cyan-300">
                    <span class="size-1.5 animate-pulse rounded-full bg-cyan-500"></span>
                    {{ __('Technology, explained with depth') }}
                </span>
                <h1 class="mt-6 max-w-3xl text-4xl font-black leading-tight tracking-tight text-slate-950 sm:text-6xl dark:text-white">
                    {{ __('Ideas that help you build what comes next.') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-300">
                    {{ __('High-quality articles and current technology news for students, engineers and curious builders.') }}
                </p>
                <form action="{{ route('blog.posts.index') }}" method="GET" class="mt-8 flex max-w-xl gap-2">
                    <flux:input name="search" icon="magnifying-glass" placeholder="{{ __('Search articles and news') }}" class="flex-1" />
                    <flux:button type="submit" variant="primary">{{ __('Search') }}</flux:button>
                </form>
            </div>

            @if ($featured = $this->posts->first())
                @php($translation = $featured->translation())
                <a href="{{ route('blog.posts.show', $translation) }}" wire:navigate class="group relative block overflow-hidden rounded-3xl border border-white/60 bg-slate-900 shadow-2xl shadow-indigo-950/20 dark:border-white/10">
                    <div class="aspect-[4/3]">
                        @if ($featured->hasMedia('featured_image'))
                            <img src="{{ $featured->getFirstMediaUrl('featured_image', 'hero') }}" alt="{{ $translation?->title }}" class="size-full object-cover transition duration-700 group-hover:scale-105" />
                        @else
                            <div class="size-full bg-gradient-to-br from-cyan-500 via-indigo-600 to-violet-700"></div>
                        @endif
                    </div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/75 to-transparent p-6 pt-24 text-white">
                        <span class="text-xs font-bold uppercase tracking-[.16em] text-cyan-300">{{ $featured->type->value }}</span>
                        <h2 class="mt-2 text-2xl font-black leading-snug">{{ $translation?->title }}</h2>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-300">{{ $translation?->summary }}</p>
                    </div>
                </a>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Fresh perspectives') }}</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">{{ __('Latest publications') }}</h2>
            </div>
            <flux:link href="{{ route('blog.posts.index') }}" wire:navigate>{{ __('View all') }} &larr;</flux:link>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->posts->skip(1) as $post)
                @php($translation = $post->translation())
                <article wire:key="post-{{ $post->getKey() }}" class="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/5 dark:border-white/10 dark:bg-slate-900">
                    <a href="{{ route('blog.posts.show', $translation) }}" wire:navigate class="block aspect-video overflow-hidden bg-gradient-to-br from-cyan-500/30 to-indigo-600/40">
                        @if ($post->hasMedia('featured_image'))
                            <img src="{{ $post->getFirstMediaUrl('featured_image', 'card') }}" alt="{{ $translation?->title }}" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105" />
                        @endif
                    </a>
                    <div class="p-5">
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span class="font-bold uppercase text-cyan-700 dark:text-cyan-300">{{ $post->type->value }}</span>
                            <time>{{ $post->published_at?->diffForHumans() }}</time>
                        </div>
                        <h3 class="mt-3 text-lg font-extrabold leading-7 text-slate-950 dark:text-white">
                            <a href="{{ route('blog.posts.show', $translation) }}" wire:navigate>{{ $translation?->title }}</a>
                        </h3>
                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $translation?->summary }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500 dark:border-white/10 dark:text-slate-400">
                            <a href="{{ route('user.profile', $post->author) }}" wire:navigate class="font-semibold hover:text-cyan-600">{{ '@'.$post->author->username }}</a>
                            <span>{{ number_format($post->views_count) }} {{ __('views') }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-12 text-center text-slate-500 dark:border-white/15">
                    {{ __('No published content yet.') }}
                </div>
            @endforelse
        </div>
    </section>

    @if ($this->categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-slate-950 p-6 text-white sm:p-9 dark:bg-slate-900">
                <h2 class="text-xl font-black">{{ __('Explore topics') }}</h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($this->categories as $category)
                        @php($translation = $category->translations->firstWhere('locale', app()->getLocale()) ?? $category->translations->first())
                        <a href="{{ route('blog.posts.index', ['category' => $category->getKey()]) }}" wire:navigate class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm transition hover:border-cyan-400/60 hover:bg-cyan-400/10">
                            {{ $translation?->name }} <span class="ms-1 text-slate-400">{{ $category->posts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
