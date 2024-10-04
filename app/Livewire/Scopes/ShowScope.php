<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowScope extends Component
{
    public Scope $scope;

    #[On('issues-updated')]
    public function refreshScope(): void
    {
        $this->scope->refresh();
    }

    public function render()
    {
        $this->authorize('view', $this->scope);

        return view('livewire.scopes.show-scope')
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
            $this->scope->title => 'active'
        ];
    }
}
