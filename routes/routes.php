<?php

use App\Controllers\LeadController;

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('POST', '/leads/change-status-to-waiting', [LeadController::class, 'changeStatusToWaitingAction']);
    $r->addRoute('POST', '/leads/copy-to-waiting-status', [LeadController::class, 'copyToWaitingStatusAction']);
};
