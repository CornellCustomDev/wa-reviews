<div>
    <h1>All Criteria in Guidelines</h1>

    <table class="table bordered">
        <thead>
            <tr>
                <th style="width: 100px;">Number</th>
                <th>Name</th>
                <th style="width: 70px;">Level</th>
            </tr>
        </thead>
        <tbody>
        @foreach($this->criteria as $criterion)
            <tr>
                <th>
                    <x-forms.button :href="route('criteria.show', $criterion)">{{ $criterion->number }}</x-forms.button>
                </th>
                <td>
                    <a href="{{ route('criteria.show', $criterion) }}">{{ $criterion->getLongName() }}</a>
                </td>
                <td>
                    {{ $criterion->level }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
