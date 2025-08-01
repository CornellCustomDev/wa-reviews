<?php

namespace App\Livewire\Issues;

use App\Events\IssueChanged;
use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Scope;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewIssues extends Component
{
    public Scope $scope;

    public IssueForm $form;
    public int $editingId = 0;

    #[Computed(persist: true)]
    public function getIssues()
    {
        return $this->scope->issues()
            ->with([
                'guideline:id,number,name,criterion_id,category_id',
                'guideline.criterion:id,number,name,level',
                'guideline.category:id,name',
            ])
            ->get()
            ->sortBy(['guideline_id', 'guideline_instance']);
    }

    #[Computed(persist: true)]
    public function hasUnreviewedAI(): bool
    {
        return $this->getIssues
            ->filter(fn(Issue $issue) => $issue->hasUnreviewedAI())
            ->isNotEmpty();
    }

    #[On('issues-updated')]
    public function refreshScope(): void
    {
        unset($this->hasUnreviewedAI);
        unset($this->getIssues);
    }

    public function delete(Issue $issue): void
    {
        $this->authorize('delete', $issue);
        $issue->delete();

        event(new IssueChanged($issue, 'deleted', []));

        $this->dispatch('issues-updated');
    }

    public function acceptAI(Issue $issue): void
    {
        $this->authorize('update', $issue);
        $issue->markAiAccepted();

        $this->dispatch('issues-updated');
    }

    public function rejectAI(Issue $issue): void
    {
        $this->authorize('update', $issue);
        $issue->markAiRejected();
        $issue->delete();

        $this->dispatch('issues-updated');
    }
}
