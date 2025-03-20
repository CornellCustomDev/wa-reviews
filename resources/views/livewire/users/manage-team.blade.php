<div>
    <h1>Manage Team: {{ $team->name }}</h1>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="members" :accent="false">Members</flux:tab>
{{--            <flux:tab name="projects" :accent="false">Projects</flux:tab>--}}
        </flux:tabs>

        <flux:tab.panel name="members" class="pt-6!">
            <livewire:users.manage-team-users :team="$team" />
        </flux:tab.panel>
{{--        <flux:tab.panel name="projects" class="pt-6!">--}}
{{--            <livewire:users.manage-team-projects :team="$team" />--}}
{{--        </flux:tab.panel>--}}
    </flux:tab.group>
</div>
