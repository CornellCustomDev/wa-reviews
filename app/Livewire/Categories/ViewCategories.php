<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;

class ViewCategories extends Component
{
    public function render()
    {
        return view('livewire.categories.view-categories', [
            'categories' => Category::all(),
        ])->layout('components.layouts.app');
    }
}
