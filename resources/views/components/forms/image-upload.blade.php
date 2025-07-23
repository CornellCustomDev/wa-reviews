@props([
    'label',
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'existingImages' => [],
    'accept' => '.jpg,.jpeg,.png,.gif,.webp',
])

<div x-data="images" class="max-w-[600px]">
    <flux:input type="file" size="sm" :$label {{ $attributes }} multiple accept="{{ $accept }}" />

    <!-- Show previews of selected images -->
    @if ($existingImages || data_get($this, $name))
        <div class="flex flex-wrap gap-4 mt-2 mb-4">
            @foreach ($existingImages as $imagePath)
                @php($imageName = pathinfo($imagePath, PATHINFO_BASENAME))
                <div class="relative mt-2">
                    <div class="relative border border-cds-gray-900 p-px">
                        @php($fileType = pathinfo($imagePath, PATHINFO_EXTENSION))
                        @if(in_array($fileType, ['pdf', 'eml']))
                            <div class="max-w-60 bg-black/60 text-white text-center p-2">
                                <a href="{{ $imagePath }}" class="text-white" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-file"></i> {{ $imageName }}
                                </a>
                            </div>
                        @else
                            @if(in_array($fileType, ['mp4', 'webm']))
                                <video src="{{ $imagePath }}" class="max-w-60" controls></video>
                            @else
                                <img
                                    src="{{ $imagePath }}"
                                    alt="Preview of image: {{ $imageName }}"
                                    class="max-w-60"
                                />
                            @endif
                            <div class="absolute bottom-0 left-0 w-full bg-black/60 text-white text-center p-1">
                                {{ $imageName }}
                            </div>
                        @endif
                    </div>
                    <flux:button
                        variant="danger"
                        size="xs"
                        class="absolute! top-2 right-2"
                        icon="x-mark"
                        tooltip="Remove {{ $imageName }}"
                        x-on:click="removeExistingImage('{{ $imageName }}')"
                    />
                </div>
            @endforeach
            @foreach (data_get($this, $name) as $index => $file)
                <div class="relative mt-2">
                    <div class="relative border border-cds-gray-900 p-px">
                        @if($file->isPreviewable())
                            <img
                                src="{{ $file->temporaryUrl() }}"
                                alt="Preview of image: {{ $file->getClientOriginalName() }}"
                                class="max-w-60"
                            />
                            <div class="absolute bottom-0 left-0 w-full bg-black/60 text-white text-center p-1">
                                {{ $file->getClientOriginalName() }}
                            </div>
                        @else
                            <div class="max-w-60 bg-black/60 text-white text-center p-2">
                                {{ $file->getClientOriginalName() }}
                            </div>
                        @endif
                    </div>
                    <flux:button
                        variant="danger"
                        size="xs"
                        class="absolute! top-2 right-2"
                        icon="x-mark"
                        tooltip="Remove {{ $file->getClientOriginalName() }}"
                        x-on:click="removeImage('{{ $file->getFilename() }}')"
                    />
                </div>
            @endforeach
        </div>
    @endif

    <div wire:loading.delay wire:target="form.images">Uploading...</div>
</div>

@script
<script>
    Alpine.data('images', () => {
        return {
            removeImage(name) {
                // For newly uploaded images
                $wire.removeUpload('{{ $name }}', name);
                $wire.$el.querySelector('[x-ref="name"]').textContent = 'Image removed (temporary)';
            },
            removeExistingImage(name) {
                $wire.dispatch('remove-existing-image', { filename: name });
                // $wire.$refresh();
                $wire.$el.querySelector('[x-ref="name"]').textContent = 'Image removed (existing)';
            },
        }
    });
</script>
@endscript
