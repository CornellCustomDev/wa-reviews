<div>
    <h1>Update Review Item</h1>

    <h2>Review</h2>
    <table class="table bordered">
        <tr>
            <th>
                Target
            </th>
            <td>
                {{ $review->target }}
            </td>
        </tr>
        <tr>
            <th>
                Description
            </th>
            <td>
                {{ $review->description }}
            </td>
        </tr>
    </table>

    <form wire:submit="save">
        @include('livewire.review-items.fields')

        <input type="submit" value="Save Review Item">
        <a href="{{ route('reviews.show', [$review->project, $review]) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
