<?php

class Chat {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // specific to new schema
    public function getChatsByUser($userId) {
        // Complex query to get chats with last message and unread count
        $query = "
            SELECT 
                c.chat_id, 
                c.type, 
                c.subject, 
                c.icon_url,
                m.content as last_message, 
                m.created_at as last_message_time,
                m.type as last_message_type,
                (SELECT COUNT(*) FROM message_status ms 
                 WHERE ms.message_id IN (SELECT message_id FROM messages WHERE chat_id = c.chat_id) 
                 AND ms.user_id = ? AND ms.status != 'read') as unread_count,
                (SELECT COUNT(*) FROM messages m 
                 JOIN message_status ms ON ms.message_id = m.message_id 
                 WHERE m.chat_id = c.chat_id AND m.sender_id = ? 
                 AND ms.user_id = (SELECT user_id FROM chat_participants WHERE chat_id = c.chat_id AND user_id != ? LIMIT 1) 
                 AND ms.status != 'read') as unread_by_other_count,
                 CASE 
                    WHEN c.type = 'individual' THEN (
                        SELECT u.name FROM users u 
                        JOIN chat_participants cp ON cp.user_id = u.user_id 
                        WHERE cp.chat_id = c.chat_id AND u.user_id != ?
                    ) 
                    ELSE c.subject 
                 END as display_name,
                  CASE 
                    WHEN c.type = 'individual' THEN (
                        SELECT u.avatar_url FROM users u 
                        JOIN chat_participants cp ON cp.user_id = u.user_id 
                        WHERE cp.chat_id = c.chat_id AND u.user_id != ?
                    ) 
                    ELSE c.icon_url 
                 END as display_icon,
                 CASE 
                    WHEN c.type = 'individual' THEN (
                        SELECT u.email FROM users u 
                        JOIN chat_participants cp ON cp.user_id = u.user_id 
                        WHERE cp.chat_id = c.chat_id AND u.user_id != ?
                    ) 
                    ELSE NULL 
                 END as other_email
            FROM chats c
            JOIN chat_participants cp ON c.chat_id = cp.chat_id
            LEFT JOIN messages m ON m.message_id = (
                SELECT MAX(message_id) FROM messages WHERE chat_id = c.chat_id
            )
            WHERE cp.user_id = ?
            ORDER BY m.created_at DESC
        ";
        
        $stmt = $this->db->prepare($query);
        // We pass userId 7 times: unread_count, unread_by_other (sender), unread_by_other (other), name, icon, other_email, main WHERE
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    public function createGenericChat($type, $subject = null, $creatorId = null) {
        $stmt = $this->db->prepare("INSERT INTO chats (type, subject, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$type, $subject, $creatorId]);
        return $this->db->lastInsertId();
    }

    public function addParticipant($chatId, $userId, $role = 'member') {
        $stmt = $this->db->prepare("INSERT INTO chat_participants (chat_id, user_id, role) VALUES (?, ?, ?)");
        return $stmt->execute([$chatId, $userId, $role]);
    }

    public function findIndividualChat($user1, $user2) {
        // Find if a chat exists between these two users only
        $query = "
            SELECT c.chat_id 
            FROM chats c
            JOIN chat_participants cp1 ON c.chat_id = cp1.chat_id
            JOIN chat_participants cp2 ON c.chat_id = cp2.chat_id
            WHERE c.type = 'individual' 
            AND cp1.user_id = ? 
            AND cp2.user_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user1, $user2]);
        return $stmt->fetch();
    }

    public function getParticipants($chatId) {
        $stmt = $this->db->prepare("SELECT user_id FROM chat_participants WHERE chat_id = ?");
        $stmt->execute([$chatId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
