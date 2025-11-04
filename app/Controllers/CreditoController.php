<?php
namespace App\Controller;

use App\Model\Credito;
use App\Model\Usuario;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CreditoController
{
    private function getUsuarioLogado(): ?array
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        
        try {
            $decoded = JWT::decode($matches[1], new Key($_ENV['JWT_SECRET'] ?? 'seu_segredo_aqui', 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function adicionar()
    {
        header('Content-Type: application/json');
        $usuario = $this->getUsuarioLogado();
        if (!$usuario || $usuario['nivel'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $usuarioId = (int)($data['usuario_id'] ?? 0);
        $valor = (float)($data['valor'] ?? 0);
        $descricao = $data['descricao'] ?? 'Crédito adicionado';
        $ref = $data['referencia_externa'] ?? null;

        if ($usuarioId <= 0 || $valor <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        if (Credito::adicionarCredito($usuarioId, $valor, $descricao, $ref)) {
            echo json_encode(['success' => true, 'message' => 'Crédito adicionado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Falha ao adicionar crédito']);
        }
    }

    public function transferir()
    {
        header('Content-Type: application/json');
        $usuario = $this->getUsuarioLogado();
        if (!$usuario || !in_array($usuario['nivel'], ['assinante', 'admin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $de = (int)($usuario['id']);
        $para = (int)($data['para'] ?? 0);
        $valor = (float)($data['valor'] ?? 0);
        $descricao = $data['descricao'] ?? 'Transferência';

        // Se for assinante, só pode transferir para vendedores dele (opcional: implementar relacionamento)
        // Por enquanto, permitimos qualquer transferência entre assinante/vendedor

        if ($para <= 0 || $valor <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        // Validação adicional: garantir que 'para' é vendedor (opcional)
        $destino = Usuario::findById($para);
        if (!$destino || $destino['nivel'] !== 'vendedor') {
            http_response_code(400);
            echo json_encode(['error' => 'Destinatário inválido']);
            return;
        }

        if (Credito::transferirCredito($de, $para, $valor, $descricao)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Saldo insuficiente ou erro na transferência']);
        }
    }

    public function saldo()
    {
        header('Content-Type: application/json');
        $usuario = $this->getUsuarioLogado();
        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado']);
            return;
        }

        $saldo = Credito::getSaldo((int)$usuario['id']);
        echo json_encode(['saldo' => number_format($saldo, 2, '.', '')]);
    }

    public function extrato()
    {
        header('Content-Type: application/json');
        $usuario = $this->getUsuarioLogado();
        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado']);
            return;
        }

        $extrato = Credito::getExtrato((int)$usuario['id']);
        echo json_encode($extrato);
    }
}