<div>
    <h1>Manage Team: {{ $team->name }}</h1>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="members">Members</flux:tab>
            <flux:tab name="projects">Projects</flux:tab>
            @can('manage-project', $team)
                <flux:tab name="activity">Activity</flux:tab>
            @endcan
        </flux:tabs>

        <flux:tab.panel name="members">
            <livewire:teams.manage-team-users :team="$team" />
        </flux:tab.panel>
{{--        <flux:tab.panel name="projects">--}}
{{--            <livewire:teams.manage-team-projects :team="$team" />--}}
{{--        </flux:tab.panel>--}}
        @can('manage-project', $team)
            <flux:tab.panel name="activity">
                <livewire:teams.activity-log :team="$team" />
            </flux:tab.panel>
        @endcan
    </flux:tab.group>
</div>
