<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\EnergyReading;



Route::prefix('api')->group(function () {
    Route::get('/health', function () {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e );
        }
        return response()->json(["status" => "ok", "db" => "ok"]);
    });
    Route::get('/readings', function (Request $req) {
        if(!array_key_exists("location", $req->all())) {
            return response()->json(["status" => "fail", "message" => "Location is a mandatory field."]);
        }
        return response()->json(["status" => "ok", "entries" => EnergyReading::whereBetween('created_at', [array_key_exists("start", $req->all()) ? $req->all()["start"] : "2000-01-01T00:00:00Z", array_key_exists("end", $req->all()) ? $req->all()["end"] : "2999-01-01T00:00:00Z"])->get()]);
    });
});
