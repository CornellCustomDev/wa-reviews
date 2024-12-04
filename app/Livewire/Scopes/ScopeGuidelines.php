<?php
namespace App\Livewire\Scopes;

use App\Enums\GuidelineTools;
use App\Models\Category;
use App\Models\Scope;
use App\Models\ScopeGuideline;
use App\Models\ScopeRule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ScopeGuidelines extends Component
{
    public Scope $scope;
    public Collection $scopeGuidelines;

    #[Url(as: 'rt')]
    public $ruleTypes = '';
    #[Url(as: 't')]
    public $tool = '';
    #[Url(as: 'c')]
    public $category = '';
    #[Url(as: 'd')]
    public $completed = '';
    #[Url(as: 'g')]
    public $guideline = '';
    #[Url(as: 'show')]
    public $showGuidelines = false;

    public ?string $response;

    public function mount()
    {
        $this->loadScopeGuidelines();
    }

    private function loadScopeGuidelines(): void
    {
        $this->scopeGuidelines = $this->scope->guidelines()
            ->with(['guideline', 'guideline.criterion'])
            ->orderBy('guideline_id')
            ->get()
            ->mapWithKeys(function (ScopeGuideline $scopeGuideline) {
                // TODO Deal with this in the query
                $scopeGuideline->guideline->notes = null;
                return [$scopeGuideline->id => $scopeGuideline];
            });
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::all();
    }

    #[Computed]
    public function tools(): array
    {
        return GuidelineTools::cases();
    }

    #[Computed]
    public function completedPercentage(): int
    {
        $count = $this->scopeGuidelines
            ->filter(fn ($g) => $g->completed)
            ->count();

        if ($count === 0) {
            return 0;
        }

        return round($count / $this->scopeGuidelines->count() * 100);
    }

    #[Computed]
    public function filteredGuidelines(): Collection
    {
        return $this->scopeGuidelines
            ->filter(function ($scopeGuideline) {
                return empty($this->tool)
                    || $scopeGuideline->guideline->tools->contains(GuidelineTools::tryFrom($this->tool));
            })
            ->filter(function ($scopeGuideline) {
                return empty($this->category) || $scopeGuideline->guideline->category_id == $this->category;
            })
            ->filter(function ($scopeGuideline) {
                return empty($this->completed) || $scopeGuideline->completed == ($this->completed === 'true');
            })
            ->filter(function ($scopeGuideline) {
                if ($this->ruleTypes === 'automated') {
                    return $scopeGuideline->guideline->hasAutomatedAssessment()
                        || $this->applicableRules->has($scopeGuideline->guideline->id);
                }
                if ($this->ruleTypes === 'manual') {
                    return !$scopeGuideline->guideline->hasAutomatedAssessment()
                        && !$this->applicableRules->has($scopeGuideline->guideline->id);
                }
                return true;
            });
    }

    #[Computed]
    public function applicableRules(): Collection
    {
        $rules = $this->scope->rules->fresh();

        return $rules->mapToGroups(function (ScopeRule $rule) {
            return [$rule->guideline_id => $rule];
        });
    }

    public function generateGuidelines(): void
    {
        $this->scope->generateScopeGuidelines();
        $this->loadScopeGuidelines();
    }

    public function toggleCompleted($id): void
    {
        $this->scopeGuidelines[$id]->update([
            'completed' => !$this->scopeGuidelines[$id]->completed,
        ]);
        unset($this->completedPercentage);
        unset($this->filteredGuidelines);
    }

    public function updated(): void
    {
        unset($this->filteredGuidelines);
    }

    #[On('scope-rules-updated')]
    public function loadScopeRules(): void
    {
        unset($this->applicableRules);
    }

    #[On('show-guideline')]
    public function showGuideline($number): void
    {
        $this->guideline = $number;
    }

    #[On('complete-siteimprove')]
    public function completeSiteimprove(): void
    {
        $this->scope->guidelines()
            ->whereHas('guideline', fn ($q) => $q->whereJsonContains('tools', GuidelineTools::Siteimprove))
            ->update(['completed' => true]);
        $this->loadScopeGuidelines();

        unset($this->completedPercentage);
        unset($this->filteredGuidelines);
    }
}
