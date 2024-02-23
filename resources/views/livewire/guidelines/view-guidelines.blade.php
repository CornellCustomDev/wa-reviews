<div>
    <h1>All Guidelines</h1>

    <table class="table bordered">
        <tbody>
            @foreach($guidelines as $guideline)
                <tr>
                    <th>
                        <x-forms.link-button route="{{ route('guidelines.show', $guideline) }}" title="{{ $guideline->number }}" />
                    </th>
                    <td>
                        <a href="{{ route('categories.show', $guideline->category) }}">
                            {{ $guideline->category->name }}
                        </a>
                    </td>
                    <td>
                        {{ $guideline->criterion->getLongName() }}
                    </td>
                    <td>
                        {{ $guideline->name }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
