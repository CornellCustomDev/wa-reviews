<div>
    <h1>{{ $this->project->name }}: Add Scope</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Title" wire:model="form.title" />
        <x-cd.form.text label="URL" wire:model="form.url" />

        <div 
            class="form-field mb-4" 
            wire:ignore 
            x-data="{
            value: @entangle('form.notes'),
            isFocused(){ return document.activeElement !== this.$refs.trix },
            setValue(){ this.$refs.trix.form.notes.loadHTML(this.value) },
            }"
            x-init="setValue(); $watch('value', () => isFocused() && setValue())"
        >
            <label for="notes">Notes</label>
            <input type="hidden" id="notes"  value="{{$form->notes}}" wire:model="form.notes"/>
            <div 
                x-on:trix-change="value = $event.target.value"
                x-on:trix-blur="value = $event.target.value"
            >
                <trix-editor input="notes"></trix-editor>
            </div>
        </div>
        <br>
        <input type="submit" value="Add Scope">
        <a href="{{ route('project.show', $this->project) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
