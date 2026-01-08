<?php
namespace Config;

class Asaas
{
    // Ambiente de Sandbox/Homologação
    const BASE_URL = 'https://api-sandbox.asaas.com/v3';
    
    // Chave de API fornecida pelo usuário (sandbox)
    const API_KEY = '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjQ1NmFhNjQ5LWYzNmItNDBlOC05M2FjLTdhN2I2MGM5ZmNhMTo6JGFhY2hfZDQzNmJmMjEtZGM3OS00MWE4LTg0OTMtMWUxNTc4YjIyODI0';

    public static function getHeaders()
    {
        return [
            'Content-Type: application/json',
            'access_token: ' . self::API_KEY
        ];
    }
}
