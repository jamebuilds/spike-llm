<?php

namespace App\DataTransferObject;

use App\ValueObject\ConformityCertificateValueObject;

readonly class ExtractedData
{
    public function __construct(
        public string                            $answer,
        public ?ConformityCertificateValueObject $certificate,
        public string                            $imageBase64,
    )
    {
    }
}
