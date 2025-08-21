<div>
    <form wire:submit="save">
        <h3>Assign a Reviewer</h3>

        <flux:select label="Reviewer" wire:model="user" placeholder="Select a team member..." variant="combobox">
            @foreach($this->nonAssignedMembers as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->name }} ({{ $user->email }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Assign Reviewer
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
