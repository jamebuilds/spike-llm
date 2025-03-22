<?php

namespace App\Services;

use App\DataTransferObject\ExtractedData;
use App\Services\ExtractApi\TogetherAiOneApi;
use App\ValueObject\ConformityCertificateValueObject;
use Illuminate\Support\Str;

class ExtractService
{
    public function handle(string $image): ExtractedData
    {
        $service = new TogetherAiOneApi();

        $result = null;
        $maxLoop = 3;

        // loop it to ensure that it will return a valid json
        while ($result === null && $maxLoop > 0) {
            [$answer, $imageInBase64] = $service->handle($image, $this->generateInitialPrompt());
            $result = $this->extractJson($answer);
            $maxLoop--;
        }

        return new ExtractedData(
            $answer,
            $result,
            $imageInBase64
        );
    }

    protected function extractJson(string $answer): ConformityCertificateValueObject|null
    {
        try {
            // remove all other text from the answer, only take the json content
            $extractJson = "{" . Str::between($answer, "{", "}") . "}";
            // remove all new line because we cannott do json_decode with it
            $extractJson = Str::replace(["\n"], '', $extractJson);
            // change single quote into double quote for a valid json string
            $extractJson = Str::replace("'", '"', $extractJson);

            // we could also potentially add in validator for each field in the object
            return ConformityCertificateValueObject::fromJsonString($extractJson);;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function generateInitialPrompt(): string
    {
        return <<<END
You are a helpful assistant and expert in reading certificates.
The image is a certificate of conformity (CoC) for a fire safety product.

## Here is a description of some of the fields in the certificate:
coc_holder is the person or entity this certificate is issued to.
certificate_number is the unique number assigned to the certificate.
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

    protected function generateCheckPrompt(string $json): string
    {
        return <<<END
You are a professional software developer who is familiar with json.
The image is a certificate of conformity (CoC) for a fire safety product.

## Here is a description of some of the fields in the certificate:
Coc holder is the person or entity this certificate is issued to.
Certificate Number sometimes could contain Revision number like Rev. 11.
Revision date and Expiry date are optional, if they dont exists, return as null.

## Do a check of the json against the image
Given this json, check against the image to see if the data is correct.
$json

## Check and return the json
Correct the json data if it is wrong. And finally return me the just the json.
END;
    }
}
