<div class="mx-auto w-full max-w-md">
    <div class="rounded-3xl border border-zinc-200/80 bg-white p-8 shadow-lg shadow-zinc-950/5">

        {{-- Header --}}
        <div class="mb-8 text-center">

            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/25">
                <x-heroicon-o-lock-closed class="h-7 w-7" />
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
                خوش آمدید
            </h1>

            <p class="mt-2 text-sm text-zinc-500">
                برای ادامه وارد حساب کاربری خود شوید.
            </p>

        </div>

        <form
            wire:submit="login"
            class="space-y-6"
        >

            {{-- Email --}}
            <flux:field>

                <flux:label>
                    ایمیل
                </flux:label>

                <flux:input
                    wire:model.blur="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="example@email.com"
                    icon="envelope"
                />

                <flux:error name="form.email" />

            </flux:field>

            {{-- Password --}}
            <flux:field>

                <div class="flex items-center justify-between">

                    <flux:label>
                        رمز عبور
                    </flux:label>

                    <flux:link
                        href="{{ route('auth.forget-password') }}"
                        wire:navigate
                        class="text-xs"
                    >
                        فراموشی رمز؟
                    </flux:link>

                </div>

                <flux:input
                    wire:model.blur="form.password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    icon="lock-closed"
                />

                <flux:error name="form.password" />

            </flux:field>

            {{-- Remember --}}
            <div class="flex items-center justify-between">

                <flux:checkbox
                    wire:model="form.remember"
                    label="مرا به خاطر بسپار"
                />

            </div>

            {{-- Submit --}}
            <flux:button
                type="submit"
                variant="primary"
                class="w-full"
                wire:loading.attr="disabled"
            >

                <div class="flex items-center justify-center gap-2">
                    <span
                        wire:loading.remove
                        wire:target="login"
                    >
                        ورود به حساب
                    </span>
                </div>

            </flux:button>

        </form>

    </div>
</div>