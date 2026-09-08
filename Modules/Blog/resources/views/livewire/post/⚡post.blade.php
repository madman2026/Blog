<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toastable;
use Modules\Blog\Actions\SavePost;
use Modules\Blog\Actions\SubmitPostForReview;
use Modules\Blog\Application\Posts\Data\SavePostData;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;

new class extends Component
{
    use Toastable;
    use WithFileUploads;

    public ?Post $post = null;

    public string $type = 'article';

    public string $faTitle = '';

    public string $faSummary = '';

    public string $faBody = '';

    public bool $includeEnglish = false;

    public string $enTitle = '';

    public string $enSummary = '';

    public string $enBody = '';

    /** @var array<int, int|string> */
    public array $categoryIds = [];

    /** @var array<int, int|string> */
    public array $tagIds = [];

    public mixed $featuredImage = null;

    public string $imageAlt = '';

    public function mount(?Post $post = null): void
    {
        if ($post) {
            Gate::authorize('update', $post);
            $this->post = $post->load(['translations', 'categories', 'tags', 'media']);
            $this->type = $post->type->value;
            $this->categoryIds = $post->categories->modelKeys();
            $this->tagIds = $post->tags->modelKeys();

            $fa = $post->translations->firstWhere('locale', 'fa');
            $en = $post->translations->firstWhere('locale', 'en');
            $this->faTitle = $fa?->title ?? '';
            $this->faSummary = $fa?->summary ?? '';
            $this->faBody = $fa?->body ?? '';
            $this->includeEnglish = $en !== null;
            $this->enTitle = $en?->title ?? '';
            $this->enSummary = $en?->summary ?? '';
            $this->enBody = $en?->body ?? '';
            $this->imageAlt = (string) $post->getFirstMedia('featured_image')?->getCustomProperty('alt', '');

            return;
        }

        Gate::authorize('create', Post::class);
    }

    /** @return Collection<int, Category> */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->where('is_active', true)->with('translations')->get();
    }

    /** @return Collection<int, Tag> */
    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()->with('translations')->get();
    }

    public function save(SavePost $savePost): void
    {
        $validated = $this->validate($this->rules());

        $translations = [[
            'locale' => 'fa',
            'title' => $validated['faTitle'],
            'summary' => filled($validated['faSummary']) ? $validated['faSummary'] : null,
            'body' => $validated['faBody'],
        ]];

        if ($validated['includeEnglish']) {
            $translations[] = [
                'locale' => 'en',
                'title' => $validated['enTitle'],
                'summary' => filled($validated['enSummary']) ? $validated['enSummary'] : null,
                'body' => $validated['enBody'],
            ];
        }

        $this->post = $savePost->handle(
            auth()->user(),
            new SavePostData(
                type: PostType::from($validated['type']),
                translations: $translations,
                categoryIds: array_map('intval', $validated['categoryIds']),
                tagIds: array_map('intval', $validated['tagIds']),
            ),
            $this->post,
        );

        if ($this->featuredImage) {
            $this->post
                ->addMedia($this->featuredImage)
                ->usingFileName($this->featuredImage->getClientOriginalName())
                ->withCustomProperties(['alt' => $validated['imageAlt']])
                ->withResponsiveImages()
                ->toMediaCollection('featured_image');
            $this->reset('featuredImage');
        } elseif ($this->post->hasMedia('featured_image')) {
            $media = $this->post->getFirstMedia('featured_image');
            $media?->setCustomProperty('alt', $validated['imageAlt']);
            $media?->save();
        }

        $this->post->load(['translations', 'categories', 'tags', 'media']);
        $this->success(__('Draft saved.'));

        if (! request()->routeIs('blog.manage.posts.edit')) {
            $this->redirectRoute('blog.manage.posts.edit', $this->post, navigate: true);
        }
    }

    public function submit(SavePost $savePost, SubmitPostForReview $submit): void
    {
        $this->save($savePost);
        Gate::authorize('submit', $this->post);
        $submit->handle($this->post);
        $this->success(__('Post submitted for editorial review.'));
        $this->redirectRoute('blog.manage.posts.index', navigate: true);
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:article,news'],
            'faTitle' => ['required', 'string', 'max:255'],
            'faSummary' => ['nullable', 'string', 'max:1000'],
            'faBody' => ['required', 'string', 'max:200000'],
            'includeEnglish' => ['boolean'],
            'enTitle' => [$this->includeEnglish ? 'required' : 'nullable', 'string', 'max:255'],
            'enSummary' => ['nullable', 'string', 'max:1000'],
            'enBody' => [$this->includeEnglish ? 'required' : 'nullable', 'string', 'max:200000'],
            'categoryIds' => ['array', 'max:5'],
            'categoryIds.*' => ['integer', 'exists:categories,id'],
            'tagIds' => ['array', 'max:20'],
            'tagIds.*' => ['integer', 'exists:tags,id'],
            'featuredImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=640,min_height=360,max_width=6000,max_height=6000'],
            'imageAlt' => ['nullable', 'string', 'max:255'],
        ];
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:link href="{{ route('blog.manage.posts.index') }}" wire:navigate>&rarr; {{ __('Back to studio') }}</flux:link>
            <h1 class="mt-3 text-3xl font-black tracking-tight">{{ $post ? __('Edit publication') : __('Create publication') }}</h1>
            @if ($post)
                <p class="mt-2 text-sm text-slate-500">{{ __('Status') }}: <span class="font-bold">{{ __($post->status->value) }}</span></p>
            @endif
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="save" wire:loading.attr="disabled" variant="ghost" icon="cloud-arrow-up">{{ __('Save draft') }}</flux:button>
            @if (! $post || auth()->user()->can('submit', $post))
                <flux:button wire:click="submit" wire:loading.attr="disabled" variant="primary" icon="paper-airplane">{{ __('Submit for review') }}</flux:button>
            @endif
        </div>
    </div>

    @if ($post?->review_notes)
        <div class="mt-6 rounded-2xl border border-amber-300/60 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
            <div class="font-black">{{ __('Editorial feedback') }}</div>
            <p class="mt-1 leading-6">{{ $post->review_notes }}</p>
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="font-black">{{ __('Persian content') }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ __('The primary publication language.') }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-mono text-xs font-bold dark:bg-slate-800">FA</span>
                </div>
                <div class="mt-5 space-y-5" dir="rtl">
                    <flux:input wire:model="faTitle" label="{{ __('Title') }}" />
                    <flux:textarea wire:model="faSummary" rows="3" label="{{ __('Summary') }}" />
                    <flux:field>
                        <flux:label>{{ __('Article body') }}</flux:label>
                        <div wire:ignore x-init="$nextTick(() => window.initializeRichEditors?.())">
                            <textarea data-rich-editor data-model="faBody" data-direction="rtl">{{ $faBody }}</textarea>
                        </div>
                        <flux:description>{{ __('HTML from the editor is sanitized before saving.') }}</flux:description>
                        <flux:error name="faBody" />
                    </flux:field>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-slate-900">
                <flux:switch wire:model.live="includeEnglish" label="{{ __('Add English translation') }}" description="{{ __('Translation is optional and maintained by the author.') }}" />
                @if ($includeEnglish)
                    <div class="mt-5 space-y-5" dir="ltr">
                        <flux:input wire:model="enTitle" label="{{ __('English title') }}" />
                        <flux:textarea wire:model="enSummary" rows="3" label="{{ __('English summary') }}" />
                        <flux:field>
                            <flux:label>{{ __('English article body') }}</flux:label>
                            <div wire:ignore x-init="$nextTick(() => window.initializeRichEditors?.())">
                                <textarea data-rich-editor data-model="enBody" data-direction="ltr">{{ $enBody }}</textarea>
                            </div>
                            <flux:error name="enBody" />
                        </flux:field>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black">{{ __('Publication settings') }}</h2>
                <div class="mt-4 space-y-4">
                    <flux:select wire:model="type" label="{{ __('Format') }}">
                        <flux:select.option value="article">{{ __('Article') }}</flux:select.option>
                        <flux:select.option value="news">{{ __('News') }}</flux:select.option>
                    </flux:select>
                    <fieldset>
                        <legend class="mb-2 text-sm font-medium">{{ __('Topics') }}</legend>
                        <div class="max-h-44 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-white/10">
                        @foreach ($this->categories as $category)
                            @php($translation = $category->translations->firstWhere('locale', app()->getLocale()) ?? $category->translations->first())
                            <flux:checkbox wire:model="categoryIds" value="{{ $category->getKey() }}" label="{{ $translation?->name }}" />
                        @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="mb-2 text-sm font-medium">{{ __('Tags') }}</legend>
                        <div class="max-h-52 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-white/10">
                        @foreach ($this->tags as $tag)
                            @php($translation = $tag->translations->firstWhere('locale', app()->getLocale()) ?? $tag->translations->first())
                            <flux:checkbox wire:model="tagIds" value="{{ $tag->getKey() }}" label="{{ $translation?->name }}" />
                        @endforeach
                        </div>
                    </fieldset>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black">{{ __('Featured image') }}</h2>
                @if ($featuredImage)
                    <img src="{{ $featuredImage->temporaryUrl() }}" alt="" class="mt-4 aspect-video w-full rounded-xl object-cover" />
                @elseif ($post?->hasMedia('featured_image'))
                    <img src="{{ $post->getFirstMediaUrl('featured_image', 'thumbnail') }}" alt="" class="mt-4 aspect-video w-full rounded-xl object-cover" />
                @endif
                <div class="mt-4 space-y-4">
                    <flux:input type="file" wire:model="featuredImage" accept="image/jpeg,image/png,image/webp" label="{{ __('Upload image') }}" />
                    <flux:input wire:model="imageAlt" label="{{ __('Alternative text') }}" />
                    <p wire:loading wire:target="featuredImage" class="text-xs text-cyan-600">{{ __('Processing image…') }}</p>
                </div>
            </section>
        </aside>
    </div>
</div>
