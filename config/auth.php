<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Définit le guard par défaut et le broker pour les mots de passe.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'), // On met api par défaut pour ton API JWT
        'passwords' => env('AUTH_PASSWORD_BROKER', 'utilisateurs'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Définition des guards pour l'application.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'utilisateurs',
        ],

        'api' => [
            'driver' => 'jwt',
            'provider' => 'utilisateurs',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Définition des providers pour récupérer les utilisateurs depuis la base.
    |
    */

    'providers' => [
        'utilisateurs' => [
            'driver' => 'eloquent',
            'model' => App\Models\Utilisateur::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'utilisateurs' => [
            'provider' => 'utilisateurs',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Durée avant que l'utilisateur doive confirmer à nouveau son mot de passe.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
