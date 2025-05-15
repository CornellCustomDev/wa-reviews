<?php

namespace App\Livewire\Issues;

use App\AiAgents\PopulateGuidelinesAgent;
use App\Models\Issue;
use Illuminate\Support\Str;
use Livewire\Component;

class IssueAnalyzer extends Component
{
    public Issue $issue;
    public bool $showFeedback = false;
    public string $feedback;

    // TODO: Stream feedback on actions instead of waiting for the whole response, see IssueChat
    public function populateGuidelines(): void
    {
        $this->authorize('update', $this->issue);

        $this->feedback = '';

        // TODO: Determine how we want to key these, probably somehow back to the calling chat?
        $agent = new PopulateGuidelinesAgent($this->issue, Str::ulid());

        $response = $agent->respond($agent->getContext());

        $this->feedback = $response;
        $this->showFeedback = true;

        $this->dispatch('items-updated');
    }
}
