<?php
declare(strict_types=1);

/*
 * Local OAuth credentials for SmartBooking V2.
 *
 * IMPORTANT:
 * - Paste the NEW Google OAuth client values below.
 * - Do not commit this file to GitHub.
 * - Keep the redirect URI exactly as written for local XAMPP testing.
 */

return [
    'google' => [
        'client_id' => '110502104428-cpiov7h1il34v8hbtqq73d4u898rpa1q.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect_uri' =>
            'http://localhost/smartbooking_v2/auth/google-callback.php',
    ],

    'facebook' => [
        'app_id' => '1077977044932044',
        'app_secret' => 'd22f72169b17b602bc50f564a9a276df',
        'redirect_uri' =>
            'http://localhost/smartbooking_v2/auth/facebook-callback.php',
        'graph_version' => 'v25.0',
    ],
];
