<?php

return [
    // All row-level pilots are OFF until roles are configured and tested.
    'customers_enabled' => (bool) env('CRM_DATA_SCOPE_CUSTOMERS_ENABLED', false),
    'leads_enabled' => (bool) env('CRM_DATA_SCOPE_LEADS_ENABLED', false),
    'opportunities_enabled' => (bool) env('CRM_DATA_SCOPE_OPPORTUNITIES_ENABLED', false),
];
