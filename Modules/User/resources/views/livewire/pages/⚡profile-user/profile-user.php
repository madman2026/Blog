<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Blog\Models\Post;
use Modules\User\Models\User;

new class extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load(['skills', 'media']);
    }

    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->whereBelongsTo($this->user, 'author')
            ->with(['translations', 'media'])
            ->withCount(['likes', 'views'])
            ->latest('published_at')
            ->paginate(9);
    }
};
