<div>
    <p>Progress: {{ $this->completedPercentage }}% complete</p>

    <div x-data="{ open: $wire.entangle('showGuidelines') }">
        @if($scopeGuidelines->isEmpty())
            <button x-on:click="$wire.generateGuidelines; open = true">Generate Guidelines</button>
        @else
            <div style="float: right">
                <button x-on:click="open = !open"><span x-text="open ? 'Hide' : 'Show'"></span> Guidelines</button>
            </div>
            <h3>Guidelines</h3>
        @endif
        <div x-show="open">
            <p>Showing {{ count($this->filteredGuidelines) }} of {{ $scopeGuidelines->count() }} guidelines. Click on a guideline to view more details.</p>
            <table class="table bordered">
                <thead>
                <tr>
                    <th>Guideline
                        <select id="hasRules" wire:model.live="ruleTypes">
                            <option value="">All Guidelines</option>
                            <option value="automated">Automated</option>
                            <option value="manual">Manual</option>
                        </select>
                        <select id="category" wire:model.live="category">
                            <option value="">All Categories</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select id="tool" wire:model.live="tool">
                            <option value="">All Tools</option>
                            @foreach(App\Enums\GuidelineTools::cases() as $tool)
                                <option value="{{ $tool->name }}">{{ $tool->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>Completed
                        <select id="status" wire:model.live="completed">
                            <option value="">All</option>
                            <option value="true">Completed</option>
                            <option value="false">Incomplete</option>
                        </select>
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($this->filteredGuidelines as $id => $scopeGuideline)
                    @php
                        /** @var App\Models\Guideline $guideline */
                        $guideline = $scopeGuideline->guideline
                    @endphp
                    <tr wire:key="{{ $id }}">
                        <td x-data @click="$dispatch('show-guideline', {number: {{ $guideline->number }} })" style="cursor: pointer;">
                            {{ $guideline->number }}: {{ $guideline->name }}
                            ({{ $guideline->criterion->number }})
                            <br>
                            Tools:
                            @foreach($guideline->tools as $tool)
                                <span class="panel fill" style="font-size: 80%">{{ $tool }}</span>
                            @endforeach
                        </td>
                        <td>
                            <input type="checkbox" wire:click="toggleCompleted({{ $id }})" @checked($scopeGuideline->completed) />
                            @if($guideline->hasAutomatedAssessment())
                                (Automated)
                            @endif
                            @if($this->applicableRules->has($scopeGuideline->guideline->id))
                                (AI review)
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


</div>
