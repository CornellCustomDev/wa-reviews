<?php

namespace App\Livewire\Items;

use App\Livewire\Forms\ItemForm;
use App\Models\Item;
use Livewire\Component;

class ItemField extends Component
{
    public ItemForm $form;
    public Item $item;
    public string $field;
    public string $label;
    public string $fieldType = 'text';
    public array $options = [];

    public bool $initialized = false;
    public bool $editing = false;

    public function edit(): void
    {
        $this->authorize('update', $this->item->issue);

        if (! $this->initialized) {
            $this->form->setModel($this->item);
        }
        $this->editing = true;
    }

    public function save(): void
    {
        $this->authorize('update', $this->item->issue);

        $this->form->update($this->field);

        $this->item = $this->form->getModel();

        $this->editing = false;
    }

    public function cancel(): void
    {
        $this->initialized = false;
        $this->editing = false;
    }

    public function close(): void
    {
        $this->editing = false;
    }
}
