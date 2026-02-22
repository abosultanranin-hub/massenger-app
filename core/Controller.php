<?php

class Controller {
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function getInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    // JWT Validation helper could go here or in a Middleware
    protected function validateAuth() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $jwt = $matches[1];
        try {
            // Simple JWT decode logic (or use library if possible)
            // For Pure PHP without composers, we implement a simple decoder
            $token = JWTHandler::decode($jwt);
            return $token;
        } catch (Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid Token'], 401);
        }
    }
}
