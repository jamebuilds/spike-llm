<?php

namespace App\Services\ExternalApi;

use Illuminate\Support\Facades\Http;

class TogetherAiApi
{
    public function handle(string $fileContent): array
    {
        $imageInBase64 = "data:image/jpeg;base64," . base64_encode($fileContent);
        $prompt = $this->generatePrompt();

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
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => ['url' => $imageInBase64]
                        ]
                    ],
                ]
            ]
        ];

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

    protected function generatePrompt(): string
    {
        return <<<END
You are a helpful assistant and expert in reading certificates.
The image is a certificate of conformity (CoC) for a fire safety product.
Coc holder is the person or entity this certificate is issued to.
Certificate Number sometimes could contain Revision number like Rev. 11.
Revision date and Expiry date are optional, if they dont exists, return as null.
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
