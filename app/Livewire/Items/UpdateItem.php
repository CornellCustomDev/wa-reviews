<?php

namespace App\Livewire\Items;

use App\Livewire\Features\SupportFileUploads\WithMultipleFileUploads;
use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
use App\Models\Item;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateItem extends Component
{
    use WithMultipleFileUploads;

    public ItemForm $form;
    public Issue $issue;

    public function mount(Issue $issue, Item $item)
    {
        $this->issue = $issue;
        $this->form->setModel($item);
    }

    public function save()
    {
        $this->authorize('update', $this->form->item->issue);
        $this->form->update();

        if ($this->form->item->isAiGenerated()) {
            $this->form->item->markAiModified();
        }

        $this->dispatch('close-edit');
        $this->dispatch('items-updated');
        //return redirect()->route('issue.show', $this->form->item->issue);
    }

    #[On('remove-existing-image')]
    public function removeExistingImage(string $filename): void
    {
        $this->form->removeExistingImage($filename);
    }
}
