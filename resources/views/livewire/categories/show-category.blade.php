<div>
    <h1>Category: {{ $category->name }}</h1>
    <p>{{ $category->description }}</p>

    <table class="table bordered">
        <caption><b>Related Guidelines</b></caption>
        <thead>
            <tr>
                <th scope="col">Guideline</th>
                <th scope="col">Criterion</th>
                <th scope="col">Name</th>
            </tr>
        </thead>
        <tbody>
        @foreach($guidelines as $guideline)
            <tr>
                <th>
                    <x-forms.button :href="route('guidelines.show', $guideline)">{{ $guideline->number }}</x-forms.button>
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
