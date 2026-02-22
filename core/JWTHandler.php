<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    public static function encode($payload) {
        $secret = getenv('JWT_SECRET');
        if (!$secret) {
            throw new Exception('JWT_SECRET not set');
        }
        return JWT::encode($payload, $secret, 'HS256');
    }

    public static function decode($token) {
        $secret = getenv('JWT_SECRET');
        if (!$secret) {
            throw new Exception('JWT_SECRET not set');
        }
        
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return (array) $decoded;
    }
}
