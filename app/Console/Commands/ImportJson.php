<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use App\Models\EnergyReading;
use Carbon\Carbon;

class ImportJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:json {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports and validates a given json file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $json = json_decode(file_get_contents($this->argument("file"), true));
        foreach($json as $entry) {
            // Invalid Timestamp == Skip Record
            // Missing Location == EE
            // price_eur_mwh - If numeric, accept(can be negative), if string == skip
            // Duplicates == ignore // Duplicate is when a record with the same timestamp and location is available.
            $msg = null;
            $entry_array = (array) $entry;
            if(!array_key_exists("timestamp", $entry_array) || !array_key_exists("price_eur_mwh", $entry_array)){
                continue;
            }
            if(!array_key_exists("location", $entry_array)){
                $entry_array["location"] = "EE";
                $this->info("Location invalid, changing to EE");
            }
            $validator = Validator::make($entry_array, ["timestamp" => "date|date_format:Y-m-d\TH:i:sp", "location" => "in:EE,LV,FI"]);

            if($validator->fails())
            {   
                $msg = "Invalid timestamp, skipping.";
                continue;
            }
            if(!is_numeric($entry_array["price_eur_mwh"])) {
                $msg = "Invalid price_eur_mwh, skipping.";
            }
            if(EnergyReading::where('created_at', new Carbon($entry_array["timestamp"]))->count() > 0 && EnergyReading::where('location', $entry_array["location"])->count() > 0){
                $msg = "Duplicate Found, skipping.";
            }
            if($msg != null) {
                $this->warn($msg);
                continue;
            }
            $energy_entry = new EnergyReading;
            $energy_entry->created_at = $entry_array["timestamp"];
            $energy_entry->location = $entry_array["location"];
            $energy_entry->price_eur_mwh = $entry_array["price_eur_mwh"];
            $energy_entry->source = "UPLOAD";
            $energy_entry->save();
            $this->info("Successfully added entry");
        }
    }
}
