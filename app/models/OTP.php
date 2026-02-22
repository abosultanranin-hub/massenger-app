<?php

class OTP {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($email, $code) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        // Delete previous OTPs for this email
        $del = $this->db->prepare("DELETE FROM otp_verifications WHERE email = ?");
        $del->execute([$email]);

        $stmt = $this->db->prepare("INSERT INTO otp_verifications (email, otp_code, expires_at) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $code, $expiresAt]);
    }

    public function verify($phone, $code) {
        $stmt = $this->db->prepare("SELECT * FROM otp_verifications WHERE phone_number = ? AND otp_code = ? AND expires_at > NOW()");
        $stmt->execute([$phone, $code]);
        return $stmt->fetch();
    }

    public function delete($phone) {
        $stmt = $this->db->prepare("DELETE FROM otp_verifications WHERE phone_number = ?");
        return $stmt->execute([$phone]);
    }
}
