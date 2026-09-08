<div
    class="
        mx-auto
        w-full
        max-w-md
        px-4
        sm:px-0
    "
>
    <div
        class="
            rounded-2xl
            border
            border-zinc-200/80
            bg-white/90

            p-5
            sm:p-7

            shadow-lg
            shadow-zinc-950/[0.04]

            backdrop-blur-xl

            dark:border-white/10
            dark:bg-zinc-900/85
            dark:shadow-black/20
        "
    >

        {{-- Header --}}
        <div class="mb-7 text-center">

            <div
                class="
                    mx-auto
                    mb-4

                    flex
                    size-12
                    items-center
                    justify-center

                    rounded-xl

                    bg-indigo-600
                    text-white

                    shadow-md
                    shadow-indigo-600/20

                    dark:bg-indigo-500
                    dark:shadow-indigo-500/10
                "
                aria-hidden="true"
            >
                <x-heroicon-o-lock-closed class="size-6" />
            </div>


            <h1
                class="
                    text-xl
                    font-semibold
                    tracking-tight
                    text-zinc-950

                    sm:text-2xl

                    dark:text-white
                "
            >
                {{ __('auth::login.title') }}
            </h1>


            <p
                class="
                    mx-auto
                    mt-2
                    max-w-sm

                    text-sm
                    leading-6
                    text-zinc-500

                    dark:text-zinc-400
                "
            >
                {{ __('auth::login.description') }}
            </p>

        </div>


        <form
            wire:submit="login"
            class="space-y-5"
        >

            {{-- Email or phone --}}
            <flux:field>

                <flux:label>
                    {{ __('Email or phone') }}
                </flux:label>


                <flux:input
                    wire:model.blur="form.identifier"

                    type="text"

                    dir="ltr"

                    autocomplete="username"
                    autocapitalize="none"
                    spellcheck="false"

                    placeholder="name@example.com / +98912..."

                    icon="envelope"
                />


                <flux:error name="form.identifier" />

            </flux:field>


            {{-- Password --}}
            <flux:field>

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-4
                    "
                >

                    <flux:label>
                        {{ __('auth::fields.password') }}
                    </flux:label>


                    <flux:link
                        href="{{ route('auth.forget-password') }}"
                        wire:navigate

                        class="
                            shrink-0
                            text-xs
                            font-medium
                        "
                    >
                        {{ __('auth::login.forgot_password') }}
                    </flux:link>

                </div>


                <flux:input
                    wire:model.blur="form.password"

                    type="password"
                    dir="ltr"

                    autocomplete="current-password"

                    placeholder="{{ __('auth::placeholders.password') }}"

                    icon="lock-closed"
                />


                <flux:error name="form.password" />

            </flux:field>


            {{-- Remember --}}
            <div class="flex items-center">

                <flux:checkbox
                    wire:model="form.remember"
                    label="{{ __('auth::login.remember') }}"
                />

            </div>


            {{-- Submit --}}
            <flux:button
                type="submit"
                variant="primary"

                class="
                    w-full
                    justify-center
                "

                wire:loading.attr="disabled"
                wire:target="login"
            >

                <span
                    wire:loading.remove
                    wire:target="login"
                >
                    {{ __('auth::login.submit') }}
                </span>


                <span
                    wire:loading
                    wire:target="login"

                    class="
                        inline-flex
                        items-center
                        gap-2
                    "
                >

                    <svg
                        class="size-4 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4Z"
                        />
                    </svg>

                    {{ __('auth::login.loading') }}

                </span>

            </flux:button>


            {{-- Register --}}
            <div
                class="
                    border-t
                    border-zinc-200/70

                    pt-5

                    text-center
                    text-sm

                    dark:border-white/10
                "
            >

                <span class="text-zinc-500 dark:text-zinc-400">
                    {{ __('auth::login.no_account') }}
                </span>

                <flux:link
                    href="{{ route('auth.register') }}"
                    wire:navigate
                    class="font-medium"
                >
                    {{ __('auth::login.register') }}
                </flux:link>

            </div>

        </form>

    </div>
</div>
