<?php

require_once __DIR__ . '/../vendor/autoload.php';

class PusherHandler {
    private static $pusher;

    public static function getInstance() {
        if (!self::$pusher) {
            self::$pusher = new Pusher\Pusher(
                "6a9e4051ca747ed900ca", // key
                "7087286d3e5e7cdfb602", // secret
                "2109436", // app_id
                array('cluster' => 'ap2')
            );
        }
        return self::$pusher;
    }

    public static function trigger($channel, $event, $data) {
        return self::getInstance()->trigger($channel, $event, $data);
    }
}
