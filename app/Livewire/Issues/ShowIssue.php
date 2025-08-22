<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowIssue extends Component
{
    public Issue $issue;
    public ?string $selectedImage = null;

    #[Url(as: 'e', history: true)]
    public bool $showEdit = false;

    #[On('items-updated')]
    public function refreshIssue(): void
    {
        $this->issue->refresh();
    }

    #[Computed('siteimproveUrl')]
    public function siteimproveUrl(): string
    {
        return SiteimproveService::getPageReportUrlForScope($this->issue->scope);
    }

    public function render()
    {
        $this->authorize('view', $this->issue);
        if ($this->showEdit === true && !Gate::allows('update', $this->issue)) {
            $this->showEdit = false;
        }

        return view('livewire.issues.show-issue')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        $issueIdentifier = $this->issue->guideline_id
            ? $this->issue->getGuidelineInstanceNumber()
            : $this->issue->id;

        return [
            'Projects' => route('projects'),
            $this->issue->project->name => route('project.show', $this->issue->project),
            ...($this->issue->scope ? [$this->issue->scope->title => route('scope.show', $this->issue->scope)] : []),
            "Viewing Issue $issueIdentifier" => 'active'
        ];
    }


    #[Computed]
    public function hasUnreviewedAI(): bool
    {
        return $this->issue->hasUnreviewedAI();
    }

    public function acceptAI(): void
    {
        $this->authorize('update', $this->issue);
        $this->issue->markAiAccepted();

        $this->dispatch('items-updated');
    }

    public function rejectAI(): void
    {
        $this->authorize('update', $this->issue);
        $this->issue->markAiRejected();

        $this->issue->delete();

        // Go to the scope page after deleting the issue
        // @TODO: Redirect to the scope page after deleting the issue
    }

    public function viewImage(string $imageUrl): void
    {
        $this->selectedImage = $imageUrl;
        $this->modal('view-image')->show();
    }

    public function closeImage(): void
    {
        $this->modal('view-image')->close();
        $this->selectedImage = null;
    }
}
