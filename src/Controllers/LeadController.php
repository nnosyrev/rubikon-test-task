<?php

namespace App\Controllers;

use App\Config\Config;
use App\Services\AmoCRM\AmoCRMCommonNotes;
use App\Services\AmoCRM\AmoCRMLeads;
use App\Services\AmoCRM\AmoCRMTasks;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class LeadController
{
    public function changeStatusToWaitingAction(): Response
    {
        $amoCRMLeads = new AmoCRMLeads();

        $leads = $amoCRMLeads->findInRequestStatus();

        if (!$leads->isEmpty()) {
            $amoCRMLeads->changeStatusTo($leads, Config::get('AMOCRM_CLIENT_WAITING_STATUS_ID'));
        }

        return new JsonResponse(['data' => 'ok ' . $leads->count()]);
    }

    public function duplicationAction(): Response
    {
        $amoCRMLeads = new AmoCRMLeads;
        $amoCRMTasks = new AmoCRMTasks;
        $amoCRMCommonNotes = new AmoCRMCommonNotes;

        $leads = $amoCRMLeads->findInClientConfirmedStatus();

        if (!$leads->isEmpty()) {
            $massCopyLeadsMap = $amoCRMLeads->massCopyToStatus($leads, Config::get('AMOCRM_CLIENT_WAITING_STATUS_ID'));
            $amoCRMTasks->massCopyLeadsTasks($massCopyLeadsMap);
            $amoCRMCommonNotes->massCopyLeadsCommonNotes($massCopyLeadsMap);
        }

        return new JsonResponse(['data' => 'ok ' . $leads->count()]);
    }
}
