<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toastable;
use Modules\User\Actions\ConfirmPhoneVerificationChallenge;
use Modules\User\Actions\IssuePhoneVerificationChallenge;
use Modules\User\Actions\SubmitAuthorApplication;
use Modules\User\Actions\UpdateProfile;
use Modules\User\Actions\UploadAvatar;
use Modules\User\Data\UpdateProfileData;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;

new class extends Component
{
    use Toastable;
    use WithFileUploads;

    public string $username = '';

    public string $bio = '';

    public string $about = '';

    public string $preferredLocale = 'fa';

    /** @var array<string, string> */
    public array $socialLinks = ['website' => '', 'github' => '', 'linkedin' => ''];

    public string $skills = '';

    public mixed $avatar = null;

    public string $phoneCode = '';

    public function mount(): void
    {
        $user = auth()->user()->load(['skills', 'media', 'authorApplication']);
        $this->username = $user->username;
        $this->bio = $user->bio ?? '';
        $this->about = $user->about ?? '';
        $this->preferredLocale = $user->preferred_locale;
        $this->socialLinks = array_merge($this->socialLinks, $user->social_links ?? []);
        $this->skills = $user->skills->pluck('name')->implode(', ');
    }

    public function save(UpdateProfile $updateProfile, UploadAvatar $uploadAvatar): void
    {
        $user = auth()->user();
        $usernameRules = ['required', 'string', 'max:32'];

        if ($this->username !== $user->username) {
            $usernameRules = [
                ...$usernameRules,
                'min:3',
                'alpha_dash:ascii',
                'unique:users,username',
            ];
        }

        $validated = $this->validate([
            'username' => $usernameRules,
            'bio' => ['nullable', 'string', 'max:280'],
            'about' => ['nullable', 'string', 'max:5000'],
            'preferredLocale' => ['required', 'string', 'in:fa,en'],
            'socialLinks.website' => ['nullable', 'url:http,https', 'max:500'],
            'socialLinks.github' => ['nullable', 'url:http,https', 'max:500'],
            'socialLinks.linkedin' => ['nullable', 'url:http,https', 'max:500'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=256,min_height=256,max_width=5000,max_height=5000'],
        ]);

        $user = $updateProfile->handle($user, new UpdateProfileData(
            attributes: [
                'username' => $validated['username'],
                'bio' => filled($validated['bio']) ? $validated['bio'] : null,
                'about' => filled($validated['about']) ? $validated['about'] : null,
                'preferred_locale' => $validated['preferredLocale'],
                'social_links' => $validated['socialLinks'],
            ],
            skills: preg_split('/[,،]/u', $validated['skills']) ?: [],
        ));

        if ($this->avatar) {
            $uploadAvatar->handle($user, $this->avatar);
            $this->reset('avatar');
        }

        session()->put('locale', $validated['preferredLocale']);
        app()->setLocale($validated['preferredLocale']);
        $this->success(__('Profile saved.'));
    }

    public function sendEmailVerification(): void
    {
        $user = auth()->user();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        $this->success(__('Verification email queued.'));
    }

    public function sendPhoneVerification(IssuePhoneVerificationChallenge $issue): void
    {
        $issue->handle(auth()->user());
        $this->success(__('Verification code queued.'));
    }

    public function verifyPhone(ConfirmPhoneVerificationChallenge $confirm): void
    {
        $validated = $this->validate(['phoneCode' => ['required', 'digits:6']]);
        $confirm->handle(auth()->user(), $validated['phoneCode']);
        $this->reset('phoneCode');
        $this->success(__('Phone number verified.'));
    }

    public function applyAsAuthor(SubmitAuthorApplication $submit): void
    {
        $submit->handle(auth()->user());
        auth()->user()->load('authorApplication');
        $this->success(__('Author application submitted.'));
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <div>
        <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Account') }}</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight">{{ __('Profile settings') }}</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Complete your professional profile and verification details.') }}</p>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <form wire:submit="save" class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black">{{ __('Public identity') }}</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <flux:input wire:model="username" dir="ltr" label="{{ __('Username') }}" />
                    <flux:select wire:model="preferredLocale" label="{{ __('Preferred language') }}">
                        <flux:select.option value="fa">فارسی</flux:select.option>
                        <flux:select.option value="en">English</flux:select.option>
                    </flux:select>
                    <div class="sm:col-span-2"><flux:textarea wire:model="bio" rows="2" label="{{ __('Short bio') }}" /></div>
                    <div class="sm:col-span-2"><flux:textarea wire:model="about" rows="7" label="{{ __('About') }}" /></div>
                    <div class="sm:col-span-2"><flux:input wire:model="skills" label="{{ __('Skills') }}" description="{{ __('Separate skills with commas.') }}" /></div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black">{{ __('Social links') }}</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <flux:input wire:model="socialLinks.website" type="url" dir="ltr" label="{{ __('Website') }}" placeholder="https://" />
                    <flux:input wire:model="socialLinks.github" type="url" dir="ltr" label="GitHub" placeholder="https://github.com/..." />
                    <flux:input wire:model="socialLinks.linkedin" type="url" dir="ltr" label="LinkedIn" placeholder="https://linkedin.com/in/..." />
                </div>
            </section>

            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Save profile') }}</flux:button>
        </form>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black">{{ __('Profile photo') }}</h2>
                <div class="mt-4 flex items-center gap-4">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" alt="" class="size-20 rounded-2xl object-cover" />
                    @elseif (auth()->user()->hasMedia('avatar'))
                        <img src="{{ auth()->user()->getFirstMediaUrl('avatar', 'avatar_small') }}" alt="" class="size-20 rounded-2xl object-cover" />
                    @else
                        <div class="grid size-20 place-items-center rounded-2xl bg-slate-100 text-2xl font-black dark:bg-slate-800">{{ mb_strtoupper(mb_substr(auth()->user()->username, 0, 1)) }}</div>
                    @endif
                </div>
                <flux:input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/webp" class="mt-4" />
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black">{{ __('Verification') }}</h2>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Email') }}</span>
                        @if (auth()->user()->hasVerifiedEmail())
                            <span class="font-bold text-emerald-600">{{ __('Verified') }}</span>
                        @else
                            <flux:button wire:click="sendEmailVerification" size="sm" variant="ghost">{{ __('Send link') }}</flux:button>
                        @endif
                    </div>
                    <div class="border-t border-slate-100 pt-4 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3">
                            <span>{{ __('Phone') }}</span>
                            @if (auth()->user()->phone_verified_at)
                                <span class="font-bold text-emerald-600">{{ __('Verified') }}</span>
                            @else
                                <flux:button wire:click="sendPhoneVerification" size="sm" variant="ghost">{{ __('Send code') }}</flux:button>
                            @endif
                        </div>
                        @if (! auth()->user()->phone_verified_at)
                            <div class="mt-3 flex gap-2">
                                <flux:input wire:model="phoneCode" inputmode="numeric" dir="ltr" placeholder="000000" />
                                <flux:button wire:click="verifyPhone" size="sm" variant="primary">{{ __('Verify') }}</flux:button>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            @unless (auth()->user()->hasAnyRole([UserRole::Author->value, UserRole::Admin->value, UserRole::SuperUser->value]))
                <section class="rounded-2xl border border-cyan-400/30 bg-cyan-500/5 p-5">
                    <h2 class="font-black">{{ __('Become an author') }}</h2>
                    @if (auth()->user()->authorApplication?->status === AuthorApplicationStatus::Pending)
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Your application is awaiting review.') }}</p>
                    @elseif (auth()->user()->authorApplication?->status === AuthorApplicationStatus::Rejected)
                        <p class="mt-2 text-sm leading-6 text-rose-700 dark:text-rose-300">{{ auth()->user()->authorApplication->review_notes }}</p>
                        <flux:button wire:click="applyAsAuthor" class="mt-4" size="sm" variant="primary">{{ __('Apply again') }}</flux:button>
                    @else
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Complete every profile field, then submit your writing application.') }}</p>
                        <flux:button wire:click="applyAsAuthor" class="mt-4" size="sm" variant="primary">{{ __('Apply as author') }}</flux:button>
                    @endif
                </section>
            @endunless
        </aside>
    </div>
</div>
