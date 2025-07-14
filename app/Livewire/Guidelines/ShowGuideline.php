<?php

namespace App\Livewire\Guidelines;

use App\Livewire\Forms\GuidelineForm;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Guideline;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowGuideline extends Component
{
    public Guideline $guideline;
    public GuidelineForm $form;

    public function mount(Guideline $guideline)
    {
        $this->guideline = $guideline;
        $this->form->setModel($guideline);
    }

    public function save(): void
    {
        $this->authorize('update', $this->form->guideline);

        $this->guideline = $this->form->update();

        $this->dispatch('close-edit');
    }

    #[Computed] public function criterionOptions(): array
    {
        return Criterion::all()
            ->map(fn ($criterion) => [
                'value' => $criterion->id,
                'option' => $criterion->getLongName(),
            ])
            ->toArray();
    }

    #[Computed] public function categoryOptions(): array
    {
        return Category::all()
            ->map(fn ($category) => [
                'value' => $category->id,
                'option' => $category->name,
            ])
            ->toArray();
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
