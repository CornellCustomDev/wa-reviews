<div>
    <h1>Teams and Users</h1>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="teams" :accent="false">Teams</flux:tab>
            @can('viewAny', App\Models\User::class)
                <flux:tab name="users" :accent="false">Users</flux:tab>
                <flux:tab name="roles" :accent="false">Roles / Permissions</flux:tab>
            @endcan
        </flux:tabs>

        <flux:tab.panel name="teams" class="pt-6!">
            <livewire:teams.view-teams />
        </flux:tab.panel>
        @can('viewAny', App\Models\User::class)
            <flux:tab.panel name="users" class="pt-6!">
                <livewire:teams.view-users />
            </flux:tab.panel>
            <flux:tab.panel name="roles" class="pt-6!">
                <livewire:teams.view-roles />
            </flux:tab.panel>
        @endcan
    </flux:tab.group>
</div>
