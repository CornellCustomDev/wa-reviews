<?php

namespace App\Livewire\Issues;

use App\Models\Guideline;
use Livewire\Attributes\On;
use Livewire\Component;

class ItemShowGuideline extends Component
{
    public ?Guideline $guideline = null;

    #[On('show-guideline')]
    public function showGuideline($number): void
    {
        $this->guideline = Guideline::find($number);
        $this->modal('show-guideline')->show();
    }

    #[On('close-guideline')]
    public function closeGuideline(): void
    {
        $this->modal('show-guideline')->close();
        $this->guideline = null;
    }
}
