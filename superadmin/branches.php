<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
// Retire the old screen and mutation endpoint, including saved forms.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(410);
    exit('This management endpoint has been retired. Use Salon Settings.');
}
header('Location: salon-settings.php', true, 302);
exit;
