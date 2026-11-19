<?php

/* A7.6.5.5: isolated staging-only READ gate. Default OFF. */
return [
    'staging_parity_enabled' => (bool) env('CRM_A7655_STAGING_PARITY_ENABLED', false),
];
