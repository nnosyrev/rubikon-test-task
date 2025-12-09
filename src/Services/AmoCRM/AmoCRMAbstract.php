<?php

namespace App\Services\AmoCRM;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use App\Config\Config;

abstract class AmoCRMAbstract
{
    protected ?AmoCRMApiClient $apiClient = null;

    protected function getApiClient(): AmoCRMApiClient
    {
        if (is_null($this->apiClient)) {
            $longLivedAccessToken = new LongLivedAccessToken(Config::get('AMOCRM_ACCESS_TOKEN'));

            $this->apiClient = (new AmoCRMApiClient())
                ->setAccessToken($longLivedAccessToken)
                ->setAccountBaseDomain(Config::get('AMOCRM_ACCOUNT_BASE_DOMAIN'));
        }

        return $this->apiClient;
    }
}
