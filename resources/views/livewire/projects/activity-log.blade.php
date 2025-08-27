<div>
    <flux:table class="table striped bordered" :paginate="$this->activities">
        <flux:table.columns>
            <flux:table.column class="w-48">
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
                    <flux:table.cell class="text-xs">
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
                            @case('added viewer')
                            @case('removed viewer')
                                <flux:badge size="sm" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">{{ $activity->delta['user_name'] }}</span>
                                @break
                            @default
                                <flux:badge size="sm" inset="top bottom">{{ $activity->action }}</flux:badge>
                                <span class="text-xs">on: {!! $this->subjectLink($activity) !!}</span>
                                @if(!empty($activity->delta))
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" class="relative top-[5px] -mt-3 -mb-1 text-cds-blue-600!"/>
                                        <flux:tooltip.content class="bg-cds-blue-200! text-cds-gray-950! min-w-[400px]">
                                            @foreach($activity->delta as $key => $val)
                                                <x-forms.field-display :label="ucfirst($key)" class="mb-0 text-cds-gray-950!">{!! $val !!}</x-forms.field-display>
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
