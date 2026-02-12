<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;
use App\Models\EnergyReading;
use Carbon\Carbon;

Route::get('/', function (Request $req) {
    $given = $req->all();
    $start = new Carbon()->startOfDay()->format("Y-m-d\TH:i:sp");
    $end = new Carbon()->startOfDay()->modify("+1 day -1 microsecond")->format("Y-m-d\TH:i:sp");
    if(array_key_exists("location", $given)) {
        $location = EnergyReading::whereBetween('created_at', [$start ? new Carbon($start) : "2000-01-01T00:00:00Z", $end ? new Carbon($end) : "2999-01-01T00:00:00Z"])->where('location', $given["location"])->get();
    }
    $dailyavg = EnergyReading::whereBetween('created_at', [$start ? new Carbon($start) : "2000-01-01T00:00:00Z", $end ? new Carbon($end) : "2999-01-01T00:00:00Z"])->get();
    if(array_key_exists("start", $given)) {
        $start = new Carbon($given["start"])->format("Y-m-d\TH:i:sp");
    }
    if(array_key_exists("end", $given)) {
        $end =  new Carbon($given["end"])->format("Y-m-d\TH:i:sp");
    }
    $date = EnergyReading::whereBetween('created_at', [$start, $end])->get();
    return Inertia::render('Dashboard', ['daily' => $dailyavg, "location" => $location ?? $dailyavg, "date" => $date ?? $dailyavg]);
})->name('dashboard');

Route::get('/sync', function (Request $req) {
    return Inertia::render('Sync');
})->name('sync');

Route::get('/status', function() {
    return Inertia::render('modulea');
})->name('status');

require __DIR__.'/settings.php';
require __DIR__.'/api.php';
