<?php
// app/Utils/JWT.php
namespace App\Utils;

use App\Models\Sessao;

class JWT
{
    //private static $secretKey = 'sua_chave_secreta_aqui'; // Substitua por uma chave segura
    private static $secretKey = JWT_SECRET;
    public static function encode($payload, $expiryHours = 24)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Adiciona tempo de expiração
        $payload['exp'] = time() + ($expiryHours * 3600);
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
    public static function createSession($usuarioId, $userData = [], $expiryHours = 24)
    {
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
        
        return false;
    }
    
    // Método para verificar uma sessão
    public static function verifySession($token)
    {
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
    }
}