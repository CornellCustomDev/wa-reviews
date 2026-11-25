<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Commands;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OneLogin\Saml2\IdPMetadataParser;
use OneLogin\Saml2\Settings;

class GenerateKeys extends Command
{
    protected $signature = 'cu-auth:generate-keys {--force : Overwrite keys if they already exist} {--weill : Use Weill Cornell IdP instead of CIT IdP}';

    protected $description = 'Generate public and private keys for SAML authentication, retrieve public key.';

    public function handle(): void
    {
        $force = $this->option('force');
        $weill = $this->option('weill');

        $certPath = config('cu-auth.cert-path');
        File::ensureDirectoryExists($certPath);

        $identityManager = new SamlIdentityManager;
        if ($identityManager->selectsIdp()) {
            $this->storeSelectionIdpSettings($certPath, $force, $identityManager->idpKeys());
        } else {
            $this->storeIdpCert($certPath, $force, $weill);
        }

        $this->storeSpKeys($certPath, $force);

        $this->info('Keys generated successfully.');
    }

    private function storeIdpCert(string $certPath, bool $force, bool $weill): void
    {
        $idpKey = $weill ? 'weill' : 'cornell';
        $idpCertPath = $certPath.'/idp_cert.pem';
        $idpMultiCertPath = $certPath.'/idp_cert_multi.json';
        if ($force || (! File::exists($idpCertPath) && ! File::exists($idpMultiCertPath))) {
            // Remove both cert files, since we don't know which one was previously used (single vs multi cert)
            File::exists($idpCertPath) && File::delete($idpCertPath);
            File::exists($idpMultiCertPath) && File::delete($idpMultiCertPath);

            $this->info('Downloading IDP certificate...');
            $idpSettings = $this->getIdpSettings($idpKey);
            if (! empty($idpSettings['x509certMulti'])) {
                File::put($idpMultiCertPath, json_encode($idpSettings['x509certMulti'], JSON_PRETTY_PRINT));
            } else {
                File::put($idpCertPath, $idpSettings['x509cert']);
            }
        } else {
            $this->info('IDP certificate already exists.');
        }
    }

    private function storeSelectionIdpSettings(string $certPath, bool $force, array $idpKeys): void
    {
        foreach ($idpKeys as $idpKey) {
            $idpSettingsPath = $certPath.'/idp_'.$idpKey.'.json';
            if ($force || ! File::exists($idpSettingsPath)) {
                $this->info("Downloading IDP settings for $idpKey...");
                $idpSettings = $this->getIdpSettings($idpKey);
                $this->validateIdpSettings($idpKey, $idpSettings);
                File::put($idpSettingsPath, json_encode($idpSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->info("IDP settings for $idpKey already exist.");
            }
        }
    }

    private function storeSpKeys(string $certPath, bool $force): void
    {
        $spKeyPath = $certPath.'/sp_key.pem';
        $spCertPath = $certPath.'/sp_cert.pem';
        if ($force || ! File::exists($spKeyPath) || ! File::exists($spCertPath)) {
            $this->info('Generating service provider key pair...');
            $config = [
                'digest_alg' => 'sha256',
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ];

            // Generate a new private key
            $key = openssl_pkey_new($config);
            openssl_pkey_export_to_file($key, $spKeyPath);

            // Generate a certificate signing request (CSR)
            $csr = openssl_csr_new([], $key, $config);

            // Self-sign the CSR to create a certificate valid for 10 years
            $days = now()->diffInDays(now()->addYears(10));
            $certificate = openssl_csr_sign($csr, null, $key, $days, $config);
            openssl_x509_export_to_file($certificate, $spCertPath);
        } else {
            $this->info('Service provider key pair already exists.');
        }
    }

    private function getIdpSettings(string $key): array|false
    {
        return app()->runningUnitTests()
            ? [ // Placeholder content for testing
                'entityId' => "https://$key.test/idp",
                'singleSignOnService' => ['url' => "https://$key.test/idp/sso"],
                'x509cert' => "test-$key-idp-cert-contents",
            ]
            : IdPMetadataParser::parseRemoteXML(config("cu-auth.idp_metadata_urls.$key"))['idp'];
    }

    /**
     * Check the entityId, SSO URL, and signing certificate(s) with the PHP SAML Toolkit,
     * so an IdP whose metadata cannot be used is not saved.
     *
     * @throws Exception
     */
    private function validateIdpSettings(string $key, array|false $idpSettings): void
    {
        // Only the SP fields the toolkit requires, so missing SP keys (generated later) don't block this.
        $sp = config('php-saml-toolkit.sp');
        $samlSettings = [
            'sp' => [
                'entityId' => $sp['entityId'],
                'assertionConsumerService' => $sp['assertionConsumerService'],
            ],
            'idp' => $idpSettings,
        ];
        $errors = is_array($idpSettings)
            ? (new Settings($samlSettings, true))->checkIdPSettings($samlSettings)
            : ['idp_metadata_not_found'];
        if (! empty($errors)) {
            $metadataUrl = config("cu-auth.idp_metadata_urls.$key");
            throw new Exception("Invalid IdP settings for $key from $metadataUrl: ".implode(', ', $errors));
        }
    }
}
