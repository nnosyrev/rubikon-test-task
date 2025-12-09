<?php

namespace App\Services\AmoCRM;

use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Collections\TasksCollection;
use AmoCRM\Filters\BaseRangeFilter;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Filters\NotesFilter;
use AmoCRM\Filters\TasksFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\NoteType\CommonNote;
use AmoCRM\Models\TaskModel;
use App\Config\Config;

final class AmoCRMLeads extends AmoCRMAbstract
{
    private const CHANGE_STATUS_TO_WAITING_PRICE_FROM = 5001;
    private const DUPLICATE_PRICE = 4999;

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

    public function duplicate(): void
    {
        $leadsService = $this->getApiClient()->leads();

        // Leads
        $filter = new LeadsFilter();
        $filter->setStatuses([[
            'status_id' => Config::get('AMOCRM_CLIENT_CONFIRMED_STATUS_ID'),
            'pipeline_id' => Config::get('AMOCRM_PIPELINE_ID')
        ]]);
        $filter->setPrice(self::DUPLICATE_PRICE);
        $filter->setLimit(Config::get('AMOCRM_LEADS_LIMIT'));

        $leadsCollection = $leadsService->get($filter, [
            LeadModel::CATALOG_ELEMENTS,
            LeadModel::IS_PRICE_BY_ROBOT,
            LeadModel::LOSS_REASON,
            LeadModel::SOURCE_ID,
            LeadModel::CONTACTS,
            LeadModel::SOURCE,
        ]);

        $newLeadsCollection = new LeadsCollection();

        $leadsIds = [];
        $newLeads = [];
        foreach ($leadsCollection as $lead) {
            $newLead = $this->cloneLead($lead);

            $newLeadsCollection->add($newLead);

            $leadsIds[] = $lead->id;
            $newLeads[$lead->getId()] = $newLead;
        }

        $leadsService->add($newLeadsCollection);

        // Tasks
        $tasksService = $this->getApiClient()->tasks();

        $tasksFilter = new TasksFilter();
        $tasksFilter->setEntityType(EntityTypesInterface::LEAD);
        $tasksFilter->setEntityIds($leadsIds);

        $tasksCollection = $tasksService->get($tasksFilter);

        $newTasksCollection = new TasksCollection();
        foreach ($tasksCollection as $task) {
            $newTask = $this->cloneTask($task);
            $newTask->setEntityId($newLeads[$task->getEntityId()]->getId());

            $newTasksCollection->add($newTask);
        }

        $tasksService->add($newTasksCollection);

        // Notes
        $notesService = $this->getApiClient()->notes(EntityTypesInterface::LEADS);

        $notesFilter = new NotesFilter();
        $notesFilter->setEntityIds($leadsIds);

        $notesCollection = $notesService->get($notesFilter);

        $newNotesCollection = new NotesCollection;
        foreach ($notesCollection as $note) {
            $newNote = $this->cloneCommonNote($note);
            $newNote->setEntityId($newLeads[$task->getEntityId()]->getId());

            $newNotesCollection->add($newNote);
        }

        $notesService->add($newNotesCollection);
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
            ->setStatusId(Config::get('AMOCRM_CLIENT_WAITING_STATUS_ID'))
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

    private function cloneTask(TaskModel $task): TaskModel
    {
        return new TaskModel()
            ->setResponsibleUserId($task->getResponsibleUserId())
            ->setGroupId($task->getGroupId())
            ->setCreatedBy($task->getCreatedBy())
            ->setUpdatedBy($task->getUpdatedBy())
            ->setAccountId($task->getAccountId())
            ->setDuration($task->getDuration())
            ->setEntityId($task->getEntityId())
            ->setEntityType($task->getEntityType())
            ->setIsCompleted($task->getIsCompleted())
            ->setTaskTypeId($task->getTaskTypeId())
            ->setText($task->getText())
            ->setResult($task->getResult())
            ->setCompleteTill($task->getCompleteTill())
            ->setCompleteTill($task->getCompleteTill());
    }

    private function cloneCommonNote(CommonNote $note): CommonNote
    {
        return new CommonNote()
            ->setText($note->getText())
            ->setEntityId($note->getEntityId())
            ->setResponsibleUserId($note->getResponsibleUserId())
            ->setGroupId($note->getGroupId())
            ->setCreatedBy($note->getCreatedBy())
            ->setUpdatedBy($note->getUpdatedBy())
            ->setAccountId($note->getAccountId())
            ->setIsNeedToTriggerDigitalPipeline($note->getIsNeedToTriggerDigitalPipeline());
    }
}
