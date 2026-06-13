<?php

namespace App\Utils;

use DateTime;
use PDO;

class Security {
    public static function isHttpsRequest(): bool {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        return isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443;
    }

    public static function cookieOptions(int $expires = 0): array {
        return [
            'expires' => $expires,
            'path' => '/',
            'secure' => self::isHttpsRequest(),
            'httponly' => true,
            'samesite' => 'Strict',
        ];
    }

    public static function setAuthCookie(string $name, string $value, int $expires = 0): void {
        setcookie($name, $value, self::cookieOptions($expires));
    }

    public static function clearAuthCookie(string $name): void {
        setcookie($name, '', self::cookieOptions(time() - 3600));
        unset($_COOKIE[$name]);
    }

    public static function clearAuthCookies(array $names = ['jwt', 'session_token']): void {
        foreach ($names as $name) {
            self::clearAuthCookie($name);
        }
    }

    public static function checkRole(PDO $db, int $userId, array $allowedRoles): bool {
        if ($userId <= 0 || empty($allowedRoles)) {
            return false;
        }

        $query = "SELECT u.role_id, r.role_name
                  FROM user u
                  INNER JOIN roles r ON u.role_id = r.id
                  WHERE u.id = :user_id
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $allowedLower = array_map(static fn($role) => strtolower((string) $role), $allowedRoles);
        $roleName = strtolower((string) ($user['role_name'] ?? ''));

        if (in_array($roleName, $allowedLower, true)) {
            return true;
        }

        return in_array((string) ($user['role_id'] ?? ''), array_map('strval', $allowedRoles), true);
    }

    public static function checkPermission(PDO $db, int $userId, string $permissionKey): bool {
        if ($userId <= 0 || trim($permissionKey) === '') {
            return false;
        }

        $query = "SELECT 1
                  FROM user u
                  INNER JOIN role_permissions rp ON u.role_id = rp.role_id
                  INNER JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = :user_id
                    AND p.permission_key = :permission_key
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':permission_key' => trim($permissionKey),
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function isLockedOut(PDO $db, int $userId): bool {
        $query = "SELECT lockout_until FROM user WHERE id = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $lockoutUntil = $stmt->fetchColumn();

        if (empty($lockoutUntil)) {
            return false;
        }

        return new DateTime($lockoutUntil) > new DateTime('now');
    }

    public static function failedLogin(PDO $db, int $userId): array {
        $query = "SELECT failed_logins, lockout_until FROM user WHERE id = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['locked' => false, 'failed_logins' => 0, 'lockout_until' => null];
        }

        $failedLogins = (int) ($user['failed_logins'] ?? 0);
        $lockoutUntil = $user['lockout_until'] ?? null;

        if (!empty($lockoutUntil) && new DateTime($lockoutUntil) > new DateTime('now')) {
            return [
                'locked' => true,
                'failed_logins' => $failedLogins,
                'lockout_until' => $lockoutUntil,
            ];
        }

        $failedLogins++;
        $newLockout = null;

        if ($failedLogins >= 5) {
            $newLockout = (new DateTime('now'))->modify('+15 minutes')->format('Y-m-d H:i:s');
        }

        $update = $db->prepare("UPDATE user
                                SET failed_logins = :failed_logins,
                                    lockout_until = :lockout_until
                                WHERE id = :user_id");
        $update->execute([
            ':failed_logins' => $failedLogins,
            ':lockout_until' => $newLockout,
            ':user_id' => $userId,
        ]);

        return [
            'locked' => $newLockout !== null,
            'failed_logins' => $failedLogins,
            'lockout_until' => $newLockout,
        ];
    }

    public static function resetFailedLogins(PDO $db, int $userId): bool {
        $query = "UPDATE user
                  SET failed_logins = 0,
                      lockout_until = NULL,
                      last_login_at = CURRENT_TIMESTAMP
                  WHERE id = :user_id";
        $stmt = $db->prepare($query);

        return $stmt->execute([':user_id' => $userId]);
    }

    public static function createSecureSession(PDO $db, int $userId, ?string $userAgent = null, ?string $ipAddress = null, int $minutes = 30): string {
        $token = bin2hex(random_bytes(32));
        $now = new DateTime('now');
        $expiresAt = (clone $now)->modify('+' . max(1, $minutes) . ' minutes')->format('Y-m-d H:i:s');

        $query = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, last_activity, expires_at)
                  VALUES (:user_id, :session_token, :ip_address, :user_agent, :last_activity, :expires_at)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':session_token' => $token,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':last_activity' => $now->format('Y-m-d H:i:s'),
            ':expires_at' => $expiresAt,
        ]);

        return $token;
    }

    public static function touchSession(PDO $db, string $token, int $minutes = 30): bool {
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        $query = "UPDATE user_sessions
                  SET last_activity = CURRENT_TIMESTAMP,
                      expires_at = :expires_at
                  WHERE session_token = :session_token";
        $stmt = $db->prepare($query);

        return $stmt->execute([
            ':expires_at' => (new DateTime('now'))->modify('+' . max(1, $minutes) . ' minutes')->format('Y-m-d H:i:s'),
            ':session_token' => $token,
        ]);
    }

    public static function invalidateSession(PDO $db, string $token): bool {
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        $stmt = $db->prepare("DELETE FROM user_sessions WHERE session_token = :session_token");

        return $stmt->execute([':session_token' => $token]);
    }

    public static function validateSession(PDO $db, string $token, int $minutes = 30, bool $touch = true): ?array {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        $query = "SELECT s.id AS session_id,
                         s.user_id,
                         s.session_token,
                         s.last_activity,
                         s.expires_at,
                         u.email,
                         u.role_id,
                         r.role_name,
                         u.status
                  FROM user_sessions s
                  INNER JOIN user u ON s.user_id = u.id
                  INNER JOIN roles r ON u.role_id = r.id
                  WHERE s.session_token = :session_token
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':session_token' => $token]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        $now = new DateTime('now');
        $expiresAt = !empty($session['expires_at']) ? new DateTime($session['expires_at']) : null;
        $lastActivity = !empty($session['last_activity']) ? new DateTime($session['last_activity']) : null;
        $inactivityLimit = $lastActivity ? (clone $lastActivity)->modify('+' . max(1, $minutes) . ' minutes') : null;

        if (($expiresAt && $now > $expiresAt) || ($inactivityLimit && $now > $inactivityLimit)) {
            self::invalidateSession($db, $token);
            return null;
        }

        if ($touch) {
            self::touchSession($db, $token, $minutes);
            $session['last_activity'] = $now->format('Y-m-d H:i:s');
            $session['expires_at'] = (clone $now)->modify('+' . max(1, $minutes) . ' minutes')->format('Y-m-d H:i:s');
        }

        $session['role'] = $session['role_name'] ?? null;

        return $session;
    }

    public static function checkInactivity(PDO $db, string $token, int $minutes = 30): bool {
        return self::validateSession($db, $token, $minutes, true) !== null;
    }
}