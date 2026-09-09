<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toastable;
use Modules\Interaction\Actions\ModerateComment;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Enums\UserPermission;

new class extends Component
{
    use Toastable;
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url]
    public string $search = '';

    public ?int $selectedCommentId = null;
    public string $decision = '';
    public string $notes = '';

    public function mount(): void
    {
        Gate::authorize(UserPermission::CommentsViewAny->value);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function comments(): LengthAwarePaginator
    {
        return Comment::query()
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', fn ($query) => $query->where('body', 'like', '%'.$this->search.'%'))
            ->with(['user.media', 'commentable.translations', 'moderator'])
            ->latest()
            ->paginate(15);
    }

    public function openDecision(int $commentId, string $decision): void
    {
        $comment = Comment::query()->findOrFail($commentId);
        Gate::authorize('moderate', $comment);

        $this->selectedCommentId = $commentId;
        $this->decision = CommentStatus::from($decision)->value;
        $this->notes = '';
        $this->modal('comment-decision')->show();
    }

    public function saveDecision(ModerateComment $moderateComment): void
    {
        $validated = $this->validate([
            'selectedCommentId' => ['required', 'integer', 'exists:comments,id'],
            'decision' => ['required', 'in:approved,rejected'],
            'notes' => [$this->decision === CommentStatus::Rejected->value ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        $comment = Comment::query()->findOrFail($validated['selectedCommentId']);
        Gate::authorize('moderate', $comment);
        $moderateComment->handle($comment, auth()->user(), CommentStatus::from($validated['decision']), filled($validated['notes']) ? $validated['notes'] : null);

        $this->modal('comment-decision')->close();
        $this->reset('selectedCommentId', 'decision', 'notes');
        unset($this->comments);
        $this->success(__('Comment moderation saved.'));
    }
};
?>

<x-admin.shell :title="__('Comment moderation')" :description="__('Review community conversations with context and keep decisions auditable.')">
    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_13rem]">
        <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" placeholder="{{ __('Search comment text') }}" />
        <flux:select wire:model.live="status">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            @foreach (CommentStatus::cases() as $option)
                <flux:select.option value="{{ $option->value }}">{{ __($option->value) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="mt-5 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-slate-950/60">
        @forelse ($this->comments as $comment)
            <article wire:key="comment-{{ $comment->getKey() }}" class="p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="grid size-9 place-items-center overflow-hidden rounded-full bg-indigo-500/10 text-sm font-black text-indigo-600">
                            @if ($comment->user->hasMedia('avatar'))<img src="{{ $comment->user->getFirstMediaUrl('avatar', 'avatar_small') }}" alt="" class="size-full object-cover" />@else{{ mb_strtoupper(mb_substr($comment->user->username, 0, 1)) }}@endif
                        </div>
                        <div><a href="{{ route('user.profile', $comment->user) }}" wire:navigate class="text-sm font-black">{{ '@'.$comment->user->username }}</a><p class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</p></div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold dark:bg-slate-800">{{ __($comment->status->value) }}</span>
                </div>
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-200">{{ $comment->body }}</p>
                <p class="mt-3 text-xs text-slate-500">{{ __('On') }}: {{ $comment->commentable?->translation()?->title ?? __('Deleted content') }}</p>
                @if ($comment->moderation_notes)<p class="mt-2 rounded-xl bg-amber-500/10 px-3 py-2 text-xs text-amber-800 dark:text-amber-200">{{ $comment->moderation_notes }}</p>@endif
                @can('moderate', $comment)
                    <div class="mt-4 flex gap-2">
                        <flux:button wire:click="openDecision({{ $comment->getKey() }}, 'approved')" size="sm" variant="primary" icon="check">{{ __('Approve') }}</flux:button>
                        <flux:button wire:click="openDecision({{ $comment->getKey() }}, 'rejected')" size="sm" variant="danger" icon="x-mark">{{ __('Reject') }}</flux:button>
                    </div>
                @endcan
            </article>
        @empty
            <div class="p-12 text-center text-sm text-slate-500">{{ __('No comments match these filters.') }}</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $this->comments->links() }}</div>

    <flux:modal name="comment-decision" class="md:w-[30rem]">
        <form wire:submit="saveDecision" class="space-y-5">
            <div><flux:heading size="lg">{{ __('Moderation decision') }}</flux:heading><flux:text class="mt-2">{{ __('A rejection requires a clear internal note.') }}</flux:text></div>
            <flux:textarea wire:model="notes" rows="5" label="{{ __('Notes') }}" />
            <div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('Save decision') }}</flux:button></div>
        </form>
    </flux:modal>
</x-admin.shell>
