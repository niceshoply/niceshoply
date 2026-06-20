<?php
/**
 * Sentry Configuration
 *
 * @see https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/
 */

return [

    'dsn' => env('SENTRY_LARAVEL_DSN'),

    'release' => env('SENTRY_RELEASE', config('niceshoply.version', '1.0.0')),

    'environment' => env('APP_ENV', 'production'),

    'sample_rate' => env('SENTRY_SAMPLE_RATE', 1.0),

    // Performance monitoring
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),

    'send_default_pii' => false,

    // Exceptions that should not be reported to Sentry
    'ignore_exceptions' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],

];
