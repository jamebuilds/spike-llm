<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadSampleCocController extends Controller
{
    public function invoke(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'file_name' => Rule::in(['22A0614.png', 'CLS1B-081460-0025-Rev.-00.png', 'FSP-2018-1188-DoC.png']),
        ]);

        return Storage::disk('public')->download("{$validated['file_name']}");
    }
}
