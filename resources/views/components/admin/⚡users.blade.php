<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toastable;
use Modules\User\Actions\UpdateManagedUserRole;
use Modules\User\Actions\UpdateManagedUserStatus;
use Modules\User\Enums\UserPermission;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;

new class extends Component
{
    use Toastable;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize(UserPermission::UsersViewAny->value);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('username', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->with(['roles', 'media'])
            ->latest()
            ->paginate(15);
    }

    public function changeStatus(int $userId, string $status, UpdateManagedUserStatus $updateStatus): void
    {
        Gate::authorize(UserPermission::UsersSuspend->value);
        $user = User::query()->findOrFail($userId);
        $updateStatus->handle($user, auth()->user(), UserStatus::from($status));
        unset($this->users);
        $this->success(__('User status updated.'));
    }

    public function changeRole(int $userId, string $role, UpdateManagedUserRole $updateRole): void
    {
        Gate::authorize(UserPermission::RolesManage->value);
        $user = User::query()->findOrFail($userId);
        $updateRole->handle($user, auth()->user(), UserRole::from($role));
        unset($this->users);
        $this->success(__('User role updated.'));
    }
};
?>

<x-admin.shell :title="__('User management')" :description="__('Search accounts, control access and keep role changes intentionally restricted.')">
    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_13rem]">
        <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" placeholder="{{ __('Search username, email or phone') }}" />
        <flux:select wire:model.live="status">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            @foreach (UserStatus::cases() as $option)<flux:select.option value="{{ $option->value }}">{{ __($option->value) }}</flux:select.option>@endforeach
        </flux:select>
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-950/60">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[46rem] text-start text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-white/5"><tr><th class="px-4 py-3 text-start">{{ __('User') }}</th><th class="px-4 py-3 text-start">{{ __('Role') }}</th><th class="px-4 py-3 text-start">{{ __('Status') }}</th><th class="px-4 py-3 text-start">{{ __('Joined') }}</th><th class="px-4 py-3 text-end">{{ __('Available actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                    @forelse ($this->users as $user)
                        @php($role = $user->roles->first()?->name ?? UserRole::User->value)
                        <tr wire:key="managed-user-{{ $user->getKey() }}" class="transition hover:bg-slate-50/70 dark:hover:bg-white/5">
                            <td class="px-4 py-4"><div class="flex items-center gap-3"><div class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full bg-indigo-500/10 font-black text-indigo-600">@if ($user->hasMedia('avatar'))<img src="{{ $user->getFirstMediaUrl('avatar', 'avatar_small') }}" alt="" class="size-full object-cover" />@else{{ mb_strtoupper(mb_substr($user->username, 0, 1)) }}@endif</div><div class="min-w-0"><a href="{{ route('user.profile', $user) }}" wire:navigate class="font-black">{{ '@'.$user->username }}</a><p class="max-w-52 truncate text-xs text-slate-500">{{ $user->email }}</p></div></div></td>
                            <td class="px-4 py-4">
                                @can(UserPermission::RolesManage->value)
                                    @if (! auth()->user()->is($user))
                                        <select wire:change="changeRole({{ $user->getKey() }}, $event.target.value)" class="rounded-xl border border-slate-200 bg-transparent px-2.5 py-2 text-xs font-bold dark:border-white/15">
                                            @foreach (UserRole::cases() as $option)<option value="{{ $option->value }}" @selected($role === $option->value)>{{ __($option->value) }}</option>@endforeach
                                        </select>
                                    @else<span class="font-bold">{{ __($role) }}</span>@endif
                                @else<span class="font-bold">{{ __($role) }}</span>@endcan
                            </td>
                            <td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' => $user->status === UserStatus::Active, 'bg-rose-500/10 text-rose-700 dark:text-rose-300' => $user->status === UserStatus::Suspended])>{{ __($user->status->value) }}</span></td>
                            <td class="px-4 py-4 text-xs text-slate-500">{{ $user->created_at->toFormattedDateString() }}</td>
                            <td class="px-4 py-4 text-end">
                                @can(UserPermission::UsersSuspend->value)
                                    @if (! auth()->user()->is($user) && ! $user->hasRole(UserRole::SuperUser->value))
                                        <flux:button wire:click="changeStatus({{ $user->getKey() }}, '{{ $user->status === UserStatus::Active ? UserStatus::Suspended->value : UserStatus::Active->value }}')" wire:confirm="{{ __('Change this account status?') }}" size="sm" :variant="$user->status === UserStatus::Active ? 'danger' : 'primary'">{{ $user->status === UserStatus::Active ? __('Suspend') : __('Activate') }}</flux:button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-12 text-center text-slate-500">{{ __('No users match these filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $this->users->links() }}</div>
</x-admin.shell>
