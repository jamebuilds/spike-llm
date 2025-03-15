<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('extract-coc.create');
//    return Inertia::render('welcome');
})->name('home');

Route::get('/extract-coc', [\App\Http\Controllers\ExtractCocController::class, 'create'])->name('extract-coc.create');
Route::post('/extract-coc', [\App\Http\Controllers\ExtractCocController::class, 'store'])->name('extract-coc.store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
