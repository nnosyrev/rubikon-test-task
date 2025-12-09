<?php

namespace App\Controllers;

use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use App\Services\AmoCRM\AmoCRMLeads;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class LeadController
{
    public function changeStatusToWaitingAction(): Response
    {
        try {
            $amoCRMLeads = new AmoCRMLeads();
            $amoCRMLeads->changeStatusToWaiting();

            return new JsonResponse(['data' => 'ok']);
        } catch (AmoCRMApiNoContentException $e) {
            return new JsonResponse(['data' => 'leads not found']);
        }
    }

    public function duplicationAction(): Response
    {
        return new JsonResponse(['data' => 999]);
    }
}
