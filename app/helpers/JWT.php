<?php
namespace App\Helpers;

class JWT
{
    private static function base64UrlEncode(string $data): string
    {
        $b64 = base64_encode($data);
        return rtrim(strtr($b64, '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data)
    {
        $b64 = strtr($data, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($b64);
    }

    public static function encode(array $payload, string $secret, int $expiry = 3600, string $alg = 'HS256'): string
    {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $now = time();
        $payload['iat'] = $now;
        if ($expiry > 0) {
            $payload['exp'] = $now + $expiry;
        }

        $b64header = self::base64UrlEncode(json_encode($header));
        $b64payload = self::base64UrlEncode(json_encode($payload));
        $signing_input = $b64header . '.' . $b64payload;
        $signature = self::sign($signing_input, $secret, $alg);
        $b64sig = self::base64UrlEncode($signature);

        return $b64header . '.' . $b64payload . '.' . $b64sig;
    }

    private static function sign(string $input, string $secret, string $alg): string
    {
        if ($alg === 'HS256') {
            return hash_hmac('sha256', $input, $secret, true);
        }

        throw new \Exception('Algoritmo no soportado: ' . $alg);
    }

    public static function decode(string $token, string $secret, array $allowed_algs = ['HS256']): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Token inválido');
        }

        list($b64header, $b64payload, $b64sig) = $parts;

        $headerJson = self::base64UrlDecode($b64header);
        $payloadJson = self::base64UrlDecode($b64payload);
        $sig = self::base64UrlDecode($b64sig);

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            throw new \Exception('Header o payload corrupto');
        }

        if (empty($header['alg']) || !in_array($header['alg'], $allowed_algs, true)) {
            throw new \Exception('Algoritmo no permitido');
        }

        $signing_input = $b64header . '.' . $b64payload;
        $expected = self::sign($signing_input, $secret, $header['alg']);

        if (!hash_equals($expected, $sig)) {
            throw new \Exception('Firma inválida');
        }

        if (isset($payload['exp']) && time() >= $payload['exp']) {
            throw new \Exception('Token expirado');
        }

        return $payload;
    }
}