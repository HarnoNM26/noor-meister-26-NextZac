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

        $validator = Validator::make($req->all(), ["start" => "date|date_format:Y-m-d\TH:i:sp", "end" => "date|date_format:Y-m-d\TH:i:sp"]);
        if($validator->fails()) {
            return response()->json(["status" => "fail", "message" => $validator->errors()]);
        }
        if(!array_key_exists("location", $req->all())) {
            return response()->json(["status" => "fail", "message" => "Location is a mandatory field."]);
        }
        if(array_key_exists("start", $req->all())) {
            $start = $req->all()["start"];
        }
        if(array_key_exists("end", $req->all())) {
            $start = $req->all()["end"];
        }

        $entries = EnergyReading::whereBetween('created_at', [$start ?? "2000-01-01T00:00:00Z", $end ?? "2999-01-01T00:00:00Z"])->get();
        return response()->json(["status" => "ok", "entries" => $entries]);
    });
});
