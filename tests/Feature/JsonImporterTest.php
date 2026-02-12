<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class JsonImporterTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_json_importer_command(): void
    {
        $this->artisan('import:json energy_dump.json')->expectsOutput('Successfully added entry')->assertExitCode(0);
    }
}
