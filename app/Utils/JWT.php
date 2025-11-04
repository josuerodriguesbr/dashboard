<?php
// app/Utils/JWT.php
namespace App\Utils;

use App\Models\Sessao;

class JWT
{
    private static $secretKey = null;
    
    private static function getSecretKey()
    {
        if (self::$secretKey === null) {
            // Verifica se a constante JWT_SECRET está definida
            if (defined('JWT_SECRET') && JWT_SECRET) {
                self::$secretKey = JWT_SECRET;
            } else {
                // Fallback para uma chave padrão (não recomendado para produção)
                self::$secretKey = 'chave_secreta_padrao_fallback_123456';
                error_log("WARNING: JWT_SECRET não está definido. Usando chave padrão.");
            }
        }
        return self::$secretKey;
    }

    public static function encode($payload, $expiryHours = 24)
    {
        try {
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            
            // Adiciona tempo de expiração
            $payload['exp'] = time() + ($expiryHours * 3600);
            $payload['iat'] = time();
            
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
            
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        } catch (\Exception $e) {
            error_log("Erro ao codificar JWT: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function decode($jwt)
    {
        try {
            $tokenParts = explode('.', $jwt);
            
            if (count($tokenParts) != 3) {
                throw new \Exception('Token inválido');
            }
            
            list($header, $payload, $signature) = $tokenParts;
            
            $signatureVerification = hash_hmac('sha256', $header . "." . $payload, self::getSecretKey(), true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureVerification));
            
            if ($base64UrlSignature !== $signature) {
                throw new \Exception('Assinatura inválida');
            }
            
            $decodedPayload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
            
            // Verifica expiração
            if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) {
                throw new \Exception('Token expirado');
            }
            
            return $decodedPayload;
        } catch (\Exception $e) {
            error_log("Erro ao decodificar JWT: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Método para criar uma sessão com JWT
    public static function createSession($usuarioId, $userData = [], $expiryHours = 24)
    {
        try {
            $payload = array_merge([
                'usuarioId' => $usuarioId
            ], $userData);
            
            $token = self::encode($payload, $expiryHours);
            
            // Salvar a sessão no banco de dados
            $sessaoId = Sessao::criar($usuarioId, $token, $expiryHours);
            
            if ($sessaoId) {
                return [
                    'token' => $token,
                    'sessaoId' => $sessaoId
                ];
            }
            
            error_log("Falha ao criar sessão no banco de dados para usuário ID: " . $usuarioId);
            return false;
        } catch (\Exception $e) {
            error_log("Erro ao criar sessão JWT: " . $e->getMessage());
            return false;
        }
    }
    
    // Método para verificar uma sessão
    public static function verifySession($token)
    {
        try {
            // Primeiro verificar se a sessão existe no banco
            $sessao = Sessao::buscarPorToken($token);
            
            if (!$sessao) {
                throw new \Exception('Sessão inválida ou expirada');
            }
            
            // Depois verificar o JWT
            $payload = self::decode($token);
            
            return [
                'sessao' => $sessao,
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            error_log("Erro ao verificar sessão: " . $e->getMessage());
            throw $e;
        }
    }
}