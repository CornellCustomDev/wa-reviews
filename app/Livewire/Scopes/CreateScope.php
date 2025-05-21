<?php

namespace App\Livewire\Scopes;

use App\Livewire\Forms\ScopeForm;
use App\Models\Project;
use App\Models\Scope;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateScope extends Component
{
    public ScopeForm $form;
    public Project $project;

    public function mount(Project $project): void
    {
        $this->project = $project;
        $url = request()->query('url');
        if ($url) {
            $this->form->url = $url;

            // Take a URL like https://sustainablecampus.cornell.edu/buildings-energy/solar-energy and turn it into "Solar Energy"
            $this->form->title = Str::of($url)->afterLast('/')->headline();
        }
    }

    public function save()
    {
        $this->authorize('create', [Scope::class, $this->project]);
        $scope = $this->form->store($this->project);

        return redirect()->route('scope.show', $scope);
    }

    public function render()
    {
        return view('livewire.scopes.create-scope')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->project->name => route('project.show', $this->project),
            'Add Scope' => 'active'
        ];
    }
}
