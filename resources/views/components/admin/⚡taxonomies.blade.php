<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toastable;
use Modules\Blog\Actions\SaveCategory;
use Modules\Blog\Actions\SaveTag;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Tag;
use Modules\User\Enums\UserPermission;

new class extends Component
{
    use Toastable;
    use WithPagination;

    public string $kind = 'category';
    public ?int $editingId = null;
    public string $faName = '';
    public string $faSlug = '';
    public string $faDescription = '';
    public string $enName = '';
    public string $enSlug = '';
    public string $enDescription = '';
    public ?int $parentId = null;
    public bool $isActive = true;

    public function mount(): void
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);
    }

    #[Computed]
    public function categories(): LengthAwarePaginator
    {
        return Category::query()
            ->with(['translations', 'parent.translations'])
            ->withCount(['children', 'posts'])
            ->latest()
            ->paginate(10, pageName: 'categoriesPage');
    }

    #[Computed]
    public function tags(): LengthAwarePaginator
    {
        return Tag::query()
            ->with('translations')
            ->withCount('posts')
            ->latest()
            ->paginate(10, pageName: 'tagsPage');
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Category> */
    #[Computed]
    public function parentOptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::query()
            ->when($this->editingId && $this->kind === 'category', fn ($query) => $query->whereKeyNot($this->editingId))
            ->with('translations')
            ->orderBy('id')
            ->get();
    }

    public function openCreate(string $kind): void
    {
        $this->resetForm();
        $this->kind = $kind;
        $this->modal('taxonomy-form')->show();
    }

    public function edit(string $kind, int $id): void
    {
        $this->resetForm();
        $this->kind = $kind;
        $this->editingId = $id;

        $entity = $kind === 'category'
            ? Category::query()->with('translations')->findOrFail($id)
            : Tag::query()->with('translations')->findOrFail($id);

        $fa = $entity->translations->firstWhere('locale', 'fa');
        $en = $entity->translations->firstWhere('locale', 'en');
        $this->faName = $fa?->name ?? '';
        $this->faSlug = $fa?->slug ?? '';
        $this->faDescription = $fa?->description ?? '';
        $this->enName = $en?->name ?? '';
        $this->enSlug = $en?->slug ?? '';
        $this->enDescription = $en?->description ?? '';

        if ($entity instanceof Category) {
            $this->parentId = $entity->parent_id;
            $this->isActive = $entity->is_active;
        }

        $this->modal('taxonomy-form')->show();
    }

    public function save(SaveCategory $saveCategory, SaveTag $saveTag): void
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);
        $validated = $this->validate([
            'kind' => ['required', 'in:category,tag'],
            'faName' => ['required', 'string', 'max:255'],
            'faSlug' => ['required', 'alpha_dash', 'max:255'],
            'enName' => ['nullable', 'string', 'max:255'],
            'enSlug' => ['nullable', 'required_with:enName', 'alpha_dash', 'max:255'],
            'faDescription' => ['nullable', 'string', 'max:2000'],
            'enDescription' => ['nullable', 'string', 'max:2000'],
            'parentId' => ['nullable', 'integer', 'exists:categories,id'],
            'isActive' => ['boolean'],
        ]);

        $translations = [[
            'locale' => 'fa',
            'name' => $validated['faName'],
            'slug' => $validated['faSlug'],
            'description' => filled($validated['faDescription']) ? $validated['faDescription'] : null,
        ]];

        if (filled($validated['enName'])) {
            $translations[] = [
                'locale' => 'en',
                'name' => $validated['enName'],
                'slug' => $validated['enSlug'],
                'description' => filled($validated['enDescription']) ? $validated['enDescription'] : null,
            ];
        }

        if ($this->kind === 'category') {
            $category = $this->editingId ? Category::query()->findOrFail($this->editingId) : null;
            $saveCategory->handle([
                'parent_id' => $validated['parentId'],
                'is_active' => $validated['isActive'],
                'translations' => $translations,
            ], $category);
            unset($this->categories, $this->parentOptions);
        } else {
            $tag = $this->editingId ? Tag::query()->findOrFail($this->editingId) : null;
            $saveTag->handle(['translations' => array_map(fn (array $translation): array => array_intersect_key($translation, array_flip(['locale', 'name', 'slug'])), $translations)], $tag);
            unset($this->tags);
        }

        $this->modal('taxonomy-form')->close();
        $this->resetForm();
        $this->success(__('Taxonomy saved.'));
    }

    public function delete(string $kind, int $id): void
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        if ($kind === 'category') {
            $category = Category::query()->findOrFail($id);
            if ($category->children()->exists() || $category->posts()->exists()) {
                throw ValidationException::withMessages(['category' => __('A category in use cannot be deleted.')]);
            }
            $category->delete();
            unset($this->categories, $this->parentOptions);
        } else {
            $tag = Tag::query()->findOrFail($id);
            if ($tag->posts()->exists()) {
                throw ValidationException::withMessages(['tag' => __('A tag in use cannot be deleted.')]);
            }
            $tag->delete();
            unset($this->tags);
        }

        $this->success(__('Taxonomy deleted.'));
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'faName', 'faSlug', 'faDescription', 'enName', 'enSlug', 'enDescription', 'parentId');
        $this->isActive = true;
        $this->resetValidation();
    }
};
?>

<x-admin.shell :title="__('Taxonomies')" :description="__('Keep categories and tags bilingual, clean and ready for discovery features.')">
    <div class="grid gap-8 xl:grid-cols-2">
        @foreach ([
            ['kind' => 'category', 'title' => __('Categories'), 'items' => $this->categories],
            ['kind' => 'tag', 'title' => __('Tags'), 'items' => $this->tags],
        ] as $group)
            <section wire:key="taxonomy-group-{{ $group['kind'] }}">
                <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-black">{{ $group['title'] }}</h2><flux:button wire:click="openCreate('{{ $group['kind'] }}')" size="sm" variant="primary" icon="plus">{{ __('Add') }}</flux:button></div>
                <div class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-slate-950/60">
                    @forelse ($group['items'] as $item)
                        @php($translation = $item->translations->firstWhere('locale', app()->getLocale()) ?? $item->translations->first())
                        <div wire:key="{{ $group['kind'] }}-{{ $item->getKey() }}" class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0"><p class="truncate font-black">{{ $translation?->name }}</p><p class="mt-1 truncate text-xs text-slate-500">{{ $translation?->slug }} · {{ number_format($item->posts_count) }} {{ __('linked articles') }}@if ($item instanceof Category) · {{ $item->is_active ? __('active') : __('inactive') }}@endif</p></div>
                            <div class="flex shrink-0 gap-1"><flux:button wire:click="edit('{{ $group['kind'] }}', {{ $item->getKey() }})" size="sm" variant="ghost" icon="pencil-square" /><flux:button wire:click="delete('{{ $group['kind'] }}', {{ $item->getKey() }})" wire:confirm="{{ __('Delete this taxonomy?') }}" size="sm" variant="ghost" icon="trash" /></div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-500">{{ __('Nothing has been added yet.') }}</div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $group['items']->links() }}</div>
            </section>
        @endforeach
    </div>

    <flux:modal name="taxonomy-form" class="md:w-[40rem]">
        <form wire:submit="save" class="space-y-5">
            <div><flux:heading size="lg">{{ $editingId ? __('Edit taxonomy') : __('New taxonomy') }}</flux:heading><flux:text class="mt-2">{{ __('Persian is required; English remains optional.') }}</flux:text></div>
            <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="faName" label="{{ __('Persian name') }}" /><flux:input wire:model="faSlug" label="{{ __('Persian slug') }}" dir="ltr" /></div>
            @if ($kind === 'category')<flux:textarea wire:model="faDescription" rows="2" label="{{ __('Persian description') }}" />@endif
            <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="enName" label="{{ __('English name') }}" /><flux:input wire:model="enSlug" label="{{ __('English slug') }}" dir="ltr" /></div>
            @if ($kind === 'category')
                <flux:textarea wire:model="enDescription" rows="2" label="{{ __('English description') }}" />
                <div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="parentId" label="{{ __('Parent category') }}"><flux:select.option value="">{{ __('No parent') }}</flux:select.option>@foreach ($this->parentOptions as $parent)@php($parentName = $parent->translations->firstWhere('locale', app()->getLocale())?->name ?? $parent->translations->first()?->name)<flux:select.option value="{{ $parent->getKey() }}">{{ $parentName }}</flux:select.option>@endforeach</flux:select><flux:checkbox wire:model="isActive" label="{{ __('Active') }}" /></div>
            @endif
            <div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button></div>
        </form>
    </flux:modal>
</x-admin.shell>
