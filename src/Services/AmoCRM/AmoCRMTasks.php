<?php

namespace App\Services\AmoCRM;

use AmoCRM\Collections\TasksCollection;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Filters\TasksFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\TaskModel;

final class AmoCRMTasks extends AmoCRMAbstract
{
    public function massCopyLeadsTasks(MassCopyLeadsMap $massCopyLeadsMap): void
    {
        $tasksService = $this->getApiClient()->tasks();

        $tasksFilter = new TasksFilter();
        $tasksFilter->setEntityType(EntityTypesInterface::LEAD);
        $tasksFilter->setEntityIds($massCopyLeadsMap->getOriginIds());

        try {
            $tasksCollection = $tasksService->get($tasksFilter);
        } catch (AmoCRMApiNoContentException $e) {
            // Не нашли задач
            return;
        }

        $newTasksCollection = new TasksCollection();
        foreach ($tasksCollection as $task) {
            $newTask = $this->cloneTask($task);
            $newTask->setEntityId($massCopyLeadsMap->getCopyByOriginId($task->getEntityId())->getId());

            $newTasksCollection->add($newTask);
        }

        $tasksService->add($newTasksCollection);
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
}
