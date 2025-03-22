<?php

namespace App\Services\ExternalApi;

use Illuminate\Support\Facades\Http;

class OllamaApi
{
    public function handle(string $fileContent): array
    {
        $imageInBase64 = base64_encode($fileContent);
        $prompt = $this->generatePrompt();

        $systemMessage = 'you are an expert in certification.';

        $payload = [
            "model" => "llama3.2-vision",
            "stream" => false,
            "format" => [
                "type" => "object",
                "properties" => [
                    "certificate_number" => [
                        'type' => 'string'
                    ],
                ]
            ],
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemMessage,
                ],
                [
                    "role" => "user",
                    "content" => $prompt,
                    "images" => [$imageInBase64]
                ]
            ],
        ];

        $response = Http::timeout(240)
            ->post(config('services.ollama.url'), $payload);

        return [
            // extract the answer from the response json
            $response->json()['message']['content'],
            "data:image/jpeg;base64," . $imageInBase64
        ];
    }

    protected function generatePrompt(): string
    {
        return <<<END
Extract the certificate number from the image and return ONLY a valid JSON object with the following structuree.
{
    'certificate_number': '1234567890',
}
Do NOT include any other text or explanation. Only return the JSON object."
END;
    }
}
