<?php

namespace App\Services;

class EleringInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $url = "https://dashboard.elering.ee/api/nps/price";
    }

    public function getData() {
    }
}
