<div>
    <p>Progress: {{ $this->completedPercentage }}% complete</p>

    <div x-data="{ open: false }">
        @if($scopeGuidelines->isEmpty())
            <button wire:click="generateGuidelines">Generate Guidelines</button>
        @else
            <div style="margin-bottom: 2em;">
                <button @click="open = !open"><span x-text="open ? 'Hide' : 'Show'"></span> Guidelines</button>
            </div>
        @endif
        <div x-show="open">
            <h3>Guidelines</h3>
            <table class="table bordered">
                <thead>
                <tr>
                    <th>Guideline
                        <select id="hasRules" wire:model.live="ruleTypes">
                            <option value="">All Guidelines</option>
                            <option value="automated">Automated rules</option>
                            <option value="manual">Manual check</option>
                        </select>
                        <select id="tool" wire:model.live="tool">
                            <option value="">All Tools</option>
                            @foreach(App\Enums\GuidelineTools::cases() as $tool)
                                <option value="{{ $tool->name }}">{{ $tool->name }}</option>
                            @endforeach
                    </th>
                    <th>Category
                        <select id="category" wire:model.live="category">
                            <option value="">All Categories</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                    @php($guideline = $scopeGuideline->guideline)
                    <tr wire:key="{{ $id }}">
                        <td>
                            {{ $guideline->number }}: {{ $guideline->name }}
                            ({{ $guideline->criterion->number }})
                            <button x-on:click="$dispatch('show-guideline', {number: {{ $guideline->number }} })" >Show</button><br>
                            @foreach($guideline->tools as $tool)
                                <span class="panel fill" style="font-size: 80%">{{ $tool }}</span>
                            @endforeach
                        </td>
                        <td>
                            {{ $guideline->category->name }}
                        </td>
                        <td>
                            <input type="checkbox" wire:click="toggleCompleted({{ $id }})" @checked($scopeGuideline->completed) />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


</div>
