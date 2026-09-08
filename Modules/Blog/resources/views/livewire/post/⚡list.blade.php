<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public ?int $category = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->category, fn ($query) => $query->whereHas('categories', fn ($categoryQuery) => $categoryQuery->whereKey($this->category)))
            ->when($this->search !== '', fn ($query) => $query->whereHas('translations', fn ($translationQuery) => $translationQuery
                ->where('locale', app()->getLocale())
                ->where(fn ($textQuery) => $textQuery
                    ->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('summary', 'like', '%'.$this->search.'%'))))
            ->with(['translations', 'author.media', 'media', 'categories.translations'])
            ->withCount(['comments' => fn ($query) => $query->approved(), 'likes', 'views'])
            ->latest('published_at')
            ->paginate(12);
    }

    /** @return Collection<int, Category> */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->where('is_active', true)->with('translations')->get();
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="max-w-3xl">
        <p class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ __('Knowledge stream') }}</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-5xl">{{ __('Articles and technology news') }}</h1>
        <p class="mt-4 leading-7 text-slate-600 dark:text-slate-300">{{ __('Practical engineering knowledge, thoughtful analysis and important updates.') }}</p>
    </div>

    <div class="mt-8 grid gap-3 rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur sm:grid-cols-[1fr_auto_auto] dark:border-white/10 dark:bg-slate-900/80">
        <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" placeholder="{{ __('Search title or summary') }}" clearable />
        <flux:select wire:model.live="type" class="sm:w-44">
            <flux:select.option value="">{{ __('All formats') }}</flux:select.option>
            <flux:select.option value="{{ PostType::Article->value }}">{{ __('Articles') }}</flux:select.option>
            <flux:select.option value="{{ PostType::News->value }}">{{ __('News') }}</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="category" class="sm:w-48">
            <flux:select.option value="">{{ __('All topics') }}</flux:select.option>
            @foreach ($this->categories as $item)
                @php($translation = $item->translations->firstWhere('locale', app()->getLocale()) ?? $item->translations->first())
                <flux:select.option value="{{ $item->getKey() }}">{{ $translation?->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->posts as $post)
            @php($translation = $post->translation())
            <article wire:key="publication-{{ $post->getKey() }}" class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-950/5 dark:border-white/10 dark:bg-slate-900">
                <a href="{{ route('blog.posts.show', $translation) }}" wire:navigate class="aspect-video overflow-hidden bg-gradient-to-br from-cyan-500/30 to-indigo-600/50">
                    @if ($post->hasMedia('featured_image'))
                        <img src="{{ $post->getFirstMediaUrl('featured_image', 'card') }}" alt="{{ $translation?->title }}" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105" />
                    @endif
                </a>
                <div class="flex flex-1 flex-col p-5">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-cyan-500/10 px-2.5 py-1 font-bold text-cyan-700 dark:text-cyan-300">{{ __($post->type->value) }}</span>
                        <span class="text-slate-500 dark:text-slate-400">{{ $post->published_at?->diffForHumans() }}</span>
                    </div>
                    <h2 class="mt-3 text-xl font-black leading-8">
                        <a href="{{ route('blog.posts.show', $translation) }}" wire:navigate class="transition hover:text-cyan-600">{{ $translation?->title }}</a>
                    </h2>
                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $translation?->summary }}</p>
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-5 text-xs text-slate-500 dark:border-white/10 dark:text-slate-400">
                        <a href="{{ route('user.profile', $post->author) }}" wire:navigate class="font-semibold">{{ '@'.$post->author->username }}</a>
                        <span>{{ $post->views_count }} {{ __('views') }} · {{ $post->likes_count }} {{ __('likes') }}</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 px-6 py-16 text-center dark:border-white/15">
                <x-heroicon-o-magnifying-glass class="mx-auto size-9 text-slate-400" />
                <p class="mt-3 font-bold">{{ __('No results found') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('Try another search or remove a filter.') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-10">{{ $this->posts->links() }}</div>
</div>
