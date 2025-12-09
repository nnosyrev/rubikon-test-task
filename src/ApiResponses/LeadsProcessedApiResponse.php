<?php

namespace App\ApiResponses;

use AmoCRM\Collections\Leads\LeadsCollection;
use Symfony\Component\HttpFoundation\Response;

final readonly class LeadsProcessedApiResponse implements ApiResponseInterface
{
    public function __construct(
        private LeadsCollection $leads
    ) {
    }

    public function getData(): array
    {
        return [
            'status' => 'success',
            'message' => \sprintf('%d leads were successfully processed', $this->leads->count())
        ];
    }

    public function getHttpStatus(): int
    {
        return Response::HTTP_OK;
    }
}
