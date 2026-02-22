<?php

// Basic error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS (also handled in Router, but keep here to cover early failures)
// CORS headers are handled by .htaccess to prevent duplication
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/EnvLoader.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/JWTHandler.php';

// Load Env
try {
    EnvLoader::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    // Continue if .env missing, maybe env vars are set by server
}

// Router Setup
$router = new Router();
$router->get('/', function() {
    echo json_encode(['status' => 'ok', 'message' => 'API is running']);
});

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ChatController.php';
require_once __DIR__ . '/../app/controllers/AIController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

// Auth Routes
$router->post('/auth/register', [AuthController::class, 'register']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/me', [AuthController::class, 'me']);

// User/Pusher Routes
$router->post('/user/ping', [UserController::class, 'ping']);
$router->post('/pusher/auth', [UserController::class, 'pusherAuth']);

// Chat Routes
$router->get('/chats', [ChatController::class, 'index']);
$router->post('/chats', [ChatController::class, 'start']);
$router->get('/chats/messages', [ChatController::class, 'history']);
$router->post('/chats/messages', [ChatController::class, 'send']);
$router->post('/chats/read', [ChatController::class, 'readMessages']);

// AI Routes
$router->post('/ai/chat', [AIController::class, 'sendMessage']);

// Run Router
$router->resolve();











