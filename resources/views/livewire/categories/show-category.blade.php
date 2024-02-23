<div>
    <h1>Category: {{ $category->name }}</h1>
    <p>{{ $category->description }}</p>

    <table class="table bordered">
        <caption><b>Related Guidelines</b></caption>
        <tbody>
        @foreach($guidelines as $guideline)
            <tr>
                <th>
                    <x-forms.link-button route="{{ route('guidelines.show', $guideline) }}" title="{{ $guideline->number }}" />
                </th>
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
