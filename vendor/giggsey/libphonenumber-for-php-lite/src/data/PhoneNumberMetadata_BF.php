<?php

/**
 * libphonenumber-for-php-lite data file
 * This file has been @generated from libphonenumber data
 * Do not modify!
 * @internal
 */

declare(strict_types=1);

namespace libphonenumber\data;

use libphonenumber\NumberFormat;
use libphonenumber\PhoneMetadata;
use libphonenumber\PhoneNumberDesc;

/**
 * @internal
 */
class PhoneNumberMetadata_BF extends PhoneMetadata
{
    protected const ID = 'BF';
    protected const COUNTRY_CODE = 226;

    protected ?string $internationalPrefix = '00';

    public function __construct()
    {
        $this->generalDesc = (new PhoneNumberDesc())
            ->setNationalNumberPattern('(?:[025-7]\d|44)\d{6}')
            ->setPossibleLength([8]);
        $this->mobile = (new PhoneNumberDesc())
            ->setNationalNumberPattern('(?:0[1-7]|44|5[0-8]|[67]\d)\d{6}')
            ->setExampleNumber('70123456');
        $this->premiumRate = PhoneNumberDesc::empty();
        $this->fixedLine = (new PhoneNumberDesc())
            ->setNationalNumberPattern('2(?:0(?:49|5[23]|6[5-7]|9[016-9])|4(?:4[569]|5[4-6]|6[5-7]|7[0179])|5(?:[34]\d|50|6[5-7]))\d{4}')
            ->setExampleNumber('20491234');
        $this->numberFormat = [
            (new NumberFormat())
                ->setPattern('(\d{2})(\d{2})(\d{2})(\d{2})')
                ->setFormat('$1 $2 $3 $4')
                ->setLeadingDigitsPattern(['[024-7]'])
                ->setNationalPrefixOptionalWhenFormatting(false),
        ];
        $this->tollFree = PhoneNumberDesc::empty();
        $this->sharedCost = PhoneNumberDesc::empty();
        $this->personalNumber = PhoneNumberDesc::empty();
        $this->voip = PhoneNumberDesc::empty();
        $this->pager = PhoneNumberDesc::empty();
        $this->uan = PhoneNumberDesc::empty();
        $this->voicemail = PhoneNumberDesc::empty();
        $this->noInternationalDialling = PhoneNumberDesc::empty();
    }
}
