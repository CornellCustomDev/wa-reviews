<div>
    <h1>User Management</h1>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="users" :accent="false">Users</flux:tab>
            <flux:tab name="teams" :accent="false">Teams</flux:tab>
{{--            <flux:tab name="roles" :accent="false">Roles</flux:tab>--}}
{{--            <flux:tab name="permissions" :accent="false">Permissions</flux:tab>--}}
        </flux:tabs>

        <flux:tab.panel name="users" class="pt-6!">
            <livewire:users.view-users />
        </flux:tab.panel>
        <flux:tab.panel name="teams" class="pt-6!">
            <livewire:users.view-teams />
        </flux:tab.panel>
{{--        <flux:tab.panel name="roles" class="pt-6!">--}}
{{--            <livewire:roles.view-roles />--}}
{{--        </flux:tab.panel>--}}
{{--        <flux:tab.panel name="permissions" class="pt-6!">--}}
{{--            <livewire:permissions.view-permissions />--}}
{{--        </flux:tab.panel>--}}
    </flux:tab.group>
</div>
