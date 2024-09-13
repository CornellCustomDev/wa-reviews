<div>
    <h1>All Guidelines</h1>

    <table class="table bordered">
        <thead>
            <tr>
                <th>Number</th>
                <th>Guideline</th>
                <th>Name</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guidelines as $guideline)
                <tr>
                    <th>
                        <x-forms.link-button route="{{ route('guidelines.show', $guideline) }}" title="{{ $guideline->number }}" />
                    </th>
                    <td>
                        {{ $guideline->name }}
                    </td>
                    <td>
                        <a href="{{ route('criteria.show', $guideline->criterion) }}">{{ $guideline->criterion->getLongName() }}</a>
                    </td>
                    <td>
                        <a href="{{ route('categories.show', $guideline->category) }}">
                            {{ $guideline->category->name }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
