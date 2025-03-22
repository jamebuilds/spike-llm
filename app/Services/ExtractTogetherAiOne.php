<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExtractTogetherAiOne
{
    public function handle(string $fileContent, $prompt): array
    {
        $imageInBase64 = $fileContent ? "data:image/jpeg;base64," . base64_encode($fileContent) : '';

        $payload = $this->generatePayload($prompt, $imageInBase64);

        // call the together ai api
        $response = Http::timeout(240)
            ->withToken(config('services.together_ai.token'))
            ->contentType('application/json')
            ->post(config('services.together_ai.url'), $payload);

        return [
            // extract the answer from the response json
            $response->json()['choices'][0]['message']['content'],
            $imageInBase64
        ];
    }

    protected function generatePayload(string $prompt, string $imageInBase64): array
    {
        $payload = [
            "model" => "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo",
            // "model" => "meta-llama/Llama-3.2-90B-Vision-Instruct-Turbo",
            "messages" => [
                // todo: can add system message?
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
}
