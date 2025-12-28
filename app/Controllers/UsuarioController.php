<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Perfil;
use App\Utils\UserContext;

class UsuarioController
{
    /**
     * Obter vendedores disponíveis para vincular um novo cliente
     */
    public function getVendedoresDisponiveis($request, $response, $args)
    {
        try {
            $perfilAtivo = UserContext::getPerfilAtivo();
            if (!$perfilAtivo) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Usuário não tem perfil ativo'
                ], 401);
            }

            // Obter todos os perfis da mesma árvore hierárquica que são vendedores
            $perfisArvore = Perfil::getPerfisDaArvore($perfilAtivo['id']);
            
            if (empty($perfisArvore)) {
                return $response->withJson([
                    'success' => true,
                    'vendedores' => []
                ], 200);
            }

            // Obter detalhes dos perfis vendedores
            $pdo = \Config\Database::getConnection();
            $perfisIdsStr = implode(',', array_fill(0, count($perfisArvore), '?'));

            $stmt = $pdo->prepare("
                SELECT p.id, u.nome, u.email, pa.nivel as papel_nivel
                FROM integra_perfis p
                JOIN integra_usuarios u ON p.id_usuario = u.id
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.id IN ($perfisIdsStr) AND pa.nivel = 'vendedor'
                ORDER BY u.nome
            ");

            $stmt->execute($perfisArvore);
            $vendedores = $stmt->fetchAll();

            return $response->withJson([
                'success' => true,
                'vendedores' => $vendedores
            ], 200);

        } catch (\Exception $e) {
            error_log("UsuarioController::getVendedoresDisponiveis falhou: " . $e->getMessage());
            return $response->withJson([
                'success' => false,
                'message' => 'Erro ao obter vendedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter o hash de convite de um vendedor específico
     */
    public function getHashVendedor($request, $response, $args)
    {
        try {
            $perfilAtivo = UserContext::getPerfilAtivo();
            if (!$perfilAtivo) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Usuário não tem perfil ativo'
                ], 401);
            }

            $vendedorId = (int)($args['id'] ?? 0);
            
            if ($vendedorId <= 0) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'ID do vendedor inválido'
                ], 400);
            }

            // Verificar se o vendedor pertence à mesma árvore hierárquica
            if (!Perfil::podeAcessarPerfil($perfilAtivo['id'], $vendedorId)) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Acesso negado - vendedor não pertence à sua hierarquia'
                ], 403);
            }

            // Obter o hash de convite do vendedor
            $hash = Perfil::getHashConvite($vendedorId);
            
            if (!$hash) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Vendedor não encontrado'
                ], 404);
            }

            return $response->withJson([
                'success' => true,
                'hash' => $hash
            ], 200);

        } catch (\Exception $e) {
            error_log("UsuarioController::getHashVendedor falhou: " . $e->getMessage());
            return $response->withJson([
                'success' => false,
                'message' => 'Erro ao obter hash do vendedor: ' . $e->getMessage()
            ], 500);
        }
    }
}