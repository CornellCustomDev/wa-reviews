<?php

namespace App\Livewire\Projects;

use App\Events\ProjectChanged;
use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

class UpdateStatus extends Component
{
    public Project $project;

    public function removeReviewer(): void
    {
        $this->authorize('update-reviewer', $this->project);

        $this->project->unassign();

        $this->dispatch('refresh-project');
    }

    public function updateStatus(string $direction): void
    {
        $this->authorize('update-status', $this->project);

        $this->dispatch('close-update-status');

        switch ($direction) {
            case 'next':
                $this->project->update([
                    'status' => $this->project->status->nextStatus(),
                    'completed_at' => $this->project->status->nextStatus()->isCompleted() ? now() : null,
                ]);
                break;
            case 'previous':
                $this->project->update([
                    'status' => $this->project->status->previousStatus(),
                    'completed_at' => null,
                ]);
                break;
            default:
                throw new \InvalidArgumentException("Invalid direction: $direction");
        }

        event(new ProjectChanged($this->project, 'status changed'));

        $this->dispatch('refresh-project');
    }

    #[On('close-update-reviewer')]
    public function closeUpdateReviewer(): void
    {
        $this->modal('update-reviewer')->close();
        $this->dispatch('reset-update-reviewer');
    }

    #[On('close-update-status')]
    public function closeUpdateStatus(): void
    {
        $this->modal('update-status')->close();
        $this->dispatch('reset-update-status');
    }
}
