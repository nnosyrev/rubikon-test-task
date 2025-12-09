<?php

namespace App\Services\AmoCRM;

use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Filters\BaseRangeFilter;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Models\LeadModel;
use App\Config\Config;

final readonly class AmoCRMLeads extends AmoCRMAbstract
{
    private const CHANGE_STATUS_TO_WAITING_PRICE_FROM = 5001;
    private const DUPLICATE_PRICE = 4999;

    public function findInRequestStatus(): LeadsCollection
    {
        $leadsService = $this->amoCRMApiClient->leads();

        $filter = new LeadsFilter();
        $filter->setStatuses([[
            'status_id' => Config::get('AMOCRM_REQUEST_STATUS_ID'),
            'pipeline_id' => Config::get('AMOCRM_PIPELINE_ID')
        ]]);
        $filter->setPrice((new BaseRangeFilter)->setFrom(self::CHANGE_STATUS_TO_WAITING_PRICE_FROM)->setTo(PHP_INT_MAX));
        $filter->setLimit(Config::get('AMOCRM_LEADS_LIMIT'));

        try {
            return $leadsService->get($filter);
        } catch (AmoCRMApiNoContentException $e) {
            return new LeadsCollection();
        }
    }

    public function changeStatusTo(LeadsCollection $leadsCollection, int $statusId): void
    {
        $leadsService = $this->amoCRMApiClient->leads();

        foreach ($leadsCollection as $lead) {
            $lead->setStatusId($statusId);
        }

        $leadsService->update($leadsCollection);
    }

    public function findInClientConfirmedStatus(): LeadsCollection
    {
        $leadsService = $this->amoCRMApiClient->leads();

        $filter = new LeadsFilter();
        $filter->setStatuses([[
            'status_id' => Config::get('AMOCRM_CLIENT_CONFIRMED_STATUS_ID'),
            'pipeline_id' => Config::get('AMOCRM_PIPELINE_ID')
        ]]);
        $filter->setPrice(self::DUPLICATE_PRICE);
        $filter->setLimit(Config::get('AMOCRM_LEADS_LIMIT'));

        try {
            return $leadsService->get($filter, [
                LeadModel::CATALOG_ELEMENTS,
                LeadModel::IS_PRICE_BY_ROBOT,
                LeadModel::LOSS_REASON,
                LeadModel::SOURCE_ID,
                LeadModel::CONTACTS,
                LeadModel::SOURCE,
            ]);
        } catch (AmoCRMApiNoContentException $e) {
            return new LeadsCollection();
        }
    }

    public function massCopyToStatus(LeadsCollection $leadsCollection, int $statusId): MassCopyLeadsMap
    {
        $leadsService = $this->amoCRMApiClient->leads();

        $newLeadsCollection = new LeadsCollection();

        $massCopyLeadsMap = new MassCopyLeadsMap();

        foreach ($leadsCollection as $lead) {
            $newLead = $this->cloneLead($lead);
            $newLead->setStatusId($statusId);

            $newLeadsCollection->add($newLead);

            $massCopyLeadsMap->addPair($lead, $newLead);
        }

        $leadsService->add($newLeadsCollection);

        return $massCopyLeadsMap;
    }

    private function cloneLead(LeadModel $lead): LeadModel
    {
        return new LeadModel()
            ->setName($lead->getName())
            ->setPrice($lead->getPrice())
            ->setAccountId($lead->getAccountId())
            ->setGroupId($lead->getGroupId())
            ->setResponsibleUserId($lead->getResponsibleUserId())
            ->setCreatedBy($lead->getCreatedBy())
            ->setUpdatedBy($lead->getUpdatedBy())
            ->setPipelineId($lead->getPipelineId())
            ->setStatusId($lead->getStatusId())
            ->setClosedAt($lead->getClosedAt())
            ->setClosestTaskAt($lead->getClosestTaskAt())
            ->setLossReasonId($lead->getLossReasonId())
            ->setLossReason($lead->getLossReason())
            ->setIsDeleted($lead->getIsDeleted())
            ->setTags($lead->getTags())
            ->setSourceId($lead->getSourceId())
            ->setSource($lead->getSource())
            ->setCompany($lead->getCompany())
            ->setCustomFieldsValues($lead->getCustomFieldsValues())
            ->setContacts($lead->getContacts())
            ->setScore($lead->getScore());
    }
}
