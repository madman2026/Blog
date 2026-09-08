<div class="mx-auto w-full max-w-md px-4 py-14 sm:px-0">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-950/5 sm:p-8 dark:border-white/10 dark:bg-slate-900">
        <div class="grid size-12 place-items-center rounded-xl bg-cyan-500/10 text-cyan-700 dark:text-cyan-300">
            <x-heroicon-o-key class="size-6" />
        </div>
        <h1 class="mt-5 text-2xl font-black">{{ __('Reset your password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Enter your email and we will send a secure reset link if the account exists.') }}</p>

        @if ($sent)
            <div class="mt-6 rounded-xl border border-emerald-300/60 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                {{ __('Check your inbox for the password reset link.') }}
            </div>
        @else
            <form wire:submit="sendResetLink" class="mt-6 space-y-5">
                <flux:input wire:model="email" type="email" dir="ltr" autocomplete="email" icon="envelope" label="{{ __('Email') }}" />
                <flux:button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">{{ __('Send reset link') }}</flux:button>
            </form>
        @endif

        <div class="mt-6 text-center text-sm"><flux:link href="{{ route('auth.login') }}" wire:navigate>{{ __('Back to login') }}</flux:link></div>
    </div>
</div>
