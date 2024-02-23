<div>
    <h1>Categories</h1>

    <p>
        The checklist provides categories for each testing guideline.  These are meant to help identify relevant criteria as you are evaluating a site, application, or document.  They are broken down as follows.
    </p>

    <ul>
        @foreach($categories as $category)
            <li><b><a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a></b>: {{ $category->description }}</li>
        @endforeach
    </ul>

</div>
