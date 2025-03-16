<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ExtractCocController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('extract-coc');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$request->hasFile('coc_file')) {
            abort(404);
        }

        // generate the payload for the prompt
        $imageInBase64 = "data:image/jpeg;base64," . base64_encode($request->file('coc_file')->get());
        $prompt = $this->generatePrompt();
        $payload = [
            "model" => "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo",
            "messages" => [
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
            ],
        ];

        // call the llm api
        $response = Http::timeout(240)
            ->withToken(config('services.together_ai.token'))
            ->contentType('application/json')
            ->post(config('services.together_ai.url'), $payload);

        // extract the answer from the response json
        $answer = $response->json()['choices'][0]['message']['content'];

        // keep the data in the session to load later
        $uploadKey = Str::random(32);
        session([$uploadKey => [
            'image' => $imageInBase64,
            'answer' => $answer,
        ]]);

        return redirect()->route('extract-coc.show', ['session_key' => $uploadKey]);
    }

    protected function generatePrompt(): string
    {
        return <<<END
You are a helpful assistant.
The image is a certificate of conformity (CoC).
These are the data I want to extract from the certificate.
- Certificate number
- Certificate issue date
- Certificate revision date
- Certificate exipriy date
- CoC Holder’s name (this is the name this certification is issued to)
- CoC Holder’s address
- CoC Holder’s Nationality or Country of Registration
- Product Scheme (should be something like "Type 1b", "Type 2", "Type 5")
- Product type
- Product brand
- Product model
- Country of origin
- Product Detail
- Test standards
- Test reports
Extract those data above and return ONLY a valid JSON object with the following structure as an example.
{
    'certificate_number': '1234567890',
    'issue_date': '01/01/2021',
    'revision_date': '01/01/2021',
    'expiry_date': '01/01/2021',
    'coc_holder_name': 'john',
    'coc_holder_address': '123 street',
    'coc_holder_nationality': 'germany',
    'product_scheme': 'type 1b',
    'product_type': 'fire alarm',
    'product_brand': 'abc',
    'product_model': 'abc',
    'country_of_origin': 'abc',
    'product_detail': 'abc',
    'test_standards': 'abc',
    'test_reports': 'abc',
}
Do NOT include any other text or explanation. Only return the JSON object."
END;
    }

    public function show(Request $request): Response{
        $sessionKey = $request->session_key;
        $data = $request->session()->get($sessionKey);

        return Inertia::render('extract-coc-show', [
            'data' => $data,
        ]);
    }
}
