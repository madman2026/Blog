<div class="mx-auto w-full max-w-md px-4 py-14 sm:px-0">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-950/5 sm:p-8 dark:border-white/10 dark:bg-slate-900">
        <div class="grid size-12 place-items-center rounded-xl bg-indigo-500/10 text-indigo-700 dark:text-indigo-300"><x-heroicon-o-lock-closed class="size-6" /></div>
        <h1 class="mt-5 text-2xl font-black">{{ __('Choose a new password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Use a strong, unique password for this account.') }}</p>

        <form wire:submit="resetPassword" class="mt-6 space-y-5">
            <flux:input wire:model="email" type="email" dir="ltr" autocomplete="email" label="{{ __('Email') }}" />
            <flux:input wire:model="password" type="password" dir="ltr" autocomplete="new-password" label="{{ __('New password') }}" viewable />
            <flux:input wire:model="password_confirmation" type="password" dir="ltr" autocomplete="new-password" label="{{ __('Confirm password') }}" viewable />
            <flux:button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">{{ __('Reset password') }}</flux:button>
        </form>
    </div>
</div>
