<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\DB;




Route::prefix('api')->group(function () {
    Route::get('/health', function () {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e );
        }
        return response()->json(["status" => "ok", "db" => "ok"]);
    });
});
