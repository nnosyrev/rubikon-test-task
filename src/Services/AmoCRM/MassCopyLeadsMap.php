<?php

namespace App\Services\AmoCRM;

use AmoCRM\Models\LeadModel;
use Exception;

final class MassCopyLeadsMap
{
    private array $map;

    public function addPair(LeadModel $origin, LeadModel $copy): void
    {
        $this->map[$origin->getId()] = $copy;
    }

    public function getOriginIds(): array
    {
        return array_keys($this->map);
    }

    public function getCopyByOriginId(int $id): LeadModel
    {
        if (!array_key_exists($id, $this->map)) {
            throw new Exception('Something went wrong.');
        }

        return $this->map[$id];
    }
}
