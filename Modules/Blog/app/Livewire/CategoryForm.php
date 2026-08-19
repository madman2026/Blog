<?php

namespace Modules\Blog\Livewire;

use Livewire\Form;
use Modules\Blog\Models\Category;

class CategoryForm extends Form
{
    public string $name = '';

    public function save()
    {
        $data = $this->validate();
        $category = null;
        if ($data['parent_id'] > 0) {
            $category = Category::query()->findOrFail($data['parent_id'])->create(['name']);
        } else {
            $category = Category::query()->create(['name']);
        }

        return $category;
    }
}
