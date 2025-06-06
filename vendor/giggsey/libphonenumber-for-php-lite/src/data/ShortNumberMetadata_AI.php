<?php

/**
 * libphonenumber-for-php-lite data file
 * This file has been @generated from libphonenumber data
 * Do not modify!
 * @internal
 */

declare(strict_types=1);

namespace libphonenumber\data;

use libphonenumber\PhoneMetadata;
use libphonenumber\PhoneNumberDesc;

/**
 * @internal
 */
class ShortNumberMetadata_AI extends PhoneMetadata
{
    protected const ID = 'AI';
    protected const COUNTRY_CODE = 0;

    protected ?string $internationalPrefix = '';

    public function __construct()
    {
        $this->generalDesc = (new PhoneNumberDesc())
            ->setNationalNumberPattern('[19]\d\d')
            ->setPossibleLength([3]);
        $this->premiumRate = PhoneNumberDesc::empty();
        $this->tollFree = (new PhoneNumberDesc())
            ->setNationalNumberPattern('9(?:11|88)')
            ->setExampleNumber('911');
        $this->emergency = (new PhoneNumberDesc())
            ->setNationalNumberPattern('911')
            ->setExampleNumber('911');
        $this->short_code = (new PhoneNumberDesc())
            ->setNationalNumberPattern('176|9(?:11|88)')
            ->setExampleNumber('176');
        $this->standard_rate = PhoneNumberDesc::empty();
        $this->carrierSpecific = (new PhoneNumberDesc())
            ->setNationalNumberPattern('176')
            ->setExampleNumber('176');
        $this->smsServices = (new PhoneNumberDesc())
            ->setNationalNumberPattern('176')
            ->setExampleNumber('176');
    }
}
