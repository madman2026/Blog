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
                aria-hidden="true"
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
            >
                <x-heroicon-o-user-plus class="size-6" />
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
                {{ __('auth::register.title') }}
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
                {{ __('auth::register.description') }}
            </p>

        </div>


        <form
            wire:submit="register"
            class="space-y-5"
        >

            {{-- Email --}}
            <flux:field>

                <flux:label>
                    {{ __('auth::fields.email') }}
                </flux:label>

                <flux:input
                    wire:model.blur="form.email"

                    type="email"
                    inputmode="email"
                    dir="ltr"

                    autocomplete="email"
                    autocapitalize="none"
                    spellcheck="false"

                    placeholder="{{ __('auth::placeholders.email') }}"

                    icon="envelope"
                />

                <flux:error name="form.email" />

            </flux:field>


            {{-- Username --}}
            <flux:field>

                <flux:label>
                    {{ __('auth::fields.username') }}
                </flux:label>

                <flux:input
                    wire:model.blur="form.username"

                    type="text"
                    inputmode="text"
                    dir="ltr"

                    autocomplete="username"
                    autocapitalize="none"
                    spellcheck="false"

                    placeholder="{{ __('auth::placeholders.username') }}"

                    icon="user"
                />

                <flux:error name="form.username" />

            </flux:field>


            {{-- Phone --}}
            <flux:field>

                <flux:label>
                    {{ __('Phone number') }}
                </flux:label>

                <flux:input
                    wire:model.blur="form.phone"
                    type="tel"
                    inputmode="tel"
                    dir="ltr"
                    autocomplete="tel"
                    placeholder="+989121234567"
                    icon="phone"
                />

                <flux:error name="form.phone" />

            </flux:field>


            {{-- Password --}}
            <flux:field>

                <flux:label>
                    {{ __('auth::fields.password') }}
                </flux:label>

                <flux:input
                    wire:model.blur="form.password"

                    type="password"
                    dir="ltr"

                    autocomplete="new-password"

                    placeholder="{{ __('auth::placeholders.password') }}"

                    icon="lock-closed"
                />

                <flux:error name="form.password" />

            </flux:field>


            {{-- Password Confirmation --}}
            <flux:field>

                <flux:label>
                    {{ __('auth::fields.password_confirmation') }}
                </flux:label>

                <flux:input
                    wire:model.blur="form.password_confirmation"

                    type="password"
                    dir="ltr"

                    autocomplete="new-password"

                    placeholder="{{ __('auth::placeholders.password_confirmation') }}"

                    icon="lock-closed"
                />

                <flux:error name="form.password_confirmation" />

            </flux:field>


            {{-- Submit --}}
            <flux:button
                type="submit"
                variant="primary"

                class="
                    w-full
                    justify-center
                "

                wire:loading.attr="disabled"
                wire:target="register"
            >

                <span
                    wire:loading.remove
                    wire:target="register"
                >
                    {{ __('auth::register.submit') }}
                </span>

                <span
                    wire:loading
                    wire:target="register"

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

                    {{ __('auth::register.loading') }}
                </span>

            </flux:button>


            {{-- Login --}}
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
                    {{ __('auth::register.have_account') }}
                </span>

                <flux:link
                    href="{{ route('auth.login') }}"
                    wire:navigate
                    class="font-medium"
                >
                    {{ __('auth::register.login') }}
                </flux:link>
            </div>

        </form>

    </div>
</div>
