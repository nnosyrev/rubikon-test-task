<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use App\Config\Config;
use Psr\Container\ContainerInterface;

return [
    AmoCRMApiClient::class => function (ContainerInterface $c) {
        $longLivedAccessToken = new LongLivedAccessToken(Config::get('AMOCRM_ACCESS_TOKEN'));

        return (new AmoCRMApiClient())
            ->setAccessToken($longLivedAccessToken)
            ->setAccountBaseDomain(Config::get('AMOCRM_ACCOUNT_BASE_DOMAIN'));
    },
];
