<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Player routes
Route::get('players', [PlayerController::class, 'index'])->name('players.index');
Route::get('players/{player}', [PlayerController::class, 'show'])->name('players.show');
Route::get('players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
Route::put('players/{player}', [PlayerController::class, 'update'])->name('players.update');
Route::post('players/{player}/generate-description', [PlayerController::class, 'generateDescription'])->name('players.generate-description');

require __DIR__.'/settings.php';
