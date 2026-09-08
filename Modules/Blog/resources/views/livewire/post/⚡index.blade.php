<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toastable;
use Modules\Blog\Actions\ReviewPost;
use Modules\Blog\Actions\SubmitPostForReview;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Models\Post;
use Modules\User\Enums\UserPermission;

new class extends Component
{
    use Toastable;
    use WithPagination;

    #[Url]
    public string $status = '';

    public ?int $reviewingPostId = null;

    public string $reviewDecision = '';

    public string $reviewNotes = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Post::class);
    }

    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        return Post::query()
            ->when(
                ! auth()->user()->can(UserPermission::PostsUpdateAny->value),
                fn ($query) => $query->whereBelongsTo(auth()->user(), 'author'),
            )
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->with(['translations', 'author.media', 'media'])
            ->latest()
            ->paginate(15);
    }

    public function submit(int $postId, SubmitPostForReview $submit): void
    {
        $post = Post::query()->findOrFail($postId);
        Gate::authorize('submit', $post);
        $submit->handle($post);
        unset($this->posts);
        $this->success(__('Post submitted for editorial review.'));
    }

    public function openReview(int $postId, string $decision): void
    {
        $post = Post::query()->findOrFail($postId);
        Gate::authorize('review', $post);

        $this->reviewingPostId = $postId;
        $this->reviewDecision = ReviewDecision::from($decision)->value;
        $this->reviewNotes = '';
        $this->modal('review-post')->show();
    }

    public function review(ReviewPost $reviewPost): void
    {
        $validated = $this->validate([
            'reviewingPostId' => ['required', 'integer', 'exists:posts,id'],
            'reviewDecision' => ['required', 'string'],
            'reviewNotes' => [
                $this->reviewDecision === ReviewDecision::Publish->value ? 'nullable' : 'required',
                'string',
                'max:3000',
            ],
        ]);

        $post = Post::query()->findOrFail($validated['reviewingPostId']);
        Gate::authorize('review', $post);
        $reviewPost->handle(
            $post,
            auth()->user(),
            ReviewDecision::from($validated['reviewDecision']),
            filled($validated['reviewNotes']) ? $validated['reviewNotes'] : null,
        );

        $this->modal('review-post')->close();
        $this->reset('reviewingPostId', 'reviewDecision', 'reviewNotes');
        unset($this->posts);
        $this->success(__('Editorial decision saved.'));
    }

    public function delete(int $postId): void
    {
        $post = Post::query()->findOrFail($postId);
        Gate::authorize('delete', $post);
        $post->delete();
        unset($this->posts);
        $this->success(__('Draft deleted.'));
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Publishing workspace') }}</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight">{{ __('Writer studio') }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Draft, review and publish from one focused workflow.') }}</p>
        </div>
        @can('create', Post::class)
            <flux:button href="{{ route('blog.manage.posts.create') }}" variant="primary" icon="plus" wire:navigate>{{ __('New post') }}</flux:button>
        @endcan
    </div>

    <div class="mt-8 flex gap-2 overflow-x-auto pb-1">
        <flux:button wire:click="$set('status', '')" size="sm" :variant="$status === '' ? 'primary' : 'ghost'">{{ __('All') }}</flux:button>
        @foreach (PostStatus::cases() as $option)
            <flux:button wire:click="$set('status', '{{ $option->value }}')" size="sm" :variant="$status === $option->value ? 'primary' : 'ghost'">{{ __($option->value) }}</flux:button>
        @endforeach
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        <div class="divide-y divide-slate-100 dark:divide-white/10">
            @forelse ($this->posts as $post)
                @php($translation = $post->translation())
                <article wire:key="managed-post-{{ $post->getKey() }}" class="grid gap-4 p-4 sm:grid-cols-[96px_minmax(0,1fr)_auto] sm:items-center sm:p-5">
                    <div class="aspect-video overflow-hidden rounded-xl bg-gradient-to-br from-cyan-500/30 to-indigo-600/50 sm:aspect-square">
                        @if ($post->hasMedia('featured_image'))
                            <img src="{{ $post->getFirstMediaUrl('featured_image', 'thumbnail') }}" alt="" class="size-full object-cover" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ __($post->status->value) }}</span>
                            <span class="text-slate-500">{{ __($post->type->value) }}</span>
                            @can(UserPermission::PostsUpdateAny->value)
                                <span class="text-slate-500">{{ '@'.$post->author->username }}</span>
                            @endcan
                        </div>
                        <h2 class="mt-2 truncate text-base font-black sm:text-lg">{{ $translation?->title ?? __('Untitled draft') }}</h2>
                        @if ($post->review_notes)
                            <p class="mt-1 line-clamp-1 text-xs text-amber-700 dark:text-amber-300">{{ $post->review_notes }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-500">{{ __('Updated') }} {{ $post->updated_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        @can('update', $post)
                            <flux:button href="{{ route('blog.manage.posts.edit', $post) }}" size="sm" variant="ghost" icon="pencil-square" wire:navigate>{{ __('Edit') }}</flux:button>
                        @endcan
                        @can('submit', $post)
                            <flux:button wire:click="submit({{ $post->getKey() }})" wire:confirm="{{ __('Submit this post for review?') }}" size="sm" variant="primary">{{ __('Submit') }}</flux:button>
                        @endcan
                        @can('review', $post)
                            <flux:button wire:click="openReview({{ $post->getKey() }}, 'publish')" size="sm" variant="primary">{{ __('Publish') }}</flux:button>
                            <flux:button wire:click="openReview({{ $post->getKey() }}, 'request-changes')" size="sm" variant="ghost">{{ __('Changes') }}</flux:button>
                            <flux:button wire:click="openReview({{ $post->getKey() }}, 'reject')" size="sm" variant="danger">{{ __('Reject') }}</flux:button>
                        @endcan
                        @can('delete', $post)
                            <flux:button wire:click="delete({{ $post->getKey() }})" wire:confirm="{{ __('Delete this draft?') }}" size="sm" variant="ghost" icon="trash" />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="p-14 text-center text-sm text-slate-500">{{ __('No posts in this state.') }}</div>
            @endforelse
        </div>
    </div>

    <div class="mt-8">{{ $this->posts->links() }}</div>

    <flux:modal name="review-post" class="md:w-[32rem]">
        <form wire:submit="review" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Editorial review') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Add clear feedback whenever publication is not approved.') }}</flux:text>
            </div>
            <flux:textarea wire:model="reviewNotes" rows="5" label="{{ __('Review notes') }}" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save decision') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
