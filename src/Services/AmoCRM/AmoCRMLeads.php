<?php

namespace App\Services\AmoCRM;

use AmoCRM\Filters\BaseRangeFilter;
use AmoCRM\Filters\LeadsFilter;
use App\Config\Config;

final class AmoCRMLeads extends AmoCRMAbstract
{
    private const CHANGE_STATUS_TO_WAITING_PRICE_FROM = 5001;

    public function changeStatusToWaiting(): void
    {
        $leadsService = $this->getApiClient()->leads();

        $filter = new LeadsFilter();
        $filter->setStatuses([[
            'status_id' => Config::get('AMOCRM_REQUEST_STATUS_ID'),
            'pipeline_id' => Config::get('AMOCRM_PIPELINE_ID')
        ]]);
        $filter->setPrice((new BaseRangeFilter)->setFrom(self::CHANGE_STATUS_TO_WAITING_PRICE_FROM)->setTo(PHP_INT_MAX));
        $filter->setLimit(Config::get('AMOCRM_LEADS_LIMIT'));

        $leadsCollection = $leadsService->get($filter);

        foreach ($leadsCollection as $lead) {
            $lead->setStatusId(Config::get('AMOCRM_CLIENT_WAITING_STATUS_ID'));
        }

        $leadsService->update($leadsCollection);
    }
}
