<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\EnergyReading;
use Carbon\Carbon;


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
        $start = new Carbon()->startOfDay()->format("Y-m-d\TH:i:sp");
        $end = new Carbon()->startOfDay()->modify("+1 day -1 microsecond")->format("Y-m-d\TH:i:sp");
        $validator = Validator::make($req->all(), ["start" => "date|date_format:Y-m-d\TH:i:sp", "end" => "date|date_format:Y-m-d\TH:i:sp", "location" => "required|in:EE,LV,FI"]);
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
            $end = $req->all()["end"];
        }
        $entries = EnergyReading::whereBetween('created_at', [$start ? new Carbon($start) : "2000-01-01T00:00:00Z", $end ? new Carbon($end) : "2999-01-01T00:00:00Z"])->where('location', $req->all()["location"])->get();
        return response()->json(["status" => "ok", "entries" => $entries]);
    });
    Route::post('/sync/prices', function(Request $req) {
        $args = (array) $req->all();
        $validator = Validator::make($args, ["start" => "date|nullable", "end" => "date|nullable",]);
        $start = new Carbon()->startOfDay()->format("Y-m-d\TH:i:sp");
        $end = new Carbon()->startOfDay()->modify("+1 day -1 microsecond")->format("Y-m-d\TH:i:sp");
        if($req->all()['start'] != null) {
            $start = new Carbon($args["start"])->format("Y-m-d\TH:i:sp");
        }
        if($req->all()['end'] != null) {
            $end =  new Carbon($args["end"])->format("Y-m-d\TH:i:sp");
        }
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try {
        $json = json_decode(file_get_contents("https://dashboard.elering.ee/api/nps/price?start={$start}&end={$end}"));
        } catch(Exception $e) {
           return response()->json(["error" => "PRICE_API_UNAVAILABLE"]);
        }
        $count = 0;
        $duplicate_count = 0;
        foreach($json->data as $country => $cnt_value) {
            foreach($cnt_value as $index => $entry) {
                $date = new Carbon($entry->timestamp);
                 if(EnergyReading::where('created_at', $date)->count() > 0 && EnergyReading::where('location', strtoupper((string)$country))->count() > 0){
                   $duplicate_count++;
                   continue;
                }
                $energyEntry = new EnergyReading;
                $energyEntry->location = strtoupper((string)$country);
                $energyEntry->price_eur_mwh = $entry->price;
                $energyEntry->created_at = $date;
                $energyEntry->source = "API";
                $energyEntry->save();
                $count++;
            }
        }
        return response()->json(["success" => "ok", "message" => "Added {$count} entries. Duplicates {$duplicate_count}"]);
    });
    Route::delete('/readings', function() {
        $all_data = EnergyReading::where('source', "UPLOAD")->get();
        $count = $all_data->count();
        // try{
            if($count == 0) {
                return response()->json(["error" => "No UPLOAD records found."]);
            }
            EnergyReading::where('source', "UPLOAD")->delete();
            return response()->json(["success" => "ok", "message" => "Deleted {$count} uploaded records."]);
        // } catch(Exception $e) {
        //     return response()->json(["error" => "Cleanup failed. Please try again."]);
        // }
    })->name('readings.delete');
});
