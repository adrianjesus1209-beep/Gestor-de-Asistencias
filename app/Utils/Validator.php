<?php

namespace App\Utils;

use DateTime;
use DateTimeInterface;

class Validator {
    public static function sanitizarInput($value, array $allowedTags = []): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (!empty($allowedTags)) {
            $tags = '<' . implode('><', array_map('strtolower', $allowedTags)) . '>';
            return strip_tags($value, $tags);
        }

        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }

    public static function normalizarCedula($idNumber): string {
        $idNumber = strtoupper(trim((string) $idNumber));
        $idNumber = preg_replace('/\s+/', '', $idNumber);

        if (strpos($idNumber, 'V-') === 0) {
            return 'V-' . substr($idNumber, 2);
        }

        if (strpos($idNumber, 'V') === 0) {
            return 'V-' . substr($idNumber, 1);
        }

        return $idNumber;
    }

    public static function cedula($idNumber): bool {
        $normalized = self::normalizarCedula($idNumber);
        $comparison = strtoupper(str_replace('-', '', $normalized));

        return (bool) preg_match('/^V?[0-9]{6,8}$/', $comparison);
    }

    public static function email($email): bool {
        $email = trim((string) $email);

        if ($email === '' || strlen($email) > 100) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function password($password): array {
        $password = (string) $password;
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos un número.';
        }

        $hasSpecialCharacter = (bool) preg_match('/[!@#$%^&*]/', $password);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'hash' => empty($errors) ? password_hash($password, PASSWORD_DEFAULT) : null,
            'has_special_character' => $hasSpecialCharacter,
        ];
    }

    public static function nombre($value): bool {
        $value = trim((string) $value);

        return $value !== '' && (bool) preg_match('/^[a-zA-ZáéíóúñÑ\s]{2,50}$/u', $value);
    }

    public static function codigo($value): bool {
        return (bool) preg_match('/^[A-Z]{2,6}-[0-9]{3}$/', trim((string) $value));
    }

    public static function qrToken($value): bool {
        return (bool) preg_match('/^[a-f0-9]{64}$/', trim((string) $value));
    }

    public static function fecha($value): bool {
        $value = trim((string) $value);
        $date = DateTime::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    public static function hora($value): bool {
        $value = trim((string) $value);
        $time = DateTime::createFromFormat('H:i:s', $value);

        return $time !== false && $time->format('H:i:s') === $value;
    }

    public static function rangoHoras($startTime, $endTime): bool {
        if (!self::hora($startTime) || !self::hora($endTime)) {
            return false;
        }

        return strtotime($startTime) < strtotime($endTime);
    }

    public static function horarioClase($dayOfWeek, $startTime, $endTime, int $toleranceMinutes = 10, ?DateTimeInterface $now = null): string {
        if (!self::hora($startTime) || !self::hora($endTime)) {
            return 'Absent';
        }

        $now = $now ? DateTime::createFromInterface($now) : new DateTime('now');
        $currentDay = strtolower($now->format('l'));
        $expectedDay = strtolower(trim((string) $dayOfWeek));

        if ($expectedDay !== '' && $currentDay !== $expectedDay) {
            return 'Absent';
        }

        $today = $now->format('Y-m-d');
        $start = new DateTime($today . ' ' . $startTime);
        $end = new DateTime($today . ' ' . $endTime);
        $lateThreshold = (clone $start)->modify('+' . max(0, $toleranceMinutes) . ' minutes');

        if ($now > $end) {
            return 'Absent';
        }

        if ($now >= $lateThreshold) {
            return 'Late';
        }

        if ($now >= $start && $now <= $end) {
            return 'Present';
        }

        return 'Absent';
    }

    public static function sanitizeRichText($value, array $allowedTags = ['p', 'br', 'strong', 'em', 'b', 'i', 'u', 'ul', 'ol', 'li']): string {
        $value = trim((string) $value);
        $tags = '<' . implode('><', array_map('strtolower', $allowedTags)) . '>';

        return strip_tags($value, $tags);
    }
}