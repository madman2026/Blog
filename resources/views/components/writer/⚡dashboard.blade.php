<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;

new class extends Component
{
    public function mount(): void
    {
        Gate::authorize('viewAny', Post::class);
    }

    /** @return array<string, int> */
    #[Computed]
    public function metrics(): array
    {
        $posts = Post::query()->whereBelongsTo(auth()->user(), 'author');

        return [
            'all' => (clone $posts)->count(),
            'draft' => (clone $posts)->where('status', PostStatus::Draft)->count(),
            'review' => (clone $posts)->where('status', PostStatus::PendingReview)->count(),
            'published' => (clone $posts)->where('status', PostStatus::Published)->count(),
            'views' => (clone $posts)->withCount('views')->get()->sum('views_count'),
            'likes' => (clone $posts)->withCount('likes')->get()->sum('likes_count'),
        ];
    }

    /** @return Collection<int, Post> */
    #[Computed]
    public function recentPosts(): Collection
    {
        return Post::query()
            ->whereBelongsTo(auth()->user(), 'author')
            ->with(['translations', 'media'])
            ->withCount(['views', 'likes', 'comments'])
            ->latest('updated_at')
            ->limit(6)
            ->get();
    }
};
?>

<x-writer.shell :title="__('Writer dashboard')" :description="__('Track your publishing pipeline and continue from the next useful action.')">
    <x-slot:actions>
        <flux:button href="{{ route('blog.manage.posts.create') }}" variant="primary" icon="plus" wire:navigate>{{ __('Write a post') }}</flux:button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['key' => 'all', 'label' => __('All posts'), 'icon' => 'document-text'],
            ['key' => 'draft', 'label' => __('Drafts'), 'icon' => 'pencil-square'],
            ['key' => 'review', 'label' => __('Awaiting review'), 'icon' => 'clock'],
            ['key' => 'published', 'label' => __('Published'), 'icon' => 'check-badge'],
            ['key' => 'views', 'label' => __('Total views'), 'icon' => 'eye'],
            ['key' => 'likes', 'label' => __('Total likes'), 'icon' => 'heart'],
        ] as $metric)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-slate-950/60">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</span>
                    <span class="grid size-9 place-items-center rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400"><x-dynamic-component :component="'heroicon-o-'.$metric['icon']" class="size-5" /></span>
                </div>
                <p class="mt-4 text-3xl font-black tabular-nums">{{ number_format($this->metrics[$metric['key']]) }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-8">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-black">{{ __('Recently updated') }}</h2>
            <flux:link href="{{ route('blog.manage.posts.index') }}" wire:navigate>{{ __('View all posts') }}</flux:link>
        </div>

        <div class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-slate-950/60">
            @forelse ($this->recentPosts as $post)
                <article wire:key="writer-post-{{ $post->getKey() }}" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold dark:bg-slate-800">{{ __($post->status->value) }}</span>
                            <h3 class="truncate font-black">{{ $post->translation()?->title ?? __('Untitled draft') }}</h3>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">{{ number_format($post->views_count) }} {{ __('views') }} · {{ number_format($post->likes_count) }} {{ __('likes') }} · {{ number_format($post->comments_count) }} {{ __('comments') }}</p>
                    </div>
                    <flux:button href="{{ route('blog.manage.posts.edit', $post) }}" size="sm" variant="ghost" icon="pencil-square" wire:navigate>{{ __('Continue') }}</flux:button>
                </article>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">{{ __('Your first story can start here.') }}</div>
            @endforelse
        </div>
    </section>
</x-writer.shell>
