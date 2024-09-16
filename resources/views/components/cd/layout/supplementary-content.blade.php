<div class="band accent1 padded" role="complementary">
    <div class="container-fluid">
        <div class="row">
            <div class="primary">
                {{ $slot }}
            </div>
            @if ($secondary ?? false)
            <div class="secondary">
                {{ $secondary }}
            </div>
            @endif
        </div>
    </div>
</div>
