<?php

return [
    // A6.11 Customer-only pilot. Keep false until the 38 NULL View grants
    // have been reviewed and explicitly configured in Roles.
    'customers_enabled' => (bool) env('CRM_DATA_SCOPE_CUSTOMERS_ENABLED', false),
];
