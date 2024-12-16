<?php

namespace App\Livewire\Guidelines;

use App\Models\Guideline;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Sidebar extends Component
{
    public ?Guideline $guideline;

    #[Url(as: 'g', except: '')]
    public $guidelineNumber = '';

    public function mount()
    {
        if ($this->guidelineNumber) {
            $this->guideline = Guideline::find($this->guidelineNumber);
        }
    }

    #[On('show-guideline')]
    public function showGuideline($number): void
    {
        $this->guidelineNumber = $number;
        $this->guideline = Guideline::find($number);

        unset($this->scopeGuidelineRules);
    }

    public function hideGuideline(): void
    {
        $this->guideline = null;
        $this->guidelineNumber = '';
    }
}
