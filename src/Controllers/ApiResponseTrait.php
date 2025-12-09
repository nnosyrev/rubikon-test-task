<?php

namespace App\Controllers;

use App\ApiResponses\ApiResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    private function apiResponse(ApiResponseInterface $apiResponse): Response
    {
        return new JsonResponse($apiResponse->getData(), $apiResponse->getHttpStatus());
    }
}
