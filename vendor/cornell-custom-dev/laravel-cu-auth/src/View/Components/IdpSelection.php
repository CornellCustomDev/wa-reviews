<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\View\Components;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Sign-in buttons for the configured IdPs, shown only in page mode.
 */
class IdpSelection extends Component
{
    public function __construct(
        public ?string $heading = null,
        public ?string $redirectUrl = null,
    ) {
        $this->heading ??= config('cu-auth.idp_selection.page.heading');
    }

    public function shouldRender(): bool
    {
        $identityManager = app(IdentityManager::class);

        return $identityManager instanceof SamlIdentityManager
            && $identityManager->idpSelectionMode() === SamlIdentityManager::IDP_SELECTION_PAGE;
    }

    public function render(): View
    {
        /** @var SamlIdentityManager $identityManager */
        $identityManager = app(IdentityManager::class);
        $redirectUrl = $this->redirectUrl ?? request()->query('redirect_url', '/');
        $redirectUrl = is_string($redirectUrl) ? $redirectUrl : '/';

        return view('cu-auth::components.idp-selection', [
            'idps' => $identityManager->idpChoices($redirectUrl),
        ]);
    }
}
