<?php

namespace App\Livewire\Forms;

use App\Events\GuidelineChanged;
use App\Models\Guideline;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class GuidelineForm extends Form
{
    public Guideline $guideline;

    #[Validate('required', as: 'Heading')]
    public string $name = '';
    #[Validate('required', as: 'Criterion')]
    public int $criterion_id = 0;
    #[Validate('required', as: 'Category')]
    public int $category_id = 0;
    #[Validate('required', as: 'Description')]
    public string $notes = '';

    public function setModel(Guideline $guideline): void
    {
        $this->guideline = $guideline;
        // number
        $this->name = $guideline->name;
        $this->criterion_id = $guideline->criterion_id;
        $this->category_id = $guideline->category_id;
        $this->notes = Str::markdown($guideline->notes);
        // tools
    }

    public function update(): Guideline
    {
        $this->validate();

        $attributes = $this->all();

        $this->guideline->update($attributes);

        event(new GuidelineChanged($this->guideline, 'updated'));

        return $this->guideline;
    }
}
