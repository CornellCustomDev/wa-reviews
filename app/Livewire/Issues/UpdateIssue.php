<?php

namespace App\Livewire\Issues;

use App\Livewire\Features\SupportFileUploads\WithMultipleFileUploads;
use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Scope;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateIssue extends Component
{
    use WithMultipleFileUploads;

    public IssueForm $form;
    public Scope $scope;

    public function mount(Scope $scope, Issue $issue): void
    {
        $this->scope = $scope;
        $this->form->setModel($issue);
    }

    #[On('items-updated')]
    public function refreshIssue(): void
    {
        $issue = $this->form->issue->fresh();
        $this->form->setModel($issue);
    }

    #[Computed(persist: true)]
    public function getGuidelinesOptions(): array
    {
        return $this->form->getGuidelineSelectArray();
    }

    #[Computed(persist: true)]
    public function getIssueInstanceOptions(): array
    {
        return $this->form->getIssueInstanceSelectArray();
    }

    public function save()
    {
        $this->authorize('update', $this->form->issue);
        $this->form->update();

        return redirect()->route('issue.show', $this->form->issue);
    }

    #[On('remove-existing-image')]
    public function removeExistingImage(string $filename): void
    {
        $this->form->removeExistingImage($filename);
    }
}
