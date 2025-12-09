<?php

namespace App\Controllers;

use App\ApiResponses\LeadsNotFoundApiResponse;
use App\ApiResponses\LeadsProcessedApiResponse;
use App\Config\Config;
use App\Services\AmoCRM\AmoCRMCommonNotes;
use App\Services\AmoCRM\AmoCRMLeads;
use App\Services\AmoCRM\AmoCRMTasks;
use Symfony\Component\HttpFoundation\Response;

final readonly class LeadController
{
    use ApiResponseTrait;

    public function __construct(
        private AmoCRMLeads $amoCRMLeads,
        private AmoCRMTasks $amoCRMTasks,
        private AmoCRMCommonNotes $amoCRMCommonNotes,
    ) {
    }

    public function changeStatusToWaitingAction(): Response
    {
        $leads = $this->amoCRMLeads->findInRequestStatus();

        if (!$leads->isEmpty()) {
            $this->amoCRMLeads->changeStatusTo($leads, Config::get('AMOCRM_CLIENT_WAITING_STATUS_ID'));

            return $this->apiResponse(new LeadsProcessedApiResponse($leads));
        }

        return $this->apiResponse(new LeadsNotFoundApiResponse());
    }

    public function duplicationAction(): Response
    {
        $leads = $this->amoCRMLeads->findInClientConfirmedStatus();

        if (!$leads->isEmpty()) {
            $massCopyLeadsMap = $this->amoCRMLeads->massCopyToStatus($leads, Config::get('AMOCRM_CLIENT_WAITING_STATUS_ID'));
            $this->amoCRMTasks->massCopyLeadsTasks($massCopyLeadsMap);
            $this->amoCRMCommonNotes->massCopyLeadsCommonNotes($massCopyLeadsMap);

            return $this->apiResponse(new LeadsProcessedApiResponse($leads));
        }

        return $this->apiResponse(new LeadsNotFoundApiResponse());
    }
}
