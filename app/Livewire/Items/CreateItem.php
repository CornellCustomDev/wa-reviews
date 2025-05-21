<?php

namespace App\Livewire\Items;

use App\Livewire\Features\SupportFileUploads\WithMultipleFileUploads;
use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
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
        return [
            'Projects' => route('projects'),
            $this->issue->project->name => route('project.show', $this->issue->project),
            $this->issue->scope->title => route('scope.show', $this->issue->scope),
            'Issue' => route('issue.show', $this->issue),
            'Add Observation' => 'active'
        ];
    }
}
