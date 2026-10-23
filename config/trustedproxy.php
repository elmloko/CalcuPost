<?php

return [
    // Comma-separated proxy IP addresses or CIDR ranges. Empty means trust none.
    'proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
