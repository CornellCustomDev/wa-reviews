<?php

namespace App\Livewire\Issues;

use App\Enums\Assessment;
use App\Enums\Impact;
use App\Livewire\Features\SupportFileUploads\WithMultipleFileUploads;
use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\SiaRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateIssue extends Component
{
    use WithMultipleFileUploads;

    public IssueForm $form;
    public Scope $scope;
    public array $aiData = [];

    #[Url]
    public ?string $rule = null;
    #[Url]
    public ?string $guideline = null;

    public function mount(Scope $scope)
    {
        $this->scope = $scope;
        if ($this->guideline) {
            $this->form->guideline_id = $this->guideline;
        }
        if ($this->rule) {
            // Get the description from SiteimproveRule
            // $siteimproveRule = $this->rule->siteimproveRules->firstWhere('criterion_id', $this->guideline->criterion_id);
            $rule = SiaRule::find($this->rule);
            $this->form->description = $rule?->name_html; // $siteimproveRule->issues;
        }
    }

    public function save()
    {
        $this->authorize('create', [Issue::class, $this->scope->project]);
        $this->form->scope_id = $this->scope->id;
        $rule = $this->rule ? SiaRule::find($this->rule) : null;
        $issue = $this->form->store($this->scope->project, $rule, aiData: $this->aiData);

        return redirect()->route('issue.show', $issue);
    }

    #[Computed(persist: true)]
    public function getGuidelinesOptions()
    {
        return $this->form->getGuidelineSelectArray();
    }

    #[Computed(persist: true)]
    public function getIssueInstanceOptions(): array
    {
        return [];
    }

    #[On('analyze-issue')]
    public function analyzeIssue()
    {
        $this->dispatch('recommend-guidelines',
            target: $this->form->target,
            description: $this->form->description,
        );
    }

    #[On('populate-form')]
    public function populateForm($item)
    {
        // Update the form with the AI recommendations
        $this->form->guideline_id = $item['guideline_id'];
        $this->form->assessment = Assessment::fromName($item['assessment']);
        $this->form->recommendation = $item['recommendation'];
        $this->form->impact = Impact::fromName($item['impact']);
        $this->aiData = $item;
    }

    public function render()
    {
        return view('livewire.issues.create-issue')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->scope->project->name => route('project.show', $this->scope->project),
            $this->scope->title => route('scope.show', $this->scope),
            'Add Issue' => 'active'
        ];
    }
}
