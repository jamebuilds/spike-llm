<?php

namespace App\Services;

use App\DataTransferObject\ExtractedData;
use App\Services\ExtractApi\TogetherAiOneApi;

class ExtractZeroPromptService
{
    public function handle(string $image): ExtractedData
    {
        $service = new TogetherAiOneApi();

        [$answer, $imageInBase64] = $service->handle($image, $this->generateInitialPrompt());

        return new ExtractedData(
            $answer,
            null,
            $imageInBase64
        );
    }

    protected function generateInitialPrompt(): string
    {
        return <<<END
You are a helpful assistant and expert in reading certificates.
The image is a certificate of conformity (CoC) for a fire safety product.

## Here is a description of some of the fields in the certificate:
coc_holder is the person or entity this certificate is issued to.
certificate_number is the unique number assigned to the certificate.
certificate_number sometimes will contains Revision number like Rev. 00, remove it from the certificate_number.
Here are some rules for the certificate_number:
  certificate_number sometimes could contain Revision number like Rev. 11.
  certificate_number can be in this format “FSP-NNNN-NNNN-EE” where “NNNN” represents 4 numbers and “EE” represents 2 optional numbers
  certificate_number can be in this format “NNAEEEE” where “NN” represents 2 numbers, “A” represents 1 letter, and “EEEE” represents 4 to 5 numbers
  certificate_number can be in either of these formats:(1) “CLSXX NNNNNN EEEE” where “XX” represents “1A”, “AN”, “1B”, “BN”, “2” or “2N”, “NNNNNN” represents 6 numbers and “EEEE” represents 4 numbers. or (2) “CLSXX YY MM NNNNN EEE” where “XX” represents “1A”, “AN”, “1B”, “BN”, “2” or “2N”, “YY” and “MM” represents 2 numbers, “NNNNN” represents 5 numbers and “EEE” represents 3 numbers
revision_date and expiry_date are optional, if they dont exists, return as null.

Extract the data and return ONLY a valid JSON object with the following structure as an example.
{
    'certificate_number': '1234567890',
    'issue_date': '01/01/2021',
    'revision_date': '01/01/2021',
    'expiry_date': '01/01/2021',
    'coc_holder_name': 'john',
    'coc_holder_address': '123 street',
    'coc_holder_nationality': 'germany'
}
Do NOT include any other text or explanation. Only return the JSON object."
END;
    }
}
