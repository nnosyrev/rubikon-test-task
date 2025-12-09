<?php

namespace App\Services\AmoCRM;

use AmoCRM\Client\AmoCRMApiClient;

abstract readonly class AmoCRMAbstract
{
    public function __construct(
        protected AmoCRMApiClient $amoCRMApiClient,
    ) {
    }
}
