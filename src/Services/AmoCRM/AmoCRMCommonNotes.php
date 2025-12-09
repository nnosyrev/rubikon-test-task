<?php

namespace App\Services\AmoCRM;

use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Filters\NotesFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\NoteType\CommonNote;

final class AmoCRMCommonNotes extends AmoCRMAbstract
{
    public function massCopyLeadsCommonNotes(MassCopyLeadsMap $massCopyLeadsMap): void
    {
        $notesService = $this->getApiClient()->notes(EntityTypesInterface::LEADS);

        $notesFilter = new NotesFilter();
        $notesFilter->setEntityIds($massCopyLeadsMap->getOriginIds());

        try {
            $notesCollection = $notesService->get($notesFilter);
        } catch (AmoCRMApiNoContentException $e) {
            // Не нашли примечаний
            $notesCollection = new NotesCollection();
        }

        if (!$notesCollection->isEmpty()) {
            $newNotesCollection = new NotesCollection;
            foreach ($notesCollection as $note) {
                $newNote = $this->cloneCommonNote($note);
                $newNote->setEntityId($massCopyLeadsMap->getCopyByOriginId($note->getEntityId())->getId());

                $newNotesCollection->add($newNote);
            }

            $notesService->add($newNotesCollection);
        }
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
