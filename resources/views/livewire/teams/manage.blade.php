<div>
    <h1>Teams and Users</h1>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="teams">Teams</flux:tab>
            @can('view-any', App\Models\User::class)
                <flux:tab name="users">Users</flux:tab>
                <flux:tab name="roles">Roles / Permissions</flux:tab>
            @endcan
        </flux:tabs>

        <flux:tab.panel name="teams">
            <livewire:teams.view-teams />
        </flux:tab.panel>
        @can('view-any', App\Models\User::class)
            <flux:tab.panel name="users">
                <livewire:teams.view-users />
            </flux:tab.panel>
            <flux:tab.panel name="roles">
                <livewire:teams.view-roles />
            </flux:tab.panel>
        @endcan
    </flux:tab.group>
</div>
