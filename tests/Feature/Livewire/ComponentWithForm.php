<?php

namespace Tests\Feature\Livewire;

use Livewire\Component;
use Livewire\Features\SupportFormObjects\Form;

/**
 * https://github.com/livewire/livewire/discussions/8969#discussioncomment-11122604
 */
class ComponentWithForm extends Component
{
    public string $formClass = '';

    public Form $form;

    public function mount($formClass): void
    {
        $this->form = new $formClass($this, 'form');
    }

    public function submit(): void
    {
        if (method_exists($this->form, 'submit')) {
            $this->form->submit();
        }
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
