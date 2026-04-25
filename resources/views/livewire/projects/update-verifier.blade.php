<div>
    <form wire:submit="save">
        <h3>Assign a Verifier</h3>

        <flux:select label="Verifier" wire:model="user" placeholder="Select a team member..." variant="combobox">
            @foreach($this->nonAssignedVerifiers as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->name }} ({{ $user->email }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Assign Verifier
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
