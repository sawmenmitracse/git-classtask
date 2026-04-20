<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

function send_json(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function require_post_method(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        send_json([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }
}

function get_request_data(): array {
    $rawInput = file_get_contents('php://input');
    if ($rawInput !== false && trim($rawInput) !== '') {
        $decoded = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    return $_POST;
}

function validate_csrf_token(string $csrfToken): void {
    if ($csrfToken === '' || $csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
        send_json([
            'success' => false,
            'message' => 'Invalid CSRF token'
        ], 403);
    }
}
?>
