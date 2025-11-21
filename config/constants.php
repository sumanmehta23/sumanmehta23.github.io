<?php

return [
    'AGENT' => 'WebRegistration',
    'PATH_TO_LOGS'=>storage_path('logs'),
    'IS_WRITE_DEBUG_LOG'=> false,
    
    /*
    |--------------------------------------------------------------------------
    | Inactive Users Configuration
    |--------------------------------------------------------------------------
    |
    | Number of days of inactivity before marking a user as inactive.
    | Users who haven't logged in for this many days will be marked as inactive.
    |
    */
    'INACTIVE_USER_DAYS' => env('INACTIVE_USER_DAYS', 30),
];
