<x-forms.field-display label="Target" variation="inline">
    Describe <strong>what</strong> exactly is causing the issue.
</x-forms.field-display>
<x-forms.field-display label="Description" variation="inline">
    Describe <strong>why</strong> there is an issue.
</x-forms.field-display>


<div class="expander">
    <h4>Assessment</h4>
    <div>
        @foreach(\App\Enums\Assessment::cases() as $case)
            <div data-cds-field-display class="mb-1.5!">
                <flux:heading>
                    {{ $case->getDescription() }}
                </flux:heading>
                <flux:text>
                    {{ $case->getLongDescription() }}
                </flux:text>
            </div>
        @endforeach
    </div>

    <h4>Impact</h4>
    <div>
        @foreach(\App\Enums\Impact::cases() as $case)
            <div data-cds-field-display class="mb-1.5!">
                <flux:heading>
                    {{ $case->value() }}
                </flux:heading>
                <flux:text>
                    {{ $case->getLongDescription() }}
                </flux:text>
            </div>
        @endforeach
    </div>
</div>
