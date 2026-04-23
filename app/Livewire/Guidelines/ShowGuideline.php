<?php

namespace App\Livewire\Guidelines;

use App\Livewire\Forms\GuidelineForm;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Guideline;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowGuideline extends Component
{
    public Guideline $guideline;

    public function mount(Guideline $guideline): void
    {
        $this->guideline = $guideline;
    }

    #[On('version-updated')]
    public function getGuideline(): void
    {
        $this->guideline->refresh();
    }

    public function render()
    {
        return view('livewire.guidelines.show-guideline')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => [
                    'Guidelines' => route('guidelines.index'),
                    $this->guideline->name => 'active'
                ],
            ]);
    }
}
