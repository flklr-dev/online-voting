<?php

// app/Traits/LogsActivity.php

namespace App\Traits;

use App\Services\ActivityLogService;

trait LogsActivity
{
    protected function logActivity($action, $module, $description)
    {
        ActivityLogService::log($action, $module, $description);
    }
}