<?php
require_once __DIR__ . '/JWT.php';

use App\Helpers\JWT;

class JWTHelper {
    private static $secret_key = 'cambiar_por_una_clave_segura_unefa_2026';
    private static $algorithm = 'HS256';

    public static function generateToken($userId, $email = null, $role = null, $expiry = 3600) {
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'role' => $role
        ];

        return JWT::encode($payload, self::$secret_key, $expiry, self::$algorithm);
    }

    public static function validateToken($token) {
        try {
            $payload = JWT::decode($token, self::$secret_key, [self::$algorithm]);
            return $payload; // devuelve array con claims
        } catch (\Exception $e) {
            return null;
        }
    }
}
