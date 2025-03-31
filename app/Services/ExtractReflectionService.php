<?php

namespace App\Services;

use App\DataTransferObject\ExtractedData;
use App\ValueObject\ConformityCertificateValueObject;
use Illuminate\Support\Facades\Http;

class ExtractReflectionService
{
    public function handle(string $image): ExtractedData
    {
        $retry = 3;
        $result = null;

        while ($retry > 0 && !$result) {
            try {
                // Step 1: Initial extraction
                $imageInBase64 = $image ? "data:image/jpeg;base64," . base64_encode($image) : '';
                $payload = $this->generatePayload($this->generateInitialPrompt(), $imageInBase64);
                $response = Http::timeout(240)
                    ->withToken(config('services.together_ai.token'))
                    ->contentType('application/json')
                    ->post(config('services.together_ai.url'), $payload);
                $initialText = $response->json()['choices'][0]['message']['content'] ?? '';

                // Step 2: Try to extract JSON
                $extractedJson = $this->extractJsonFromText($initialText);

                // Step 3: Reflect if it is not a valid json
                if (!$extractedJson) {
                    $reflectionPrompt = $this->generateReflectionPromptInvalidJson($initialText);
                    $reflectionPayload = $this->generatePayload($reflectionPrompt, $imageInBase64);

                    $reflectionResponse = Http::timeout(240)
                        ->withToken(config('services.together_ai.token'))
                        ->contentType('application/json')
                        ->post(config('services.together_ai.url'), $reflectionPayload);

                    $reflectedText = $reflectionResponse->json()['choices'][0]['message']['content'] ?? '';
                    $extractedJson = $this->extractJsonFromText($reflectedText);

                    if (!$extractedJson) {
                        throw new \Exception('extract json failed');
                    }
                }

                // Step 4: Check json keys are as expected
                $result = ConformityCertificateValueObject::fromArray($extractedJson);

                // Step 5: Validate the values, currently only validate the cert number
                $this->validate($result);
            } catch (\Exception $e) {
                $result = null;
                $retry--;
                sleep(1);
            }
        }

        $answer = $initialText;

        return new ExtractedData(
            $answer,
            $result,
            $imageInBase64
        );
    }

    protected function generatePayload(string $prompt, string $imageInBase64): array
    {
        $payload = [
            "model" => "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo",
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        [
                            "type" => "text", "text" => $prompt
                        ]
                    ],
                ]
            ]
        ];

        if ($imageInBase64) {
            $payload['messages'][0]['content'][] = [
                "type" => "image_url",
                "image_url" => ['url' => $imageInBase64]
            ];
        }

        return $payload;
    }

    protected function generateInitialPrompt(): string
    {
        return <<<END
You are a helpful assistant and an expert in reading certificates.

The image is a Certificate of Conformity (CoC) for a fire safety product.

Extract the following fields from the certificate:
	•	certificate_number: unique number of the certificate. Remove any “Rev. XX” from the end.
	•	issue_date, revision_date, expiry_date: dates in DD/MM/YYYY format. If missing, use null.
	•	coc_holder_name, coc_holder_address, coc_holder_nationality

Valid certificate_number formats include:
	•	FSP-NNNN-NNNN-EE
	•	NNAEEEE where N = number, A = letter
	•	CLSXX NNNNNN EEEE
	•	CLSXX YY MM NNNNN EEE

IMPORTANT: Return ONLY a valid JSON object, no Markdown, no headings, no explanations."
END;
    }

    protected function generateReflectionPromptInvalidJson(string $previousOutput): string
    {
        return <<<END
The following response was returned but did not strictly follow the instruction to output only a valid JSON object:

---
$previousOutput
---

Please extract and return ONLY the valid JSON part of the response. Do not include any markdown, formatting, or explanation. Return with a valid Json Key. Just return the clean JSON.
END;
    }

    protected function validate(ConformityCertificateValueObject $result): void
    {
        // validate the coc number format
        $certificateNumber = $result->certificateNumber;

        $result = preg_match('/^\d{2}[A-Z]\d{4,5}$/i', $certificateNumber) // element
            || preg_match('/^CLS(1B|2|1A|AN|BN|2N)\s((\d{6}\s\d{4})|(\d{2}\s\d{2}\s\d{5}\s\d{3}))$/i', $certificateNumber) // tuv sud
            || preg_match('/^FSP-\d{4}-\d{4}(-\d{1,2})?$/i', $certificateNumber); // setsco

        if (!$result) {
            throw new \Exception('invalid certificate number format');
        }
    }

    protected function extractJsonFromText(string $text): ?array
    {
        // Regex to match the first valid JSON object
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        return null;
    }
}
