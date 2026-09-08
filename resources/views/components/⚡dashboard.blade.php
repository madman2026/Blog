<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\Interaction\Actions\ModerateComment;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Actions\ReviewAuthorApplication;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\AuthorApplication;

new class extends Component
{
    use Toastable;

    public ?int $selectedId = null;
    public string $selectedKind = '';
    public string $decision = '';
    public string $notes = '';

    /** @return array<string, int> */
    #[Computed]
    public function metrics(): array
    {
        $user = auth()->user();

        return [
            'posts' => $user->posts()->count(),
            'comments' => $user->comments()->count(),
            'bookmarks' => $user->bookmarks()->count(),
            'notifications' => $user->unreadNotifications()->count(),
        ];
    }

    /** @return Collection<int, Post> */
    #[Computed]
    public function pendingPosts(): Collection
    {
        if (! auth()->user()->can(UserPermission::PostsReview->value)) {
            return new Collection;
        }

        return Post::query()->where('status', PostStatus::PendingReview)
            ->with(['translations', 'author'])->oldest('submitted_at')->limit(6)->get();
    }

    /** @return Collection<int, Comment> */
    #[Computed]
    public function pendingComments(): Collection
    {
        if (! auth()->user()->can(UserPermission::CommentsModerate->value)) {
            return new Collection;
        }

        return Comment::query()->where('status', CommentStatus::Pending)
            ->with(['user', 'commentable.translations'])->oldest()->limit(6)->get();
    }

    /** @return Collection<int, AuthorApplication> */
    #[Computed]
    public function pendingApplications(): Collection
    {
        if (! auth()->user()->can(UserPermission::AuthorApplicationsReview->value)) {
            return new Collection;
        }

        return AuthorApplication::query()->where('status', AuthorApplicationStatus::Pending)
            ->with('user.skills')->oldest()->limit(6)->get();
    }

    public function openDecision(string $kind, int $id, string $decision): void
    {
        $this->selectedKind = $kind;
        $this->selectedId = $id;
        $this->decision = $decision;
        $this->notes = '';
        $this->modal('moderation-decision')->show();
    }

    public function saveDecision(ModerateComment $moderateComment, ReviewAuthorApplication $reviewApplication): void
    {
        $this->validate([
            'selectedKind' => ['required', 'in:comment,application'],
            'selectedId' => ['required', 'integer'],
            'decision' => ['required', 'string'],
            'notes' => [$this->decision === 'rejected' ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        if ($this->selectedKind === 'comment') {
            $comment = Comment::query()->findOrFail($this->selectedId);
            Gate::authorize('moderate', $comment);
            $moderateComment->handle($comment, auth()->user(), CommentStatus::from($this->decision), $this->notes ?: null);
            unset($this->pendingComments);
        } else {
            $application = AuthorApplication::query()->findOrFail($this->selectedId);
            Gate::authorize('review', $application);
            $reviewApplication->handle($application, auth()->user(), AuthorApplicationStatus::from($this->decision), $this->notes ?: null);
            unset($this->pendingApplications);
        }

        $this->modal('moderation-decision')->close();
        $this->reset('selectedKind', 'selectedId', 'decision', 'notes');
        $this->success(__('Moderation decision saved.'));
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Workspace') }}</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight">{{ __('Welcome back, :name', ['name' => auth()->user()->username]) }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Your publishing activity and items needing attention.') }}</p>
        </div>
        @can('create', Post::class)
            <flux:button href="{{ route('blog.manage.posts.create') }}" variant="primary" icon="plus" wire:navigate>{{ __('Write a post') }}</flux:button>
        @endcan
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['key' => 'posts', 'label' => __('Your posts'), 'icon' => 'document-text'],
            ['key' => 'comments', 'label' => __('Comments'), 'icon' => 'chat-bubble-left-right'],
            ['key' => 'bookmarks', 'label' => __('Saved'), 'icon' => 'bookmark'],
            ['key' => 'notifications', 'label' => __('Unread'), 'icon' => 'bell'],
        ] as $metric)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</span>
                    <x-dynamic-component :component="'heroicon-o-'.$metric['icon']" class="size-5 text-cyan-600" />
                </div>
                <div class="mt-3 text-3xl font-black">{{ number_format($this->metrics[$metric['key']]) }}</div>
            </div>
        @endforeach
    </div>

    @if ($this->pendingPosts->isNotEmpty() || $this->pendingComments->isNotEmpty() || $this->pendingApplications->isNotEmpty())
        <div class="mt-10 grid gap-6 lg:grid-cols-2">
            @if ($this->pendingPosts->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h2 class="font-black">{{ __('Posts awaiting review') }}</h2>
                        <flux:link href="{{ route('blog.manage.posts.index', ['status' => 'pending-review']) }}" wire:navigate>{{ __('Open studio') }}</flux:link>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100 dark:divide-white/10">
                        @foreach ($this->pendingPosts as $post)
                            <a href="{{ route('blog.manage.posts.edit', $post) }}" wire:navigate class="flex items-center justify-between gap-3 py-3 text-sm">
                                <span class="min-w-0 truncate font-bold">{{ $post->translation()?->title }}</span>
                                <span class="shrink-0 text-xs text-slate-500">{{ '@'.$post->author->username }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($this->pendingComments->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <h2 class="font-black">{{ __('Comments awaiting moderation') }}</h2>
                    <div class="mt-4 divide-y divide-slate-100 dark:divide-white/10">
                        @foreach ($this->pendingComments as $comment)
                            <div class="py-4">
                                <div class="flex items-center justify-between gap-3 text-xs text-slate-500"><span>{{ '@'.$comment->user->username }}</span><time>{{ $comment->created_at->diffForHumans() }}</time></div>
                                <p class="mt-2 line-clamp-2 text-sm leading-6">{{ $comment->body }}</p>
                                <div class="mt-3 flex gap-2">
                                    <flux:button wire:click="openDecision('comment', {{ $comment->getKey() }}, 'approved')" size="sm" variant="primary">{{ __('Approve') }}</flux:button>
                                    <flux:button wire:click="openDecision('comment', {{ $comment->getKey() }}, 'rejected')" size="sm" variant="danger">{{ __('Reject') }}</flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($this->pendingApplications->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <h2 class="font-black">{{ __('Author applications') }}</h2>
                    <div class="mt-4 divide-y divide-slate-100 dark:divide-white/10">
                        @foreach ($this->pendingApplications as $application)
                            <div class="py-4">
                                <a href="{{ route('user.profile', $application->user) }}" wire:navigate class="font-bold">{{ '@'.$application->user->username }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $application->user->skills->pluck('name')->implode(' · ') }}</p>
                                <div class="mt-3 flex gap-2">
                                    <flux:button wire:click="openDecision('application', {{ $application->getKey() }}, 'approved')" size="sm" variant="primary">{{ __('Approve') }}</flux:button>
                                    <flux:button wire:click="openDecision('application', {{ $application->getKey() }}, 'rejected')" size="sm" variant="danger">{{ __('Reject') }}</flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @else
        <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500 dark:border-white/15">{{ __('Nothing needs your attention right now.') }}</div>
    @endif

    <flux:modal name="moderation-decision" class="md:w-[30rem]">
        <form wire:submit="saveDecision" class="space-y-5">
            <div><flux:heading size="lg">{{ __('Moderation decision') }}</flux:heading><flux:text class="mt-2">{{ __('Provide a useful reason when rejecting.') }}</flux:text></div>
            <flux:textarea wire:model="notes" rows="5" label="{{ __('Notes') }}" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Confirm') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
