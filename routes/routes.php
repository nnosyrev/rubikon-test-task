<?php

use App\Controllers\LeadController;

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/leads/change-status-to-waiting', [LeadController::class, 'changeStatusToWaitingAction']);
    $r->addRoute('GET', '/leads/duplication', [LeadController::class, 'duplicationAction']);
};
