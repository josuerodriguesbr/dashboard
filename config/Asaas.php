<?php
namespace Config;

class Asaas
{
    // Ambiente de Sandbox/Homologação
    const BASE_URL = 'https://api-sandbox.asaas.com/v3';

    public static function getApiKey()
    {
        // Tenta ler do config.json
        $configFile = ROOT . 'config.json';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['asaas']['api_key'])) {
                return $config['asaas']['api_key'];
            }
        }

        // Fallback ou erro
        return getenv('ASAAS_API_KEY');
    }

    public static function getHeaders()
    {
        return [
            'Content-Type: application/json',
            'access_token: ' . self::getApiKey()
        ];
    }
}
