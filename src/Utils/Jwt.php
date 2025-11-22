<?php

namespace Utils;

use Exception;

class Jwt
{
    // CHAVE SECRETA DEBUG
    private static string $key = 'minha_senha_super_secreta';

    public static function generate(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // Payload (Adiciona tempo de expiração se não tiver)
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + (60 * 60 * 2); /* 2 horas */
        }
        $payloadJson = json_encode($payload);

        // Encode Base64Url
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$key, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function validate(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception("Formato do token inválido.");
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$key, true);
        $validSignature = self::base64UrlEncode($signature);

        if ($base64Signature !== $validSignature) {
            throw new Exception("Assinatura do token inválida (Token adulterado).");
        }

        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        // Verifica se expirou
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception("Token expirado.");
        }

        return $payload;
    }

    // Helpers para Base64 URL Safe

    private static function base64UrlEncode($data)
    {
        // Troca + por -, / por _ e remove =
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        // Adiciona os = de volta e troca os caracteres
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padLength = 4 - $remainder;
            $data .= str_repeat('=', $padLength);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}