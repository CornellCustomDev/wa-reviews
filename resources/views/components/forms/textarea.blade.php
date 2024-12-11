@props([
    'label',
    'id' => $attributes->whereStartsWith('wire:model')->first(),
    'toolbar' => 'heading bold italic underline | bullet ordered blockquote | link code ~ undo redo',
])
<flux:editor :$label :$attributes class="max-w-[600px] border-cds-gray-400 border-b-gray-400 mb-4">
    <flux:editor.toolbar class="border-b-cds-gray-200">
        <flux:editor.heading />
        <flux:editor.separator />
        <flux:editor.bold />
        <flux:editor.italic />
        <flux:editor.strike />
        <flux:editor.separator />
        <flux:editor.bullet />
        <flux:editor.ordered />
        <flux:editor.blockquote />
        <flux:editor.separator />
        <flux:editor.link />
        <flux:editor.code />

        <flux:editor.spacer />

        <flux:editor.undo />
        <flux:editor.redo />
    </flux:editor.toolbar>

    <flux:editor.content />
</flux:editor>
