<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\Interaction\Actions\RecordView;
use Modules\Interaction\Actions\SubmitComment;
use Modules\Interaction\Actions\ToggleBookmark;
use Modules\Interaction\Actions\ToggleLike;
use Modules\Interaction\Models\Comment;

new class extends Component
{
    use Toastable;

    public Post $post;

    public string $comment = '';

    public function mount(PostTranslation $postTranslation, RecordView $recordView): void
    {
        $this->post = $postTranslation->post()
            ->with(['translations', 'author.skills', 'author.media', 'media', 'categories.translations', 'tags.translations'])
            ->firstOrFail();

        Gate::authorize('view', $this->post);
        $recordView->handle(request(), $this->post);
    }

    public function hydrate(): void
    {
        $this->post->loadMissing([
            'translations',
            'author.skills',
            'author.media',
            'media',
            'categories.translations',
            'tags.translations',
        ]);
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->post->comments()
            ->approved()
            ->whereNull('parent_id')
            ->with(['user.media', 'replies' => fn ($query) => $query->approved()->with('user.media')])
            ->oldest()
            ->get();
    }

    #[Computed]
    public function likesCount(): int
    {
        return $this->post->likes()->count();
    }

    #[Computed]
    public function isLiked(): bool
    {
        return auth()->check() && $this->post->likes()->whereBelongsTo(auth()->user())->exists();
    }

    #[Computed]
    public function isBookmarked(): bool
    {
        return auth()->check() && $this->post->bookmarks()->whereBelongsTo(auth()->user())->exists();
    }

    public function toggleLike(ToggleLike $toggleLike): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('auth.login', navigate: true);

            return;
        }

        $toggleLike->handle(auth()->user(), $this->post);
        unset($this->isLiked, $this->likesCount);
    }

    public function toggleBookmark(ToggleBookmark $toggleBookmark): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('auth.login', navigate: true);

            return;
        }

        $toggleBookmark->handle(auth()->user(), $this->post);
        unset($this->isBookmarked);
    }

    public function submitComment(SubmitComment $submitComment): void
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate([
            'comment' => ['required', 'string', 'min:2', 'max:3000'],
        ]);

        $submitComment->handle(auth()->user(), $this->post, $validated['comment']);
        $this->reset('comment');
        $this->success(__('Your comment was submitted for review.'));
    }
};
?>

@php($translation = $post->translation())

<div class="pb-20">
    <article>
        <header class="mx-auto max-w-5xl px-4 pb-10 pt-12 text-center sm:px-6 sm:pt-16 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-2 text-xs font-bold uppercase tracking-wider text-cyan-700 dark:text-cyan-300">
                <span>{{ __($post->type->value) }}</span>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <time class="text-slate-500 dark:text-slate-400">{{ $post->published_at?->translatedFormat('j F Y') }}</time>
            </div>
            <h1 class="mx-auto mt-5 max-w-4xl text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-6xl dark:text-white">{{ $translation?->title }}</h1>
            @if ($translation?->summary)
                <p class="mx-auto mt-6 max-w-3xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-300">{{ $translation->summary }}</p>
            @endif
            <div class="mt-7 flex items-center justify-center gap-3 text-sm">
                <a href="{{ route('user.profile', $post->author) }}" wire:navigate class="flex items-center gap-2 font-bold hover:text-cyan-600">
                    @if ($post->author->hasMedia('avatar'))
                        <img src="{{ $post->author->getFirstMediaUrl('avatar', 'avatar_small') }}" alt="" class="size-9 rounded-full object-cover" />
                    @else
                        <span class="grid size-9 place-items-center rounded-full bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ mb_strtoupper(mb_substr($post->author->username, 0, 1)) }}</span>
                    @endif
                    {{ '@'.$post->author->username }}
                </a>
                <span class="text-slate-400">·</span>
                <span class="text-slate-500">{{ $post->views()->count() }} {{ __('views') }}</span>
            </div>
        </header>

        @if ($post->hasMedia('featured_image'))
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <img
                    src="{{ $post->getFirstMediaUrl('featured_image', 'hero') }}"
                    srcset="{{ $post->getFirstMedia('featured_image')?->getSrcset('hero') }}"
                    alt="{{ $post->getFirstMedia('featured_image')?->getCustomProperty('alt', $translation?->title) }}"
                    class="aspect-[16/8] w-full rounded-3xl object-cover shadow-2xl shadow-slate-950/10"
                />
            </div>
        @endif

        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_220px] lg:px-8">
            <div class="min-w-0">
                <div class="article-content prose prose-slate max-w-none text-[1.05rem] leading-8 dark:prose-invert">
                    {!! $translation?->body !!}
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-2 border-y border-slate-200 py-5 dark:border-white/10">
                    <flux:button wire:click="toggleLike" :variant="$this->isLiked ? 'primary' : 'ghost'" icon="heart">
                        {{ $this->likesCount }} {{ __('likes') }}
                    </flux:button>
                    <flux:button wire:click="toggleBookmark" :variant="$this->isBookmarked ? 'primary' : 'ghost'" icon="bookmark">
                        {{ $this->isBookmarked ? __('Saved') : __('Save') }}
                    </flux:button>
                </div>

                <section class="mt-12" aria-labelledby="comments-heading">
                    <h2 id="comments-heading" class="text-2xl font-black">{{ __('Discussion') }} <span class="text-slate-400">{{ $this->comments->count() }}</span></h2>

                    @auth
                        <form wire:submit="submitComment" class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900">
                            <flux:textarea wire:model="comment" rows="4" label="{{ __('Add a thoughtful comment') }}" placeholder="{{ __('Your comment will appear after moderation.') }}" />
                            <div class="mt-3 flex justify-end">
                                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Submit comment') }}</flux:button>
                            </div>
                        </form>
                    @else
                        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 p-5 text-sm text-slate-600 dark:border-white/15 dark:text-slate-300">
                            <flux:link href="{{ route('auth.login') }}" wire:navigate>{{ __('Log in') }}</flux:link> {{ __('to join the discussion.') }}
                        </div>
                    @endauth

                    <div class="mt-7 space-y-4">
                        @forelse ($this->comments as $item)
                            <div wire:key="comment-{{ $item->getKey() }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
                                <div class="flex items-center justify-between gap-3">
                                    <a href="{{ route('user.profile', $item->user) }}" wire:navigate class="font-bold">{{ '@'.$item->user->username }}</a>
                                    <time class="text-xs text-slate-500">{{ $item->created_at->diffForHumans() }}</time>
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-200">{{ $item->body }}</p>
                                @foreach ($item->replies as $reply)
                                    <div class="mt-4 border-s-2 border-cyan-500/40 ps-4">
                                        <div class="text-xs font-bold">{{ '@'.$reply->user->username }}</div>
                                        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $reply->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-slate-500">{{ __('Start the conversation.') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="order-first lg:order-last">
                <div class="sticky top-24 space-y-6">
                    @if ($post->categories->isNotEmpty())
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('Topics') }}</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($post->categories as $category)
                                    @php($categoryTranslation = $category->translations->firstWhere('locale', app()->getLocale()) ?? $category->translations->first())
                                    <a href="{{ route('blog.posts.index', ['category' => $category->getKey()]) }}" wire:navigate class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold hover:bg-cyan-500/10 hover:text-cyan-700 dark:bg-slate-800">{{ $categoryTranslation?->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($post->tags->isNotEmpty())
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('Tags') }}</h2>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                                @foreach ($post->tags as $tag)
                                    @php($tagTranslation = $tag->translations->firstWhere('locale', app()->getLocale()) ?? $tag->translations->first())
                                    <span>#{{ $tagTranslation?->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </article>
</div>
