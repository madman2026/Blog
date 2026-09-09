<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

new class extends Component
{
    public function mount(): void
    {
        Gate::authorize(UserPermission::UsersViewAny->value);
    }

    /** @return array<string, int> */
    #[Computed]
    public function metrics(): array
    {
        return [
            'users' => User::query()->count(),
            'posts' => Post::query()->count(),
            'published' => Post::query()->where('status', PostStatus::Published)->count(),
            'postQueue' => Post::query()->where('status', PostStatus::PendingReview)->count(),
            'commentQueue' => Comment::query()->where('status', CommentStatus::Pending)->count(),
            'applicationQueue' => AuthorApplication::query()->where('status', AuthorApplicationStatus::Pending)->count(),
        ];
    }

    /** @return Collection<int, Post> */
    #[Computed]
    public function pendingPosts(): Collection
    {
        return Post::query()
            ->where('status', PostStatus::PendingReview)
            ->with(['translations', 'author'])
            ->oldest('submitted_at')
            ->limit(7)
            ->get();
    }
};
?>

<x-admin.shell :title="__('System overview')" :description="__('Monitor publishing, moderation and membership from one place.')">
    <x-slot:actions>
        <flux:button href="{{ route('blog.manage.posts.index', ['status' => 'pending-review']) }}" variant="primary" icon="document-magnifying-glass" wire:navigate>{{ __('Review posts') }}</flux:button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['key' => 'users', 'label' => __('Users'), 'icon' => 'users', 'route' => 'admin.users'],
            ['key' => 'posts', 'label' => __('All posts'), 'icon' => 'document-text', 'route' => 'blog.manage.posts.index'],
            ['key' => 'published', 'label' => __('Published'), 'icon' => 'check-badge', 'route' => 'blog.manage.posts.index'],
            ['key' => 'postQueue', 'label' => __('Posts awaiting review'), 'icon' => 'clock', 'route' => 'blog.manage.posts.index'],
            ['key' => 'commentQueue', 'label' => __('Comments awaiting moderation'), 'icon' => 'chat-bubble-left-right', 'route' => 'admin.comments'],
            ['key' => 'applicationQueue', 'label' => __('Author applications'), 'icon' => 'user-plus', 'route' => 'admin.applications'],
        ] as $metric)
            <a href="{{ route($metric['route']) }}" wire:navigate class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md dark:border-white/10 dark:bg-slate-950/60 dark:hover:border-indigo-500/50">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</span>
                    <x-dynamic-component :component="'heroicon-o-'.$metric['icon']" class="size-5 text-indigo-600 transition group-hover:scale-110 dark:text-indigo-400" />
                </div>
                <p class="mt-4 text-3xl font-black tabular-nums">{{ number_format($this->metrics[$metric['key']]) }}</p>
            </a>
        @endforeach
    </div>

    <section class="mt-8">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-black">{{ __('Oldest posts in review queue') }}</h2>
            <flux:link href="{{ route('blog.manage.posts.index', ['status' => 'pending-review']) }}" wire:navigate>{{ __('Open queue') }}</flux:link>
        </div>
        <div class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-slate-950/60">
            @forelse ($this->pendingPosts as $post)
                <a wire:key="review-post-{{ $post->getKey() }}" href="{{ route('blog.manage.posts.edit', $post) }}" wire:navigate class="flex items-center justify-between gap-4 p-4 transition hover:bg-slate-50 dark:hover:bg-white/5">
                    <div class="min-w-0"><p class="truncate font-black">{{ $post->translation()?->title ?? __('Untitled draft') }}</p><p class="mt-1 text-xs text-slate-500">{{ '@'.$post->author->username }}</p></div>
                    <time class="shrink-0 text-xs text-slate-500">{{ $post->submitted_at?->diffForHumans() }}</time>
                </a>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">{{ __('The editorial queue is clear.') }}</div>
            @endforelse
        </div>
    </section>
</x-admin.shell>
