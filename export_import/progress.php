<?php
session_start();
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(404);
    exit;
}

echo json_encode([
    'progress' => $_SESSION['progress'] ?? 0
]);
