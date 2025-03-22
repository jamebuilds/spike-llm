<?php

namespace App\Http\Controllers;

use App\Services\ExtractOllama;
use App\Services\ExtractService;
use App\Services\ExtractTogetherAi;
use App\Services\ExtractTogetherAiOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $service = new ExtractService();
        $data = $service->handle($request->file('coc_file')->get());

        // keep the data in the session to load later after redirect
        $uploadKeyInSession = Str::random(32);
        session([$uploadKeyInSession => [
            'image' => $data->imageBase64,
            'result' => $data->certificate ? (string)$data->certificate : "{}",
            'answer' => $data->answer,
        ]]);

        return redirect()->route('extract-coc.show', ['session_key' => $uploadKeyInSession]);
    }

    public function show(Request $request): Response
    {
        $sessionKey = $request->session_key;
        $data = $request->session()->get($sessionKey);

        return Inertia::render('extract-coc-show', [
            'data' => $data,
        ]);
    }
}
