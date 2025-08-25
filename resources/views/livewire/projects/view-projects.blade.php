<div>
    @php($teamsWithPermission = $this->getTeamsWithCreateProjectPermission())
    @if($teamsWithPermission->count() == 1)
        <div class="float-right">
            <x-forms.button.add :href="route('teams.project.create', $teamsWithPermission->first())">
                Create New Project
            </x-forms.button.add>
        </div>
    @elseif($teamsWithPermission->count() > 1)
        <div class="float-right">
            <flux:dropdown>
                <x-forms.button icon="plus-circle">Create New Project</x-forms.button>
                <x-forms.menu>
                    @foreach ($teamsWithPermission as $team)
                        <x-forms.menu.item icon="plus" href="{{ route('teams.project.create', $team) }}">
                            {{ $team->name }}
                        </x-forms.menu.item>
                    @endforeach
                </x-forms.menu>
            </flux:dropdown>
        </div>
    @endif

    <h1>Projects</h1>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="active">Active ({{ count($this->activeProjects) }})</flux:tab>
            <flux:tab name="completed">Completed ({{ count($this->completedProjects) }})</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="active">
            @include('livewire.projects.projects-list', ['projects' => $this->activeProjects])
        </flux:tab.panel>
        <flux:tab.panel name="completed">
            @include('livewire.projects.projects-list', ['projects' => $this->completedProjects])
        </flux:tab.panel>
    </flux:tab.group>
</div>
