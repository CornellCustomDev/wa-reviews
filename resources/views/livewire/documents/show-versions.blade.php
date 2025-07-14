<div>
    <h3>Versions</h3>
    <ul>
        @foreach($this->versions as $version)
            <li class="inline-flex items-center gap-2">
                {{ $version->created_at->toDayDateTimeString()  }}
                @if($version->is_current)
                    <flux:icon.star variant="mini" class="text-yellow-500 inline-flex items-center" />
                @else
                    <x-forms.button
                        wire:click="makeCurrentVersion({{ $version->id }})"
                        icon="star"
                        size="xs"
                        title="Make this version current"
                        class="inline-flex items-center"
                    />
                @endif
            </li>
        @endforeach
    </ul>
</div>
