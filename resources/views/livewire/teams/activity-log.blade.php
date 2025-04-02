<div>
    <flux:table :paginate="$this->activities" >
        <flux:table.columns>
            <flux:table.column
                sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                wire:click="sort('created_at')"
                class="w-48"
            >
                Date
            </flux:table.column>
            <flux:table.column class="w-48">
                By
            </flux:table.column>
            <flux:table.column>
                Action
            </flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->activities as $activity)
                <flux:table.row :key="$activity->id">
                    <flux:table.cell class="whitespace-nowrap" class="text-xs">
                        {{ $activity->created_at->format('D, M j, Y g:i A') }}
                    </flux:table.cell>
                    <flux:table.cell class="text-xs">
                        {{ $activity->actor->name }}
                    </flux:table.cell>
                    <flux:table.cell class="text-xs align-middle">
                        @switch($activity->action)
                            @case('roles updated')
                                <flux:badge size="sm" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">for {{ $activity->delta['user_name'] }} to: </span>
                                @forelse(Str::of($activity->delta['roles'])->explode(', ')->filter() as $role)
                                    <flux:badge size="sm" variant="solid" inset="top bottom">{{ $role }}</flux:badge>
                                @empty
                                    None
                                @endforelse
                                @break
                            @default
                                <flux:badge size="sm" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">{!! $this->subjectLink($activity) !!}</span>
                                @if(!empty($activity->delta))
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" class="relative top-[5px] -mt-3 -mb-1 text-cds-blue-600!" />
                                        <flux:tooltip.content class="bg-cds-blue-200! text-cds-gray-950! min-w-[400px]">
                                            @foreach($activity->delta as $key => $val)
                                                <x-forms.field-display :label="ucfirst($key)" class="mb-0 text-cds-gray-950!">{!! collect($val)->join(', ') !!}</x-forms.field-display>
                                            @endforeach
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                @endif
                        @endswitch
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
