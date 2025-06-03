<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\User;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapSearch;
use Exception;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddReportViewer extends Component
{
    public Project $project;
    public string $search = '';
    #[Validate('required', as: 'person')]
    public string $addReviewerEmail;

    #[Computed]
    public function nonReportViewers(): array
    {
        // If there is a search term, search LDAP
        try {
            $searchFilter = "(|(uid=$this->search*)(displayname=*$this->search*)(mail=$this->search*))";
            $result = (Str::of($this->search)->length() > 3) ? LdapSearch::search($searchFilter) : null;
            $ldapUsers = collect($result)
                ->filter(fn ($ldapData) => $ldapData->email)
                ->mapWithKeys(fn (LdapData $ldapData) => [
                    $ldapData->principalName() => new User([
                        'name' => $ldapData->name(),
                        'email' => $ldapData->email(),
                    ])
                ]);
        } catch (Exception $e) {
            $ldapUsers = collect();
        }
        $users = User::query()
            // ->whereDoesntHave('teams', fn ($q) => $q->where('team_id', $this->project->team->id))
            // and not in report viewers already
            ->whereNotIn('id', [...$this->project->reportViewers->pluck('id'), $this->project->reviewer?->id])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('uid', 'like', '%' . $this->search . '%');
                });
            })
            ->get()
            ->mapWithKeys(fn ($user) => [
                $user->uid => $user,
            ]);

        return $ldapUsers->merge($users)->sortBy('name')->all();
    }

    public function updatedSearch(): void
    {
        unset($this->nonReportViewers);
    }

    public function save()
    {
        $this->authorize('update-report-viewers', $this->project);

        $this->validate();

        $newReviewer = User::firstWhere('email', $this->addReviewerEmail);

        // if the user isn't found, but they are in LDAP, create a new user
        if (empty($newReviewer)) {
            $email = $this->addReviewerEmail;
            $ldapData = LdapSearch::getByEmail($email);
            if ($ldapData) {
                $newReviewer = User::createUserFromLdapData($ldapData);
            } else {
                $this->addError('addReviewerEmail', "$this->addReviewerEmail not found");
                return;
            }
        }

        $this->project->addReportViewer($newReviewer);

        unset($this->nonReportViewers);
        $this->dispatch('close-add-report-viewer');
    }

    #[On('refresh-report-viewers')]
    public function refreshReportViewers(): void
    {
        unset($this->nonReportViewers);
    }

    #[On('reset-add-report-viewer')]
    public function resetAddReportReviewer(): void
    {
        unset($this->addReviewerEmail);
        $this->resetValidation();
    }
}
