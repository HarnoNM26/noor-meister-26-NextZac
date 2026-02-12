<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class JsonImporterTest extends BaseTestCase
{
    public function test_json_importer_command(): void {
        $this->artisan('import:json energy_dump.json')->expectsOutput('Successfully added entry')->assertExitCode(0);
    }
}
