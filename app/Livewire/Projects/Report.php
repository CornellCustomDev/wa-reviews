<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Scope;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Report extends Component
{
    public Project $project;
    public ?string $selectedImage = null;

    #[Computed]
    public function issues()
    {
        return $this->project->issues()->with(['scope', 'items'])->get()->groupBy('scope_id');
    }

    #[Computed('siteimproveUrl')]
    public function siteimproveUrl(Scope $scope): string
    {
        return SiteimproveService::getPageReportUrlForScope($scope);
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
