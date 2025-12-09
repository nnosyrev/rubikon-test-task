<?php

namespace App\ApiResponses;

use Symfony\Component\HttpFoundation\Response;

final readonly class LeadsNotFoundApiResponse implements ApiResponseInterface
{
    public function getData(): array
    {
        return [
            'status' => 'success',
            'message' => \sprintf('No leads were found for processing')
        ];
    }

    public function getHttpStatus(): int
    {
        return Response::HTTP_OK;
    }
}
