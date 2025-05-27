<?php

namespace App\Livewire\Items;

use App\Livewire\Features\SupportFileUploads\WithMultipleFileUploads;
use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['sidebar' => true])]
class CreateItem extends Component
{
    use WithMultipleFileUploads;

    public ItemForm $form;
    public Issue $issue;

    public function save()
    {
        $this->authorize('update', $this->issue->project);
        $this->form->store($this->issue);

        return redirect()->route('issue.show', $this->issue);
    }

    #[Computed(persist: true)]
    public function getGuidelinesOptions()
    {
        return $this->form->getGuidelineSelectArray();
    }

    public function render()
    {
        return view('livewire.items.create-item')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $breadcrumbs['Projects'] = route('projects');
        $breadcrumbs[$this->issue->project->name] = route('project.show', $this->issue->project);

        if ($this->issue->scope) {
            $breadcrumbs[$this->issue->scope->title] = route('scope.show', $this->issue->scope);
        }

        $breadcrumbs['Issue'] = route('issue.show', $this->issue);
        $breadcrumbs['Add Observation'] = 'active';

        return $breadcrumbs;
    }
}
