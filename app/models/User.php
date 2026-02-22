<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($email) {
        $stmt = $this->db->prepare("INSERT INTO users (email) VALUES (?)");
        if ($stmt->execute([$email])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateProfile($userId, $name, $about, $avatarUrl) {
        $query = "UPDATE users SET name = ?, about = ?, avatar_url = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$name, $about, $avatarUrl, $userId]);
    }

    public function updateOnlineStatus($userId, $isOnline) {
        $query = "UPDATE users SET is_online = ?, last_seen = NOW() WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$isOnline ? 1 : 0, $userId]);
    }

    public function logAuthAction($userId, $action) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $query = "INSERT INTO auth_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId, $action, $ip, $agent]);
    }

    public function cleanupOfflineUsers() {
         $query = "UPDATE users SET is_online = 0 WHERE is_online = 1 AND last_seen < DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute();
        
        // اي موضوع  مرتبط بالداتا بيس   بروح احطو بال  modle 
        
    }


}
