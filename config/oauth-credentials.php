<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SMARTBOOKING SOCIAL LOGIN CREDENTIALS
|--------------------------------------------------------------------------
|
| Google and Facebook issue these values to the owner of the application.
| They cannot be generated inside PHP or supplied by another website.
|
| GOOGLE CALLBACK:
| http://localhost/smartbooking_v2/auth/google-callback.php
|
| FACEBOOK CALLBACK:
| http://localhost/smartbooking_v2/auth/facebook-callback.php
|
| Do not upload this file to a public GitHub repository after adding secrets.
*/

return [
    'google' => [
        'client_id' => 'PASTE_GOOGLE_CLIENT_ID_HERE',
        'client_secret' => 'PASTE_GOOGLE_CLIENT_SECRET_HERE',
        'redirect_uri' =>
            'http://localhost/smartbooking_v2/auth/google-callback.php',
    ],

    'facebook' => [
        'app_id' => 'PASTE_FACEBOOK_APP_ID_HERE',
        'app_secret' => 'PASTE_FACEBOOK_APP_SECRET_HERE',
        'redirect_uri' =>
            'http://localhost/smartbooking_v2/auth/facebook-callback.php',
        'graph_version' => 'v25.0',
    ],
];
