<?php
// app/Utils/JWT.php
namespace App\Utils;

use App\Models\Sessao;

class JWT
{
    //private static $secretKey = 'sua_chave_secreta_aqui'; // Substitua por uma chave segura
    private static $secretKey = JWT_SECRET;
    public static function encode($payload, $expiryHours = null)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Usa o valor padrão de JWT_EXPIRE ou converte horas para segundos
        $expiry = $expiryHours ? $expiryHours * 3600 : (defined('JWT_EXPIRE') ? JWT_EXPIRE : 3600 * 24); // 24 horas por padrão
        
        // Adiciona tempo de expiração
        $payload['exp'] = time() + $expiry;
        $payload['iat'] = time();
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($jwt)
    {
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) != 3) {
            throw new \Exception('Token inválido');
        }
        
        list($header, $payload, $signature) = $tokenParts;
        
        $signatureVerification = hash_hmac('sha256', $header . "." . $payload, self::$secretKey, true);
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
    }
    
    // Método para criar uma sessão com JWT
    public static function createSession($usuarioId, $userData = [], $expiryHours = null)
    {
        try {
            $payload = array_merge([
                'usuarioId' => $usuarioId
            ], $userData);
            
            $token = self::encode($payload, $expiryHours);
            
            // Salvar a sessão no banco de dados
            $sessaoId = Sessao::criar($usuarioId, $token, $expiryHours ? $expiryHours : 24); // Padrão para 24 horas
            
            if ($sessaoId) {
                return [
                    'token' => $token,
                    'sessaoId' => $sessaoId
                ];
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("JWT::createSession falhou: " . $e->getMessage());
            error_log("JWT::createSession trace: " . $e->getTraceAsString());
            throw $e; // Relançar a exceção para que seja capturada no método login
        }
    }
    
    // Método para verificar uma sessão
    public static function verifySession($token)
    {
        try {
            error_log("Iniciando verificação da sessão com token: " . substr($token, 0, 20) . "...");
            
            // Primeiro verificar se a sessão existe no banco
            $sessao = Sessao::buscarPorToken($token);
            error_log("Resultado da busca por token: " . print_r($sessao, true));
            
            if (!$sessao) {
                throw new \Exception('Sessão inválida ou expirada');
            }
            
            // Depois verificar o JWT
            $payload = self::decode($token);
            error_log("Payload decodificado: " . print_r($payload, true));
            
            return [
                'sessao' => $sessao,
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            error_log("JWT::verifySession falhou: " . $e->getMessage());
            error_log("JWT::verifySession trace: " . $e->getTraceAsString());
            throw $e; // Relançar a exceção
        }
    }
}