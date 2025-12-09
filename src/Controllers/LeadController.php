<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class LeadController
{
    public function statusToWaitingAction(): Response
    {
        return new JsonResponse(['data' => 123]);
    }

    public function duplicationAction(): Response
    {
        return new JsonResponse(['data' => 999]);
    }
}
