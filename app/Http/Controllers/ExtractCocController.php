<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return redirect()->back();
    }
}
