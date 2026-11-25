<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Uri;
use InvalidArgumentException;

class AuthController extends BaseController
{
    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function login(Request $request)
    {
        $redirectUrl = $this->localUrlOnly($request->query->getString('redirect_url'));

        $remoteIdentity = $this->identityManager->getIdentity();
        if ($remoteIdentity) {
            return redirect()->to($redirectUrl);
        }

        // Use the SSO login URL
        $ssoUrl = $this->identityManager->getSsoUrl($redirectUrl);

        return redirect($ssoUrl);
    }

    /**
     * The IdP selection page, for php-saml with idp_selection_mode = page.
     */
    public function select(Request $request)
    {
        $identityManager = $this->identityManager;
        if (! $this->identityManager instanceof SamlIdentityManager) {
            abort(404);
        }

        /** @var SamlIdentityManager $identityManager */
        if ($identityManager->idpSelectionMode() !== SamlIdentityManager::IDP_SELECTION_PAGE) {
            abort(404);
        }

        $redirectUrl = $this->localUrlOnly($request->query->getString('redirect_url'));

        if ($identityManager->getIdentity()) {
            return redirect()->to($redirectUrl);
        }

        return view(config('cu-auth.idp_selection.page.view'), [
            'redirectUrl' => $redirectUrl,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $returnUrl = $this->localUrlOnly($request->query->getString('return'));

        $sloUrl = $this->identityManager->getSloUrl($returnUrl);

        return redirect($sloUrl);
    }

    public function acs(Request $request)
    {
        try {
            $this->identityManager->storeIdentity();
        } catch (Exception $e) {
            return response($e->getMessage(), 403);
        }

        // Redirect to the originally intended URL
        $returnUrl = $this->localUrlOnly($this->identityManager->getSsoReturnUrl($request));

        return redirect()->to($returnUrl);
    }

    public function metadata(Request $request)
    {
        $metadata = $this->identityManager->getMetadata();

        if (empty($metadata)) {
            return response('Metadata not available.', 404);
        }

        return response($metadata)->withHeaders([
            'Content-Type' => 'text/xml',
        ]);
    }

    /**
     * The given URL as an absolute URL on this site, to prevent open redirects; otherwise the fallback URL.
     */
    protected function localUrlOnly(string $url, string $fallback = '/'): string
    {
        $fallbackUrl = secure_url($fallback);
        if (blank($url)) {
            return $fallbackUrl;
        }

        $absoluteUrl = secure_url($url);
        try {
            // Unparseable URLs get the fallback URL
            $absoluteUri = Uri::of($absoluteUrl);
        } catch (InvalidArgumentException) {
            return $fallbackUrl;
        }

        $isValidLocalUrl = $absoluteUri->scheme() === 'https'
            && $absoluteUri->host() === Uri::of(secure_url('/'))->host()
            && $absoluteUri->user() === null;

        return $isValidLocalUrl ? $absoluteUrl : $fallbackUrl;
    }
}
