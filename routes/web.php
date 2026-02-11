<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;



// Route::get('/', function () {
//     return Inertia::render('Dashboard');
// })->name('dashboard');

Route::get('/', function() {
    return Inertia::render('modulea');
})->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/api.php';
