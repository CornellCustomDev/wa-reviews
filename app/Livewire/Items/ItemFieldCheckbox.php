<?php

namespace App\Livewire\Items;

use App\Models\Item;

class ItemFieldCheckbox extends ItemField
{
    public function mount(Item $item): void
    {
        $this->item = $item;
        $this->form->setModel($this->item);
    }
}
