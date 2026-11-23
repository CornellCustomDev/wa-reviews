<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Saml;

use DOMDocument;
use Exception;
use OneLogin\Saml2\Constants;
use OneLogin\Saml2\Response;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Utils;
use OneLogin\Saml2\ValidationError;

/**
 * SP support for the SAML Identity Provider Discovery Service Protocol, beyond what
 * the OneLogin PHP SAML Toolkit provides.
 */
class IdpDiscovery
{
    public const NS_IDPDISC = 'urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol';

    /**
     * SP metadata with an idpdisc:DiscoveryResponse extension (SAML IdP Discovery Protocol).
     *
     * @param  string  $discoveryResponseUrl  Where the discovery service returns the user
     *
     * @throws Exception
     */
    public static function getDiscoveryMetadata(Settings $settings, string $discoveryResponseUrl): string
    {
        // Signing would have to happen after the extension is added, which getSPMetadata() does not allow.
        if ($settings->getSecurityData()['signMetadata']) {
            throw new Exception('signMetadata is not supported with IdP discovery.');
        }

        $dom = new DOMDocument;
        Utils::loadXML($dom, $settings->getSPMetadata());
        $spSsoDescriptor = $dom->getElementsByTagNameNS(Constants::NS_MD, 'SPSSODescriptor')->item(0);

        $extensions = $dom->createElementNS(Constants::NS_MD, 'md:Extensions');
        $discoveryResponse = $dom->createElementNS(self::NS_IDPDISC, 'idpdisc:DiscoveryResponse');
        $discoveryResponse->setAttribute('Binding', self::NS_IDPDISC);
        $discoveryResponse->setAttribute('Location', $discoveryResponseUrl);
        $discoveryResponse->setAttribute('index', '1');
        $extensions->appendChild($discoveryResponse);
        $spSsoDescriptor->insertBefore($extensions, $spSsoDescriptor->firstChild);

        return $dom->saveXML();
    }

    /**
     * Read the (unvalidated) Issuer from a base64-encoded SAML Response.
     *
     * @param  Settings  $settings  PHP SAML Toolkit settings (only the SP settings are needed)
     *
     * @throws Exception if the Response cannot be read or does not have exactly one Issuer
     */
    public static function getResponseIssuer(Settings $settings, ?string $samlResponse): string
    {
        if (empty($samlResponse)) {
            throw new ValidationError('SAML Response not found');
        }

        $response = new Response($settings, $samlResponse);
        $issuers = $response->getIssuers();
        if (count($issuers) !== 1) {
            throw new ValidationError('SAML Response and Assertion issuers do not match');
        }

        return trim($issuers[0]);
    }
}
