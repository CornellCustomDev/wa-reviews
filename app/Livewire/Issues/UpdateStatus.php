<?php

namespace App\Livewire\Issues;

use App\Enums\IssueStatus;
use App\Events\IssueChanged;
use App\Models\Issue;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UpdateStatus extends Component
{
    public Issue $issue;

    #[Validate('required')]
    public IssueStatus $status = IssueStatus::Reviewed;
    #[Validate('boolean|nullable')]
    public bool $needs_mitigation = false;

    public function mount(Issue $issue): void
    {
        $this->issue = $issue;
        $this->status = $issue->status ?? IssueStatus::Reviewed;
        $this->needs_mitigation = $issue->needs_mitigation ?? false;
    }

    public function updateStatus(): void
    {
        $this->authorize('update-status', $this->issue);

        $updates = $this->validate();
        $this->dispatch('close-update-status');

        if (! auth()->user()->can('update-needs-mitigation', $this->issue)) {
            unset($updates['needs_mitigation']);
        }

        $this->issue->update($updates);

        event(new IssueChanged($this->issue, 'issue status changed', [
            'status' => $this->issue->status,
            'needs_mitigation' => $this->issue->needs_mitigation,
        ]));

        $this->dispatch('refresh-issue');
    }

    #[On('close-update-status')]
    public function closeUpdateStatus(): void
    {
        $this->modal('update-status')->close();
        $this->dispatch('reset-update-status');
    }
}
