<div>
    <form wire:submit="save">
        <h3>Add a User</h3>

        <flux:select
            label="Person"
            wire:model="addUserEmail"
            variant="listbox"
            searchable
            :filter="false"
            placeholder="Select person..."
        >
            <x-slot name="search">
                <flux:select.search wire:ignore wire:model.live.debounce="search" placeholder="Search the Cornell directory..." />
            </x-slot>

            @forelse($this->nonTeamUsers() as $user)
                <flux:select.option value="{{ $user->email }}">
                    {{ $user->name }} ({{ $user->email }})
                </flux:select.option>
            @empty
                <flux:select.option value="" disabled>
                    No users found
                </flux:select.option>
            @endforelse
        </flux:select>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Add User
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
