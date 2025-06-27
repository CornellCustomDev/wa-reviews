<?php

namespace App\Livewire\Issues;

use Livewire\Attributes\On;
use Livewire\Component;

class ConfirmRecommendation extends Component
{
    public ?string $guidelineNumber = null;

    #[On('show-confirm-recommendation')]
    public function confirmRecommendation($guidelineNumber): void
    {
        $this->guidelineNumber = $guidelineNumber;
        $this->modal('confirm-recommendation')->show();
    }

    #[On('close-confirm-recommendation')]
    public function closeConfirmRecommendation(): void
    {
        $this->guidelineNumber = null;
        $this->modal('confirm-recommendation')->close();
    }
}
