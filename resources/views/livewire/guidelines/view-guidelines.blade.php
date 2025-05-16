<div>
    <h1>All Guidelines</h1>

    <table class="table bordered">
        <thead>
            <tr>
                <th>Number</th>
                <th>Guideline</th>
                <th>Category</th>
                <th>Tools</th>
            </tr>
        </thead>
        <tbody>
            @foreach($this->guidelines as $guideline)
                <tr>
                    <th>
                        <x-forms.button size="sm" :href="route('guidelines.show', $guideline)">
                            {{ $guideline->getNumber() }}
                        </x-forms.button>
                    </th>
                    <td>
                        {{ $guideline->name }}
                    </td>
                    <td>
                        <a href="{{ route('categories.show', $guideline->category) }}">
                            {{ $guideline->category->name }}
                        </a>
                    </td>
                    <td style="white-space: nowrap">
                        @foreach($guideline->tools as $tool)
                            {{ $tool }}<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
