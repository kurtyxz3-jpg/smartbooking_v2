<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (
    !isset($_SESSION['user_id'])
    || ($_SESSION['role'] ?? '') !== 'customer'
) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please sign in as a customer.',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);

$generationId = isset($payload['generation_id'])
    ? (int) $payload['generation_id']
    : 0;

$selectedNumber = isset($payload['selected_number'])
    ? (int) $payload['selected_number']
    : 0;

$selectedUrl = trim((string) ($payload['selected_url'] ?? ''));

if (
    $selectedNumber < 1
    || $selectedNumber > 3
    || $selectedUrl === ''
) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Choose a valid generated design.',
    ]);
    exit;
}

require_once '../../config/database.php';

try {
    if ($generationId > 0) {
        $statement = $pdo->prepare(
            "UPDATE ai_nail_generations
             SET selected_image_number = ?,
                 selected_image_url = ?,
                 status = 'selected',
                 selected_at = NOW()
             WHERE id = ?
               AND customer_id = ?"
        );

        $statement->execute([
            $selectedNumber,
            $selectedUrl,
            $generationId,
            (int) $_SESSION['user_id'],
        ]);
    }

    $_SESSION['selected_ai_design'] = [
        'generation_id' => $generationId,
        'selected_number' => $selectedNumber,
        'selected_url' => $selectedUrl,
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Your preferred nail design was saved.',
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'The selected design could not be saved.',
    ]);
}
