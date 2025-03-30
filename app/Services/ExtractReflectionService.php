<?php

namespace App\Services;

use App\DataTransferObject\ExtractedData;
use App\Services\ExtractApi\TogetherAiOneApi;
use App\ValueObject\ConformityCertificateValueObject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

class ExtractReflectionService
{
    public function handle(string $image): ExtractedData
    {
        // Step 1: Initial extraction
        $imageInBase64 = $image ? "data:image/jpeg;base64," . base64_encode($image) : '';
        $payload = $this->generatePayload($this->generateInitialPrompt(), $imageInBase64);
        // call the together ai api
        $response = Http::timeout(240)
            ->withToken(config('services.together_ai.token'))
            ->contentType('application/json')
            ->post(config('services.together_ai.url'), $payload);
        $initialText = $response->json()['choices'][0]['message']['content'] ?? '';

        // Step 2: Try to extract JSON
        $extractedJson = $this->extractJsonFromText($initialText);

        dump($initialText);
        dump($extractedJson);

        // Step 3: If failed, run reflection
        if (!$extractedJson) {
            $reflectionPrompt = $this->generateReflectionPrompt($initialText);
            $reflectionPayload = $this->generatePayload($reflectionPrompt, $imageInBase64);

            $reflectionResponse = Http::timeout(240)
                ->withToken(config('services.together_ai.token'))
                ->contentType('application/json')
                ->post(config('services.together_ai.url'), $reflectionPayload);

            $reflectedText = $reflectionResponse->json()['choices'][0]['message']['content'] ?? '';
            $extractedJson = $this->extractJsonFromText($reflectedText);
            dump($reflectedText);
            dump($extractedJson);
        }

        dd('end');

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

    protected function generateReflectionPrompt(string $previousOutput): string
    {
        return <<<END
The following response was returned but did not strictly follow the instruction to output only a valid JSON object:

---
$previousOutput
---

Please extract and return ONLY the valid JSON part of the response. Do not include any markdown, formatting, or explanation. Just return the clean JSON.
END;
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
