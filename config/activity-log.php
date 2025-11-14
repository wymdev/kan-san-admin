<?php

return [
    // Log system actions (when no user is authenticated)
    'log_system_actions' => true,
    
    // Sensitive fields to redact from logs
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'token',
        'api_token',
        'remember_token',
        'secret',
        'api_secret',
        'private_key',
        'card_number',
        'cvv',
        'ssn',
    ],
    
    // Threshold for slow requests (in milliseconds)
    'slow_request_threshold' => 1000,
    
    // Auto cleanup old logs (in days, 0 to disable)
    'auto_cleanup_days' => 90,
    
    // Maximum request body size to log
    'max_request_body_size' => 50,
];
