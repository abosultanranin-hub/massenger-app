<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/JWTHandler.php';
require_once __DIR__ . '/../app/models/User.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$jwt = $matches[1];
try {
    $token = JWTHandler::decode($jwt);
    $userId = $token['user_id'];

    $userModel = new User();
    $userModel->updateOnlineStatus($userId, true);

    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Token']);
}
