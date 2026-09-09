<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toastable;
use Modules\User\Actions\ReviewAuthorApplication;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\AuthorApplication;

new class extends Component
{
    use Toastable;
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url]
    public string $search = '';

    public ?int $selectedApplicationId = null;
    public string $decision = '';
    public string $notes = '';

    public function mount(): void
    {
        Gate::authorize(UserPermission::AuthorApplicationsReview->value);
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
    public function applications(): LengthAwarePaginator
    {
        return AuthorApplication::query()
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', fn ($query) => $query->whereHas('user', fn ($query) => $query->where('username', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%')))
            ->with(['user.skills', 'user.media', 'reviewer'])
            ->latest()
            ->paginate(12);
    }

    public function openDecision(int $applicationId, string $decision): void
    {
        $application = AuthorApplication::query()->findOrFail($applicationId);
        Gate::authorize('review', $application);

        $this->selectedApplicationId = $applicationId;
        $this->decision = AuthorApplicationStatus::from($decision)->value;
        $this->notes = '';
        $this->modal('application-decision')->show();
    }

    public function saveDecision(ReviewAuthorApplication $reviewApplication): void
    {
        $validated = $this->validate([
            'selectedApplicationId' => ['required', 'integer', 'exists:author_applications,id'],
            'decision' => ['required', 'in:approved,rejected'],
            'notes' => [$this->decision === AuthorApplicationStatus::Rejected->value ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        $application = AuthorApplication::query()->findOrFail($validated['selectedApplicationId']);
        Gate::authorize('review', $application);
        $reviewApplication->handle($application, auth()->user(), AuthorApplicationStatus::from($validated['decision']), filled($validated['notes']) ? $validated['notes'] : null);

        $this->modal('application-decision')->close();
        $this->reset('selectedApplicationId', 'decision', 'notes');
        unset($this->applications);
        $this->success(__('Author application reviewed.'));
    }
};
?>

<x-admin.shell :title="__('Author applications')" :description="__('Review profile readiness, skills and identity before granting publishing access.')">
    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_13rem]">
        <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" placeholder="{{ __('Search username or email') }}" />
        <flux:select wire:model.live="status">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            @foreach (AuthorApplicationStatus::cases() as $option)<flux:select.option value="{{ $option->value }}">{{ __($option->value) }}</flux:select.option>@endforeach
        </flux:select>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        @forelse ($this->applications as $application)
            <article wire:key="application-{{ $application->getKey() }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-950/60">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-full bg-indigo-500/10 font-black text-indigo-600">
                            @if ($application->user->hasMedia('avatar'))<img src="{{ $application->user->getFirstMediaUrl('avatar', 'avatar_small') }}" alt="" class="size-full object-cover" />@else{{ mb_strtoupper(mb_substr($application->user->username, 0, 1)) }}@endif
                        </div>
                        <div class="min-w-0"><a href="{{ route('user.profile', $application->user) }}" wire:navigate class="truncate font-black">{{ '@'.$application->user->username }}</a><p class="truncate text-xs text-slate-500">{{ $application->user->email }}</p></div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold dark:bg-slate-800">{{ __($application->status->value) }}</span>
                </div>
                <p class="mt-4 line-clamp-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $application->user->bio ?: __('No short introduction provided.') }}</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @forelse ($application->user->skills as $skill)<span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-xs font-bold text-cyan-700 dark:text-cyan-300">{{ $skill->name }}</span>@empty<span class="text-xs text-slate-500">{{ __('No skills listed') }}</span>@endforelse
                </div>
                @if ($application->review_notes)<p class="mt-4 rounded-xl bg-amber-500/10 px-3 py-2 text-xs text-amber-800 dark:text-amber-200">{{ $application->review_notes }}</p>@endif
                @can('review', $application)
                    <div class="mt-5 flex gap-2"><flux:button wire:click="openDecision({{ $application->getKey() }}, 'approved')" size="sm" variant="primary" icon="check">{{ __('Approve') }}</flux:button><flux:button wire:click="openDecision({{ $application->getKey() }}, 'rejected')" size="sm" variant="danger" icon="x-mark">{{ __('Reject') }}</flux:button></div>
                @endcan
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-12 text-center text-sm text-slate-500 xl:col-span-2 dark:border-white/15">{{ __('No applications match these filters.') }}</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $this->applications->links() }}</div>

    <flux:modal name="application-decision" class="md:w-[30rem]">
        <form wire:submit="saveDecision" class="space-y-5">
            <div><flux:heading size="lg">{{ __('Application decision') }}</flux:heading><flux:text class="mt-2">{{ __('Explain the next step when rejecting an application.') }}</flux:text></div>
            <flux:textarea wire:model="notes" rows="5" label="{{ __('Notes') }}" />
            <div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('Save decision') }}</flux:button></div>
        </form>
    </flux:modal>
</x-admin.shell>
