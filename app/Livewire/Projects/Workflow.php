<?php

namespace App\Livewire\Projects;

use App\Events\ProjectChanged;
use App\Models\Project;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Workflow extends Component
{
    public Project $project;

    #[Computed]
    public function statusDescription(): string
    {
        if ($this->project->status->isNotStarted() && $this->project->reviewer) {
            return "Are you ready to have {$this->project->reviewer->name} start work on the project?";
        }

        if (! Feature::active('verification-reviews') && $this->project->status->isReviewComplete()) {
            return "The review is complete, but you can re-open it if you need to make changes.";
        }

        return $this->project->status->description();
    }

    #[Computed]
    public function nextActionLabel(): ?string
    {
        return $this->project->status->nextActionLabel();
    }

    #[Computed]
    public function previousActionLabel(): ?string
    {
        return $this->project->status->previousActionLabel();
    }

    public function assignCurrentUser(): void
    {
        $this->authorize('update-reviewer', [$this->project, auth()->user()]);
        $this->project->assignToUser(auth()->user());
        $this->dispatch('refresh-project');
    }

    public function removeReviewer(): void
    {
        $this->authorize('update-reviewer', $this->project);
        $this->project->unassign();
        $this->dispatch('refresh-project');
    }

    public function assignCurrentUserAsVerifier(): void
    {
        $this->authorize('update-verifier', [$this->project, auth()->user()]);
        $this->project->assignVerifier(auth()->user());
        $this->dispatch('refresh-project');
    }

    public function removeVerifier(): void
    {
        $this->authorize('update-verifier', $this->project);
        $this->project->unassignVerifier();
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
                    'completed_at' => $this->project->status->nextStatus()->isReviewComplete()
                        ? now()
                        : $this->project->completed_at,
                ]);
                break;
            case 'previous':
                $this->project->update([
                    'status' => $this->project->status->previousStatus(),
                    'completed_at' => $this->project->status->previousStatus()->isPostReview()
                        ? $this->project->completed_at
                        : null,
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

    #[On('close-update-verifier')]
    public function closeUpdateVerifier(): void
    {
        $this->modal('update-verifier')->close();
        $this->dispatch('reset-update-verifier');
    }

    #[On('close-update-status')]
    public function closeUpdateStatus(): void
    {
        $this->modal('update-status')->close();
        $this->dispatch('reset-update-status');
    }
}
