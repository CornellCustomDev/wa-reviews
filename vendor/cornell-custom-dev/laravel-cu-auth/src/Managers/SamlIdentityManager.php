<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Managers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Saml\IdpDiscovery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\AuthnRequest;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\ValidationError;

class SamlIdentityManager implements IdentityManager
{
    // Values for config('cu-auth.idp_selection_mode')
    public const string IDP_SELECTION_SINGLE = 'single';

    public const string IDP_SELECTION_DISCOVERY = 'discovery';

    public const string IDP_SELECTION_PAGE = 'page';

    // Shibboleth fields generally available from either CIT or Weill IdPs.
    public const array SAML_FIELDS = [
        // staff|student|...
        'eduPersonPrimaryAffiliation' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.5',

        // John R. Doe [CIT only]
        'cn' => 'urn:oid:2.5.4.3',

        // netid@cornell.edu
        'eduPersonPrincipalName' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',

        // John
        'givenName' => 'urn:oid:2.5.4.42',

        // Doe
        'sn' => 'urn:oid:2.5.4.4',

        // John Doe
        'displayName' => 'urn:oid:2.16.840.1.113730.3.1.241',

        // netid
        'uid' => 'urn:oid:0.9.2342.19200300.100.1.1',

        // o=Cornell University,c=US [CIT only]
        'eduPersonOrgDN' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.3',

        // alias? email
        'mail' => 'urn:oid:0.9.2342.19200300.100.1.3',

        // ['employee', 'staff', ...] [CIT only]
        'eduPersonAffiliation' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.1',

        // [employee@cornell.edu, staff@cornell.edu, ...]
        'eduPersonScopedAffiliation' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.9',

        // ? [CIT only]
        'eduPersonEntitlement' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.7',

        // Web Developer [Weill only]
        'title' => 'urn:oid:2.5.4.12',
    ];

    public function hasRemoteIdentity(): bool
    {
        return ! empty($this->getIdentity());
    }

    /** {@inheritDoc} */
    public function hasIdentity(): bool
    {
        return $this->hasRemoteIdentity();
    }

    public function getIdentity(): ?RemoteIdentity
    {
        /** @var RemoteIdentity|null $remoteIdentity */
        $remoteIdentity = session()->get('remoteIdentity');

        return $remoteIdentity;
    }

    /**
     * @throws Exception
     */
    public function storeIdentity(): ?RemoteIdentity
    {
        $remoteIdentity = $this->retrieveIdentity();
        session()->put('remoteIdentity', $remoteIdentity);

        return $remoteIdentity;
    }

    /**
     * How users get an IdP: IDP_SELECTION_SINGLE (the one IdP in config/php-saml-toolkit.php,
     * also used when no mode is set), IDP_SELECTION_DISCOVERY, or IDP_SELECTION_PAGE.
     *
     * @throws Exception if the configured mode is not one of these
     */
    public function idpSelectionMode(): string
    {
        $mode = config('cu-auth.idp_selection_mode');

        // Default to single if not set.
        if (blank($mode)) {
            return self::IDP_SELECTION_SINGLE;
        }

        if (! in_array($mode, [self::IDP_SELECTION_SINGLE, self::IDP_SELECTION_DISCOVERY, self::IDP_SELECTION_PAGE], true)) {
            throw new Exception('Invalid IdP selection mode.');
        }

        return $mode;
    }

    /**
     * Whether users choose between IdPs, rather than using the single IdP in config/php-saml-toolkit.php.
     */
    public function selectsIdp(): bool
    {
        return $this->idpSelectionMode() !== self::IDP_SELECTION_SINGLE;
    }

    /**
     * Keys of the IdPs users can choose in the current mode.
     *
     * @return list<string>
     */
    public function idpKeys(): array
    {
        return match ($this->idpSelectionMode()) {
            self::IDP_SELECTION_DISCOVERY => config('cu-auth.idp_selection.discovery.idps', []),
            self::IDP_SELECTION_PAGE => array_keys(config('cu-auth.idp_selection.page.idps', [])),
            default => [],
        };
    }

    /**
     * @throws Exception
     */
    public function getSsoUrl(string $redirectUrl): string
    {
        $samlSettings = config('php-saml-toolkit');
        $mode = $this->idpSelectionMode();
        if ($mode !== self::IDP_SELECTION_SINGLE) {
            $idpEntityId = request()->query('entityID');
            $idpSettings = $this->idpSettings($idpEntityId);

            // No IdP chosen yet, so send the user to choose one.
            if (empty($idpEntityId)) {
                // Check the SP settings before the user chooses an IdP, not after.
                $spSettings = $this->spSettings();

                return $mode === self::IDP_SELECTION_PAGE
                    ? $this->getSelectionPageUrl($redirectUrl)
                    : $this->getDiscoveryUrl($redirectUrl, $spSettings->getSPData()['entityId']);
            }
            $samlSettings['idp'] = $idpSettings;
        }

        try {
            $settings = new Settings($samlSettings);
        } catch (Exception $e) {
            throw new Exception('Invalid SAML settings: '.$e->getMessage());
        }
        $authRequest = new AuthnRequest($settings);

        $url = $settings->getIdPData()['singleSignOnService']['url'];
        $query = Arr::query([
            'SAMLRequest' => $authRequest->getRequest(),
            'RelayState' => $redirectUrl,
        ]);

        return $url.'?'.$query;
    }

    /**
     * URL for the SAML discovery service (SAMLDS), which returns the user to
     * the login route with the chosen IdP added as the entityID parameter.
     */
    public function getDiscoveryUrl(string $redirectUrl, string $entityId): string
    {
        $url = config('cu-auth.idp_selection.discovery.url');
        $query = Arr::query([
            'entityID' => $entityId,
            'return' => route('cu-auth.sso-login', ['redirect_url' => $redirectUrl]),
        ]);

        return $url.(str_contains($url, '?') ? '&' : '?').$query;
    }

    /**
     * URL for the IdP selection page, which links to the login route with
     * each IdP's entityID parameter.
     */
    public function getSelectionPageUrl(string $redirectUrl): string
    {
        return route('cu-auth.sso-select', ['redirect_url' => $redirectUrl]);
    }

    /**
     * The configured IdPs to choose from, in config order. Each has its display
     * info from config and a login URL that goes straight to that IdP.
     *
     * @return list<array{url: string, label: string, info_text: ?string, info_url: ?string}>
     *
     * @throws Exception if any configured IdP is missing a valid settings file
     */
    public function idpChoices(string $redirectUrl): array
    {
        $choices = [];
        foreach ($this->configuredIdpSettings() as $key => $idpSettings) {
            $display = config("cu-auth.idp_selection.page.idps.$key");
            $choices[] = [
                'url' => route('cu-auth.sso-login', [
                    'entityID' => $idpSettings['entityId'],
                    'redirect_url' => $redirectUrl,
                ]),
                'label' => $display['label'] ?? $key,
                'info_text' => $display['info_text'] ?? null,
                'info_url' => $display['info_url'] ?? null,
            ];
        }

        return $choices;
    }

    /**
     * Settings for the IdP with the given entityId, or null if no entityId is given.
     * Every configured IdP's settings file is read and checked on each call.
     *
     * @throws Exception if any configured IdP is missing a valid settings file,
     *                   or the entityId is not one of the configured IdPs
     */
    public function idpSettings(?string $idpEntityId): ?array
    {
        $settings = array_column($this->configuredIdpSettings(), null, 'entityId');

        // Wait to check until here so we validate all configured IdPs first.
        if (empty($idpEntityId)) {
            return null;
        }

        return $settings[$idpEntityId] ?? throw new Exception('Unknown identity provider.');
    }

    /**
     * Settings for every configured IdP, read from idp_<key>.json and keyed by <key>.
     *
     * @throws Exception if any configured IdP is missing a valid settings file
     */
    private function configuredIdpSettings(): array
    {
        $settings = [];
        $certPath = config('cu-auth.cert-path');
        foreach ($this->idpKeys() as $key) {
            $file = $certPath.'/idp_'.$key.'.json';
            $idpSettings = File::exists($file) ? json_decode(File::get($file), true) : null;
            if (empty($idpSettings['entityId'])) {
                throw new Exception("Missing or invalid IdP settings for $key. Run `php artisan cu-auth:generate-keys`.");
            }
            $settings[$key] = $idpSettings;
        }

        return $settings;
    }

    /**
     * PHP SAML Toolkit settings, checking only the SP settings.
     *
     * @throws Exception
     */
    private function spSettings(): Settings
    {
        try {
            return new Settings(config('php-saml-toolkit'), true);
        } catch (Exception $e) {
            throw new Exception('Invalid SAML settings: '.$e->getMessage());
        }
    }

    public function getSsoReturnUrl(Request $request): string
    {
        return $request->input('RelayState', '/');
    }

    public function getSloUrl(string $returnUrl): string
    {
        return $returnUrl;
    }

    /**
     * @throws Exception
     */
    public function getMetadata(): ?string
    {
        try {
            $settings = new Settings(config('php-saml-toolkit'), true);
            $metadata = $this->idpSelectionMode() === self::IDP_SELECTION_DISCOVERY
                ? IdpDiscovery::getDiscoveryMetadata($settings, route('cu-auth.sso-login'))
                : $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);
        } catch (Exception $e) {
            throw new Exception('Invalid SP metadata: '.$e->getMessage());
        }

        if (! empty($errors)) {
            throw new Exception('Invalid SP metadata: '.implode(', ', $errors));
        }

        return $metadata;
    }

    /**
     * @throws Exception
     */
    public function retrieveIdentity(?array $attributes = null): RemoteIdentity
    {
        $idpEntityId = config('php-saml-toolkit.idp.entityId');

        if (empty($attributes)) {
            $samlSettings = config('php-saml-toolkit');
            if ($this->selectsIdp()) {
                // The issuer is not trusted yet; it only selects which IdP's settings
                // (and certificate) the response must validate against.
                $idpEntityId = IdpDiscovery::getResponseIssuer(new Settings($samlSettings, true), request()->post('SAMLResponse'));
                $samlSettings['idp'] = $this->idpSettings($idpEntityId);
            }

            try {
                $auth = new Auth($samlSettings);
                $auth->processResponse();
            } catch (Error|ValidationError $e) {
                throw new Exception('SAML Response Error: '.$e->getMessage());
            }

            $errors = $auth->getErrors();
            if (! empty($errors)) {
                throw new Exception('SAML Response Errors: '.implode(', ', $errors));
            }
            if (! $auth->isAuthenticated()) {
                throw new Exception('SAML Response not authenticated');
            }

            $attributes = $auth->getAttributesWithFriendlyName() ?: $auth->getAttributes();
        }

        // Set IdP based on the entityId
        $idp = match (true) {
            Str::contains($idpEntityId, 'weill'),
            Str::contains($idpEntityId, 'med.cornell.edu') => 'weill.cornell.edu',
            default => 'cit.cornell.edu',
        };

        $mappedAttributes = $this->mapAttributes($attributes);

        return RemoteIdentity::fromData(
            idp: $idp,
            uid: $mappedAttributes['uid'] ?? '',
            data: $attributes,
            cn: $mappedAttributes['cn'],
            givenName: $mappedAttributes['givenName'],
            sn: $mappedAttributes['sn'],
            displayName: $mappedAttributes['displayName'],
            eduPersonPrincipalName: $mappedAttributes['eduPersonPrincipalName'],
            mail: $mappedAttributes['mail'],
        );
    }

    /**
     * Map SAML attributes to self::SAML_FIELDS we expect.
     */
    private function mapAttributes(array $attributes): array
    {
        $mappedAttributes = [];
        foreach (self::SAML_FIELDS as $key => $oid) {
            // Prefer friendly name, fallback to OID
            $mappedAttributes[$key] = Arr::get($attributes, $key, Arr::get($attributes, $oid))[0] ?? null;
        }

        return $mappedAttributes;
    }
}
