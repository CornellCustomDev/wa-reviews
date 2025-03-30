<div>
    <flux:table :paginate="$this->activities">
        <flux:table.columns>
            <flux:table.column
                sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                wire:click="sort('created_at')"
            >
                Date
            </flux:table.column>
            <flux:table.column>
                Action
            </flux:table.column>
            <flux:table.column>
                By
            </flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->activities as $activity)
                <flux:table.row :key="$activity->id">
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $activity->created_at->format('Y-m-d h:ia') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @switch($activity->action)
                            @case('status changed')
                                <flux:badge size="sm" inset="top bottom">{{ $activity->action }}</flux:badge>
                                to:
                                <flux:badge size="sm" variant="solid" color="{{ $this->statusColor($activity->delta['status']) }}" inset="top bottom">{{ $activity->delta['status'] }}</flux:badge>
                                @break
                            @case('assigned')
                                <flux:badge size="sm" color="green" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">to: {{ $activity->delta['user_name'] }}</span>
                                @break
                            @case('unassigned')
                                <flux:badge size="sm" color="zinc" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">from: {{ $activity->delta['user_name'] }}</span>
                                @break
                            @default
                                <flux:badge size="sm" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">on: {!! $this->subjectLink($activity) !!}</span>
                                @if(!empty($activity->delta))
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" />
                                        <flux:tooltip.content class="bg-cds-blue-200! text-cds-gray-950! min-w-[400px]">
                                            @foreach($activity->delta as $key => $val)
                                                <x-forms.field-display :label="ucfirst($key)" class="mb-0 text-cds-gray-950!">{!! $val !!}</x-forms.field-display>
                                            @endforeach
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                @endif
                        @endswitch
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $activity->actor->name }}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
