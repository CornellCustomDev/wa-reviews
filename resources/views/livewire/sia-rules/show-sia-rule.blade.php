<div>
    <x-forms.button.back class="float-right" :href="route('sia-rules.index')">All Siteimprove Alfa Rules</x-forms.button.back>

@php
    $classes = Flux::classes()
        ->add('[&_svg]:w-8')
        ->add('[&_svg]:inline-block')
        ->add('[&_svg]:mr-1')
        ->add('[&_h1>span]:block')
        ->add('[&_h1>span]:text-base')
        ->add('[&_h1>span]:font-sans')
        ->add('[&_h1>span]:font-semibold')
        ->add('[&_.message\_message]:mb-5')
        ->add('[&_.message\_message]:border')
        ->add('[&_.message\_message]:border-cds-gray-200')
        ->add('[&_.message\_message]:rounded-lg')
        ->add('[&_.message\_message]:p-5')
        ->add('[&_.message\_message]:bg-cds-gray-50')
        ->add('[&_.message\_message]:flex')
        ->add('[&_.message\_message]:items-center')
        ;
@endphp

    <flux:aside class="{{ $classes }}">
        {!! $rule->rule_html !!}
    </flux:aside>
</div>
