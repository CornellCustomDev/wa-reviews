<?php

namespace App\Livewire\Projects;

use App\Exports\ProjectReportExport;
use App\Models\Project;
use App\Models\Scope;
use App\Services\GoogleApi\GoogleService;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Report extends Component
{
    public Project $project;
    public ?string $selectedImage = null;

    public function exportReport(): BinaryFileResponse
    {
        return Excel::download(
            export: new ProjectReportExport($this->project),
            fileName: 'project-report-'.Str::slug($this->project->name).'.xlsx',
        );
    }

    public function exportGoogleSheet(GoogleService $googleService)
    {
        $spreadsheetId = $googleService->createTestSheet();

        if (!$spreadsheetId) {
            // How do we trigger google.oauth from here and then get back to this function?
            return redirect()->route('google.oauth');
        }

        session()->flash('message', 'Sheet created: https://docs.google.com/spreadsheets/d/' . $spreadsheetId);

        // Go to the sheet now
        return redirect()->away('https://docs.google.com/spreadsheets/d/' . $spreadsheetId);
    }

    #[Computed]
    public function issues()
    {
        return $this->project->getReportableIssues()
            ->groupBy('scope_id')
            ->sortKeys();
    }

    #[Computed(persist: true)]
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

    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.projects.report')
            ->layout('components.layouts.app', [
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->project->name => route('project.show', $this->project),

            'Report' => 'active',
        ];
    }
}
