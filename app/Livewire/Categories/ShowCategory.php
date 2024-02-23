<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;

class ShowCategory extends Component
{
    public Category $category;

    public function render()
    {
        return view('livewire.categories.show-category', [
            'guidelines' => $this->category->guidelines,
        ])->layout('components.layouts.app', [
            'sidebar' => false,
        ]);
    }
}
