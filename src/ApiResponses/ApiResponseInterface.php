<?php

namespace App\ApiResponses;

interface ApiResponseInterface
{
    public function getData(): array;

    public function getHttpStatus(): int;
}
