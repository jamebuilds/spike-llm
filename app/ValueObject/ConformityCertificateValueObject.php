<?php

namespace App\ValueObject;

use InvalidArgumentException;

readonly class ConformityCertificateValueObject
{
    public function __construct(
        public string  $certificateNumber,
        public string  $issueDate,
        public ?string $revisionDate,
        public string  $expiryDate,
        public string  $cocHolderName,
        public string  $cocHolderAddress,
        public string  $cocHolderNationality
    )
    {
    }

    public static function fromJsonString(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON provided: ' . json_last_error_msg());
        }

        return new self(
            $data['certificate_number'] ?? throw new InvalidArgumentException('Missing certificate_number'),
            $data['issue_date'] ?? throw new InvalidArgumentException('Missing issue_date'),
            $data['revision_date'] ?? null,
            $data['expiry_date'] ?? throw new InvalidArgumentException('Missing expiry_date'),
            $data['coc_holder_name'] ?? throw new InvalidArgumentException('Missing coc_holder_name'),
            $data['coc_holder_address'] ?? throw new InvalidArgumentException('Missing coc_holder_address'),
            $data['coc_holder_nationality'] ?? throw new InvalidArgumentException('Missing coc_holder_nationality')
        );
    }

    public function __toString(): string
    {
        return json_encode([
            'certificate_number' => $this->certificateNumber,
            'issue_date' => $this->issueDate,
            'revision_date' => $this->revisionDate,
            'expiry_date' => $this->expiryDate,
            'coc_holder_name' => $this->cocHolderName,
            'coc_holder_address' => $this->cocHolderAddress,
            'coc_holder_nationality' => $this->cocHolderNationality,
        ]);
    }
}
