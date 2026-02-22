<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/JWTHandler.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller {
    
    private function normalizeEmail($emailRaw) {
        $email = trim((string)$emailRaw);
        $email = strtolower($email);
        return $email;
    }

    // POST /auth/register
    // Register with email only (no password)
    public function register() {
        $data = $this->getInput();

        if (!isset($data['email'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Email is required'], 400);
        }

        $email = $this->normalizeEmail($data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid email format'], 400);
        }

        $userModel = new User();
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Email already registered'], 409);
        }

        $userId = $userModel->create($email);
        if (!$userId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Failed to create user'], 500);
        }

        $payload = [
            'user_id' => (int)$userId,
            'email' => $email,
            'exp' => time() + (60 * 60 * 24 * 365) // 1 year
        ];
        $token = JWTHandler::encode($payload);

        // Update online status and log
        $userModel->updateOnlineStatus($userId, true);
        $userModel->logAuthAction($userId, 'register');

        $this->jsonResponse([
            'status' => 'success',
            'token' => $token,
            'user_id' => (int)$userId,
            'is_new_user' => true
        ]);
    }

    // POST /auth/login
    // Login with email only (no password)
    public function login() {
        $data = $this->getInput();

        if (!isset($data['email'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Email is required'], 400);
        }

        $email = $this->normalizeEmail($data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid email format'], 400);
        }

        $userModel = new User();
        $existingUser = $userModel->findByEmail($email);
        if (!$existingUser) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Account not found. Please register first.'], 404);
        }

        $payload = [
            'user_id' => (int)$existingUser['user_id'],
            'email' => $email,
            'exp' => time() + (60 * 60 * 24 * 365) // 1 year
        ];
        $token = JWTHandler::encode($payload);

        // Update online status and log
        $userModel->updateOnlineStatus((int)$existingUser['user_id'], true);
        $userModel->logAuthAction((int)$existingUser['user_id'], 'login');

        $this->jsonResponse([
            'status' => 'success',
            'token' => $token,
            'user_id' => (int)$existingUser['user_id'],
            'is_new_user' => false
        ]);
    }

    // GET /auth/me
    public function me() {
        $user = $this->validateAuth();
        $this->jsonResponse(['status' => 'success', 'data' => $user]);
    }
    // POST /auth/logout
    public function logout() {
        $user = $this->validateAuth();
        $userId = $user['user_id']; // validateAuth returns decoded token payload or user data

        $userModel = new User();
        $userModel->updateOnlineStatus($userId, false);
        $userModel->logAuthAction($userId, 'logout');

        $this->jsonResponse(['status' => 'success', 'message' => 'Logged out successfully']);
    }

    // GET /auth/online-status
    public function onlineStatus() {
        $user = $this->validateAuth();
        $userId = $user['user_id'];

        $userModel = new User();
        $userData = $userModel->findById($userId);

        $status = $userData['is_online'] ? 'online' : 'offline';
        $lastSeen = $userData['last_seen'];

        $this->jsonResponse(['status' => 'success', 'data' => [
            'user_id' => $userId,
            'status' => $status,
            'last_seen' => $lastSeen
        ]]);
    }
}
