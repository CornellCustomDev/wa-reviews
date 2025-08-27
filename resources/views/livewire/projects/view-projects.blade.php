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

    <div class="mb-8 w-full max-[992px]:overflow-x-auto">
        <flux:tab.group>
            <flux:tabs wire:model.live="tab">
                @if($this->myProjects->total() > 0)
                    <flux:tab name="mine">My Projects ({{ $this->myProjects->total() }})</flux:tab>
                @endif
                <flux:tab name="active">Active ({{ $this->activeProjects->total() }})</flux:tab>
                <flux:tab name="completed">Completed ({{ $this->completedProjects->total() }})</flux:tab>
            </flux:tabs>

            @if($this->myProjects->total() > 0)
                <flux:tab.panel name="mine">
                    @include('livewire.projects.projects-list', ['projects' => $this->myProjects, 'pageName' => 'my-page'])
                </flux:tab.panel>
            @endif
            <flux:tab.panel name="active">
                @include('livewire.projects.projects-list', ['projects' => $this->activeProjects, 'pageName' => 'active-page'])
            </flux:tab.panel>
            <flux:tab.panel name="completed">
                @include('livewire.projects.projects-list', ['projects' => $this->completedProjects, 'pageName' => 'completed-page'])
            </flux:tab.panel>
        </flux:tab.group>
    </div>
</div>
