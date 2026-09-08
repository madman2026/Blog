<div class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:px-8">
    <div>
        <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Security') }}</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight">{{ __('Change password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Choose a strong, unique password for your account.') }}</p>
    </div>

    <form wire:submit="save" class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900">
        <flux:input wire:model="form.currentPassword" type="password" label="{{ __('Current password') }}" autocomplete="current-password" viewable />
        <flux:input wire:model="form.password" type="password" label="{{ __('New password') }}" autocomplete="new-password" viewable />
        <flux:input wire:model="form.passwordConfirmation" type="password" label="{{ __('Confirm new password') }}" autocomplete="new-password" viewable />

        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:button href="{{ route('user.settings') }}" wire:navigate variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Update password') }}</flux:button>
        </div>
    </form>
</div>
