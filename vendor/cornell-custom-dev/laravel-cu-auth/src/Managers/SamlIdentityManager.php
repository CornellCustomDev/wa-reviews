<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Managers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\AuthnRequest;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\ValidationError;

class SamlIdentityManager implements IdentityManager
{
    // Shibboleth fields generally available from either CIT or Weill IdPs.
    public const SAML_FIELDS = [
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

    public function hasIdentity(): bool
    {
        return ! empty($this->getIdentity());
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
     * @throws Exception
     */
    public function getSsoUrl(string $redirectUrl): string
    {
        try {
            $settings = new Settings(config('php-saml-toolkit'));
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
            $metadata = $settings->getSPMetadata();
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
        if (empty($attributes)) {
            try {
                $auth = new Auth(settings: config('php-saml-toolkit'));
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

        // Set IdP based on configured entityId
        $idpEntityId = config('php-saml-toolkit.idp.entityId');
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
