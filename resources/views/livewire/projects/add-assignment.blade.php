<div>
    <form wire:submit="save">
        <h3>Assign a Team Member</h3>

        <flux:select label="Team Member" wire:model="user" placeholder="Select a team member..." variant="listbox">
            @foreach($this->nonAssignedMembers as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->name }} ({{ $user->email }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Assign
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
