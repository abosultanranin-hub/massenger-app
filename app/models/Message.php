<?php

class Message {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($chatId, $senderId, $content, $type = 'text', $mediaUrl = null, $replyTo = null) {
        $stmt = $this->db->prepare("INSERT INTO messages (chat_id, sender_id, content, type, media_url, reply_to_message_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$chatId, $senderId, $content, $type, $mediaUrl, $replyTo])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getByChat($chatId, $limit = 50, $offset = 0) {
        $query = "
            SELECT m.*, u.name as sender_name, u.email as sender_email,
                   m.created_at as time,
                   (SELECT status FROM message_status WHERE message_id = m.message_id AND user_id != m.sender_id LIMIT 1) as status
            FROM messages m 
            JOIN users u ON m.sender_id = u.user_id
            WHERE m.chat_id = ? 
            ORDER BY m.created_at  DESC 
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $chatId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $messages = $stmt->fetchAll();
        return array_reverse($messages); // Return chronological order
    }

    public function createStatus($messageId, $userId, $status = 'sent') {
        $stmt = $this->db->prepare("INSERT INTO message_status (message_id, user_id, status) VALUES (?, ?, ?)");
        return $stmt->execute([$messageId, $userId, $status]);
    }
    
    // For participants to mark messages as delivered/read
    public function updateStatus($messageId, $userId, $status) {
         $stmt = $this->db->prepare("UPDATE message_status SET status = ? WHERE message_id = ? AND user_id = ?");
         return $stmt->execute([$status, $messageId, $userId]);
    }

    public function markAsRead($chatId, $userId) {
        $query = "
           UPDATE message_status ms
           JOIN messages m ON ms.message_id = m.message_id
           SET ms.status = 'read'
           WHERE m.chat_id = ? AND ms.user_id = ? AND ms.status != 'read'
        ";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$chatId, $userId]);
   }
}
