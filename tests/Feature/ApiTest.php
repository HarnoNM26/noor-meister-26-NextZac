<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_import_and_check_returned_entries(): void
    {
        $this->artisan("import:json energy_dump.json");
        $start = new Carbon("2025-01-01T14:00:00Z")->startOfDay()->format("Y-m-d\TH:i:sp");
        $end = new Carbon("2026-02-01T14:00:00Z")->startOfDay()->modify("+1 day -1 microsecond")->format("Y-m-d\TH:i:sp");
        $responseEE = $this->get("api/readings?start={$start}&end={$end}&location=EE");
        $responseFI = $this->get("api/readings?start={$start}&end={$end}&location=LV");
        $responseLV = $this->get("api/readings?start={$start}&end={$end}&location=FI");
        $responseEE->assertStatus(200);
        $responseFI->assertStatus(200);
        $responseLV->assertStatus(200);

        $this->assertNotEmpty($responseEE->getOriginalContent()["entries"]);
        $this->assertNotEmpty($responseFI->getOriginalContent()["entries"]);
        $this->assertNotEmpty($responseLV->getOriginalContent()["entries"]);
    }

    public function test_price_sync_stores_data(): void
    {
        $this->artisan("import:json energy_dump.json");
        $response = $this->post("api/sync/prices", ["location" => "EE", "start" => null, "end" => null]);
        $regex = '/^((?!Added 0 entries.).)*$/';
        $this->assertMatchesRegularExpression($regex, $response->getOriginalContent()["message"]);
        $response->assertStatus(200);
    }
}
