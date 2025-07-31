<?php

namespace App\Livewire\Scopes;

use App\Models\Project;
use App\Models\Scope;
use App\Models\ScopeGuideline;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewScopes extends Component
{
    public Project $project;

    #[Computed]
    public function scopes(): Collection
    {
        return $this->project->scopes()
            ->with(['issues:id,scope_id,guideline_id,assessment', 'issues.guideline:id,number'])
            ->get();
    }

    #[Computed]
    public function scopesProgress(): Collection
    {
        $scopeGuidelines = ScopeGuideline::whereIn('scope_id', $this->scopes->pluck('id'))->get();

        return $scopeGuidelines->groupBy('scope_id')
            ->mapWithKeys(function ($group) {
                $count = $group->filter(fn($g) => $g->completed)->count();
                $total = $group->count();
                $progress = round($total ? $count / $total * 100 : 0).'%';
                return [$group->first()->scope_id => $progress];
            });
    }

    #[On('refresh-scopes')]
    public function refreshScopes(): void
    {
        unset($this->scopes);
    }

    public function delete(Scope $scope): void
    {
        $this->authorize('update', $scope->project);
        $scope->delete();
    }
}
