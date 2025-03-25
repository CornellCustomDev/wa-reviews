<div>
    <form wire:submit="save">
        <div class="mb-4">
            <flux:heading size="lg">Add User</flux:heading>
            <flux:subheading>Add a user to this team.</flux:subheading>
        </div>

        {{--    TODO: Search + add from LDAP --}}

        <flux:select wire:model="user" variant="listbox" searchable placeholder="Find user...">
            @foreach($this->nonTeamUsers() as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->name }} ({{ $user->email }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Add User
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
