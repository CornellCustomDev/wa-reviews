<div class="cwd-component">
    <div class="align-right">
        <x-forms.link-button route="{{ route('guidelines.index') }}" title="All Guidelines" />
    </div>

    <div class="metadata-set metadata-blocks accent-red-dark">
        <h1>
            <a href="{{ $guideline->reference_url }}" target="_blank">
                <span class="deco fa fa-bookmark">
                    {{ $guideline->number }}
                </span>
            </a>
        </h1>
    </div>

    <h2>
        {{ $guideline->name }}
    </h2>

    <table class="table bordered">
        <tr>
            <th>Category</th>
            <td>
                <a href="{{ route('categories.show', $guideline->category) }}">
                    {{ $guideline->category->name }}
                </a>
            </td>
        </tr>
        <tr>
            <th>WGAC 2 criterion</th>
            <td>{{ $guideline->criterion->getLongName() }}</td>
        </tr>
        <tr>
            <th>Tools and requirements</th>
            <td></td>
        </tr>
        <tr>
            <th>Test procedure</th>
            <td>{!! $guideline->description !!}</td>
        </tr>
    </table>
</div>
