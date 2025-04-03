<div>
    <form wire:submit="save">
        <h3>Add a Report Viewer</h3>

        {{--    TODO: Search + add from LDAP --}}

        <flux:select label="User" wire:model="user" variant="listbox" searchable placeholder="Find user...">
            @foreach($this->nonReportViewers() as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->name }} ({{ $user->email }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Add Report Viewer
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
