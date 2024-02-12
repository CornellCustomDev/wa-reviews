<div>
    <div class="cwd-component align-right">
        @can('create', [App\Models\Review::class, $project])
            <x-forms.link-button route="{{ route('reviews.create', $project) }}" title="Create Review" />
        @endcan
        <x-forms.link-button route="{{ route('projects.show', $project) }}" title="Back to Project" />
    </div>

    <h1>{{ $project->name }}: Reviews</h1>

    <form wire:submit.prevent>

    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Target</th>
            <th>Description</th>
            <th>Recommendation</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reviews as $review)
            <tr wire:key="{{ $review->id }}">
                <td x-on:click="await $wire.edit({{ $review->id }}); $nextTick(() => { $refs.form_target.focus() })" style="cursor:pointer">
                    <div x-show="$wire.editingId != '{{ $review->id }}'">
                        {{ $review->target }}
                    </div>
                    <div x-show="$wire.editingId == '{{ $review->id }}'">
                        <label for="target" class="sr-only">Target</label>
                        <input
                            id="target"
                            type="text" wire:model="form.target" x-on:blur="$wire.save()" x-ref="form_target"
                            {{--                            wire:keyup.tab="next({{ $review->id }})"--}}
                            @class(['panel accent-red' => $errors->has('form.target')])
                            @error('form.target')
                            aria-invalid="true"
                            aria-description="{{ $message }}"
                            @enderror
                        />
                        @error('form.target')
                        <div>
                            <span class="error">{{ $message }}</span>
                        </div>
                        @enderror
                        {{--                        <span wire:loading>Saving...</span>--}}
                    </div>
                </td>
                <td>{{ $review->description }}</td>
                <td>{{ $review->recommendation }}</td>
                <td class="text-nowrap">
                    <x-forms.link-button route="{{ route('reviews.show', [$review->project, $review]) }}" title="View Review {{ $review->id }}">
                        <span class="zmdi zmdi-eye" style="margin-right: 0" />
                    </x-forms.link-button>
                    @can('update', $review)
                    <x-forms.link-button route="{{ route('reviews.edit', [$review->project, $review]) }}" title="Edit Review {{ $review->id }}">
                        <span class="zmdi zmdi-edit" style="margin-right: 0" />
                    </x-forms.link-button>
                    @endcan
                    @can('delete', $review)
                    <x-forms.link-button route="#" title="Delete Review {{ $review->id }}"
                        wire:click.prevent="delete({{ $review->id }})"
                        wire:confirm="Are you sure you want to delete Review {{ $review->id }}?"
                    >
                        <span class="zmdi zmdi-delete" style="margin-right: 0" />
                    </x-forms.link-button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    </form>
</div>
