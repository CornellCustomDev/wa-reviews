<?php

namespace App\Livewire\SiaRules;

use App\Models\ActRule;
use App\Models\SiaRule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewSiaRules extends Component
{
    #[Computed('siaRules')]
    public function siaRules(): Collection
    {
        return SiaRule::select([
            'id',
            'alfa',
            'name',
            'name_html',
            'act_rule_id'
        ])->get();
    }

    #[Computed('actRules')]
    public function actRules(): Collection
    {
        return ActRule::all();
    }
}
