<?php

use App\Controllers\LeadController;

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/leads/status/change-to-wait', [LeadController::class, 'statusToWaitingAction']);
    $r->addRoute('GET', '/leads/duplication', [LeadController::class, 'duplicationAction']);
};
