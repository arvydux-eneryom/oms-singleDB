<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | You can find your API key on your Bugsnag dashboard.
    |
    */

    'api_key' => env('BUGSNAG_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | App Version
    |--------------------------------------------------------------------------
    |
    | Set the version of your application. This is used to track which
    | version of your application each error occurs in.
    |
    */

    'app_version' => env('APP_VERSION'),

    /*
    |--------------------------------------------------------------------------
    | Release Stage
    |--------------------------------------------------------------------------
    |
    | Set the release stage of the application, such as production, staging,
    | or development.
    |
    */

    'release_stage' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Release Stages
    |--------------------------------------------------------------------------
    |
    | Set which release stages should send notifications to Bugsnag.
    |
    */

    'notify_release_stages' => env('BUGSNAG_NOTIFY_RELEASE_STAGES') ?
        explode(',', env('BUGSNAG_NOTIFY_RELEASE_STAGES')) :
        ['production', 'staging'],

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | Set the endpoint to send errors to. This is useful if you're using
    | Bugsnag On-Premise.
    |
    */

    'endpoint' => env('BUGSNAG_ENDPOINT'),

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Set the strings to filter out from the metadata arrays before sending
    | them to Bugsnag. Use this if you want to ensure you don't send
    | sensitive data such as passwords, and credit card numbers.
    |
    */

    'filters' => ['password', 'password_confirmation', 'api_key', 'api_hash', 'api_id', 'session_data', 'token'],

    /*
    |--------------------------------------------------------------------------
    | Project Root
    |--------------------------------------------------------------------------
    |
    | Set the path to your project root. This is used to mark stack trace
    | lines in your code and improve the display of error reports.
    |
    */

    'project_root' => base_path(),

    /*
    |--------------------------------------------------------------------------
    | Strip Path
    |--------------------------------------------------------------------------
    |
    | Set the path to strip from stack trace file paths. This is useful if
    | you want to keep file paths short in your Bugsnag dashboard.
    |
    */

    'strip_path' => base_path(),

    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like to log all SQL queries to Bugsnag.
    |
    */

    'query' => env('BUGSNAG_QUERY', false),

    /*
    |--------------------------------------------------------------------------
    | Bindings
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like to log SQL query bindings to Bugsnag.
    |
    */

    'bindings' => env('BUGSNAG_QUERY_BINDINGS', false),

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | Enable automatic user detection and association with errors.
    |
    */

    'user' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logger Notify Level
    |--------------------------------------------------------------------------
    |
    | The minimum log level at which Bugsnag should be notified.
    | Valid options: debug, info, notice, warning, error, critical, alert, emergency
    |
    */

    'logger_level' => env('BUGSNAG_LOGGER_LEVEL', 'error'),

];
