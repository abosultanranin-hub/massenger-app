<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../models/Message.php';

class AIController extends Controller {
    
    public function sendMessage() {
        $user = $this->validateAuth();
        $data = $this->getInput();
        
        // We use chat_id as the conversation container in this project
        $chatId = $data['chat_id'] ?? null;
        $content = $data['content'] ?? null;

        if (!$chatId || !$content) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Missing fields'], 400);
        }

        // Validate access: user must be participant in chat
        $chatModel = new Chat();
        $participants = $chatModel->getParticipants((int)$chatId);
        if (!in_array((int)$user['user_id'], array_map('intval', $participants), true)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Chat not found'], 404);
        }

        // Save User Message
        $messageModel = new Message();
        $messageModel->create((int)$chatId, (int)$user['user_id'], $content, 'text');

        // Get Context (Last 10 messages for context)
        // We'll reuse existing history call: getByChat returns last 50; we'll slice last 10.
        $history = $messageModel->getByChat((int)$chatId, 50, 0);
        $history = array_slice($history, -10);
        
        // Prepare OpenAI Messages
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => 'You are a helpful AI assistant.'];
        foreach ($history as $msg) {
            $role = ((int)$msg['sender_id'] === (int)$user['user_id']) ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => (string)($msg['content'] ?? '')];
        }

        // Call AI API
        try {
            $aiResponse = $this->callOpenAI($messages);
        } catch (Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'AI Service Unavailable: ' . $e->getMessage()], 503);
        }

        // Save AI Message
        // Save as a system/ai message (sender_id = current user for now to fit schema)
        // If you want a dedicated "AI user", we can add it later.
        $messageModel->create((int)$chatId, (int)$user['user_id'], $aiResponse, 'system');

        $this->jsonResponse([
            'status' => 'success', 
            'data' => [
                'user_message' => $content,
                'ai_response' => $aiResponse
            ]
        ]);
    }

    private function callOpenAI($messages) {
        $apiKey = getenv('AI_API_KEY');
        if (!$apiKey) {
            throw new Exception("Missing AI_API_KEY in .env");
        }
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => 'gpt-3.5-turbo', // Or gpt-4
            'messages' => $messages,
            'max_tokens' => 500
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        // Disable SSL verification for local dev if needed, strictly check in prod
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $json['error']['message'] ?? 'Unknown Error';
            throw new Exception("OpenAI API Error ($httpCode): $errorMsg");
        }

        return $json['choices'][0]['message']['content'] ?? '';
    }
}
