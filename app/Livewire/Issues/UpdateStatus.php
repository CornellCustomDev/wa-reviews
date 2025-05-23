<?php

namespace App\Livewire\Issues;

use App\Enums\IssueStatus;
use App\Events\IssueChanged;
use App\Models\Issue;
use Livewire\Attributes\On;
use Livewire\Component;

class UpdateStatus extends Component
{
    public Issue $issue;
    public string $status;

    public function mount(Issue $issue): void
    {
        $this->issue = $issue;
        $this->status = $issue->status?->name ?? IssueStatus::Reviewed->name;
    }

    public function updateStatus(): void
    {
        $this->authorize('update-status', $this->issue);
        $this->dispatch('close-update-status');

        $this->issue->update([
            'status' => IssueStatus::fromName($this->status),
        ]);

        event(new IssueChanged($this->issue, 'status changed'));

        $this->dispatch('refresh-issue');
    }

    #[On('close-update-status')]
    public function closeUpdateStatus(): void
    {
        $this->modal('update-status')->close();
        $this->dispatch('reset-update-status');
    }
}
