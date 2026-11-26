<?php

/**
 * A7.9.7 assignment *job-function* identities. These are NOT RBAC grants.
 * Match complete role names, normalized to lowercase; do not infer from user names.
 * Additional authorized operational aliases can be added here without DB migrations.
 */
return [
    'roles' => [
        'technician' => [
            'technician', 'uat technician', 'service engineer', 'field service engineer',
            'installation engineer', 'maintenance engineer', 'biomedical engineer',
        ],
        'sales' => [
            'sales', 'sales owner', 'uat sales owner', 'sales executive',
            'sales representative', 'sales supervisor', 'account manager',
        ],
    ],
];
