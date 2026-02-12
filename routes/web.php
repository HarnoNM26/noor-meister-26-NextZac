<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;


Route::get('/', function (Request $req) {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/sync', function (Request $req) {
    return Inertia::render('Sync');
})->name('sync');

Route::get('/status', function() {
    return Inertia::render('modulea');
})->name('status');

require __DIR__.'/settings.php';
require __DIR__.'/api.php';
