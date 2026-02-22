<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../core/PusherHandler.php';

class UserController extends Controller {

    // POST /user/ping
    public function ping() {
        $user = $this->validateAuth();
        // Update DB
        $userModel = new User();
        // Set online status to true (1)
        $userModel->updateOnlineStatus($user['user_id'], true);
        
        // Passive cleanup: Mark others offline if they are stale
        $userModel->cleanupOfflineUsers();
        
        $this->jsonResponse(['status' => 'success', 'message' => 'Pong']);
    }

    // POST /pusher/auth
    public function pusherAuth() {
        $user = $this->validateAuth();
        $data = $this->getInput(); // Should contain socket_id and channel_name

        // Standard Pusher Auth expects POST form data usually, but if using JSON body:
        // Client side pusher.js sends application/x-www-form-urlencoded by default for auth.
        // Let's handle both.
        
        $socketId = $_POST['socket_id'] ?? $data['socket_id'] ?? null;
        $channelName = $_POST['channel_name'] ?? $data['channel_name'] ?? null;

        if (!$socketId || !$channelName) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }

        $pusher = PusherHandler::getInstance();

        // Presence channel data
        $presenceData = [
            'user_id' => $user['user_id'],
            'user_info' => [
                'name' => $user['name'],
                'email' => $user['email'],
                'avatar_url' => $user['avatar_url']
            ]
        ];

        try {
            // authorizeChannel(channel, socket_id, custom_data)
            echo $pusher->authorizeChannel($channelName, $socketId, json_encode($presenceData));
        } catch (Exception $e) {
            http_response_code(403);
            echo "Forbidden";
        }
    }
}
