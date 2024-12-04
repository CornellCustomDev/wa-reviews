<?php

namespace App\Livewire\SiteimproveRules;

use App\Models\SiteimproveRule;
use Livewire\Component;

class ViewSiteimproveRules extends Component
{
    public function render()
    {
        return view('livewire.siteimprove-rules.view-siteimprove-rules', [
            'rules' => SiteimproveRule::with('criterion')->get()->sortBy('rule_id'),
        ]);
    }
}
