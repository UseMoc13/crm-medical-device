<?php

return [
    // All row-level pilots are OFF until roles are configured and tested.
    'customers_enabled' => (bool) env('CRM_DATA_SCOPE_CUSTOMERS_ENABLED', false),
    'leads_enabled' => (bool) env('CRM_DATA_SCOPE_LEADS_ENABLED', false),
    'service_tickets_enabled' => (bool) env('CRM_DATA_SCOPE_SERVICE_TICKETS_ENABLED', false),
    'maintenance_schedules_enabled' => (bool) env('CRM_DATA_SCOPE_MAINTENANCE_ENABLED', false),
    'installations_enabled' => (bool) env('CRM_DATA_SCOPE_INSTALLATIONS_ENABLED', false),
    'activities_enabled' => (bool) env('CRM_DATA_SCOPE_ACTIVITIES_ENABLED', false),
    'opportunities_enabled' => (bool) env('CRM_DATA_SCOPE_OPPORTUNITIES_ENABLED', false),
];
