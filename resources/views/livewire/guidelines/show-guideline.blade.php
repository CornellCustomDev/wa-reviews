<div class="cwd-component">
    <div class="align-right">
        <x-forms.button.back :href="route('guidelines.index')">All Guidelines</x-forms.button.back>
    </div>

    <div class="metadata-set metadata-blocks accent-red-dark">
        <h1>
            <a href="{{ $guideline->reference_url }}" target="_blank">
                <span class="deco fa fa-bookmark">
                    {{ $guideline->getNumber() }}
                </span>
            </a>
        </h1>
    </div>

    <x-forms.edit-wrapper>
        <x-slot:view>
            <h2>
                {{ $guideline->name }}
            </h2>

            <table class="table bordered">
                <tr>
                    <th style="width: 200px">WCAG 2 criterion</th>
                    <td><a href="{{ route('criteria.show', [$guideline->criterion]) }}">{{ $guideline->criterion->getLongName() }}</a></td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>
                        <a href="{{ route('categories.show', $guideline->category) }}">
                            {{ $guideline->category->name }}
                        </a>
                    </td>
                </tr>
{{--                <tr class="hidden">--}}
{{--                    <th>ACT Rules</th>--}}
{{--                    <td>--}}
{{--                        @if($guideline->actRules->isNotEmpty())--}}
{{--                            <ul>--}}
{{--                                @foreach($guideline->actRules as $rule)--}}
{{--                                    <li><a href="{{ route('act-rules.show', $rule) }}">{{ $rule->name }}</a></li>--}}
{{--                                @endforeach--}}
{{--                            </ul>--}}
{{--                        @endif--}}
{{--                    </td>--}}
{{--                </tr>--}}
            </table>

            {!! Str::markdown($guideline->notes) !!}
        </x-slot:view>

        @can('update', $guideline)
            <x-slot:edit>
                <livewire:guidelines.edit-guideline :$guideline />
            </x-slot:edit>
        @endcan
    </x-forms.edit-wrapper>

</div>

{{-- Sidebar for AI help --}}
<x-slot name="sidebarPrimary">
    <livewire:guidelines.guideline-chat :$guideline />
</x-slot>
