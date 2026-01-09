<?php
namespace App\Controllers;

use App\Models\CreditoTransacao;
use App\Models\Perfil;
use App\Utils\UserContext;

class CreditoController
{
    /**
     * Renderizar a página de gerenciamento de créditos
     */
    public function mostrarGerenciarCreditos($request = null, $response = null, $args = null)
    {
        $data = [
            'title' => 'Gerenciar Créditos'
        ];
        
        view('recursos/creditos/gerenciar-creditos', $data);
        
        return $response;
    }

    /**
     * Transferir créditos entre perfis
     */
    public function transferirCreditos($request = null, $response = null, $args = null)
    {
        // Limpar qualquer conteúdo que possa ter sido enviado anteriormente
        if (ob_get_level()) {
            ob_clean();
        }
        
        try {
            // Verificar autenticação via AuthMiddleware
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Buscar o perfil ativo do usuário autenticado
            $perfilAtivo = \App\Models\Usuario::getPerfilAtivo($usuario['id']);
            
            if (!$perfilAtivo) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ], 401);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ]);
                    exit;
                }
            }

            $dados = json_decode($request ? $request->getBody() : file_get_contents('php://input'), true);

            $destinoPerfilId = (int)($dados['destino_perfil_id'] ?? 0);
            $valor = (float)($dados['valor'] ?? 0);
            $descricao = $dados['descricao'] ?? '';

            if ($destinoPerfilId <= 0) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'ID do perfil de destino inválido'
                    ], 400);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'ID do perfil de destino inválido'
                    ]);
                    exit;
                }
            }

            if ($valor <= 0) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'Valor da transferência inválido'
                    ], 400);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Valor da transferência inválido'
                    ]);
                    exit;
                }
            }

            // Verificar se o perfil pode acessar o perfil de destino
            if (!Perfil::podeAcessarPerfil($perfilAtivo['id'], $destinoPerfilId)) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'Acesso negado - você não pode transferir créditos para este perfil'
                    ], 403);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Acesso negado - você não pode transferir créditos para este perfil'
                    ]);
                    exit;
                }
            }

            $resultado = CreditoTransacao::transferirCreditos(
                $perfilAtivo['id'],
                $destinoPerfilId,
                $valor,
                $descricao
            );

            if ($response !== null) {
                return $response->withJson($resultado, 200);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
                echo json_encode($resultado);
                exit;
            }

        } catch (\Exception $e) {
            error_log("CreditoController::transferirCreditos falhou: " . $e->getMessage());
            if ($response !== null) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Erro ao realizar transferência: ' . $e->getMessage()
                ], 500);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao realizar transferência: ' . $e->getMessage()
                ]);
                exit;
            }
        }
    }

    /**
     * Obter saldo do perfil ativo
     */
    public function getSaldo($request = null, $response = null, $args = null)
    {
        // Limpar qualquer conteúdo que possa ter sido enviado anteriormente
        if (ob_get_level()) {
            ob_clean();
        }
        
        try {
            // Primeiro, verificar autenticação via AuthMiddleware
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Buscar o perfil ativo do usuário autenticado
            $perfilAtivo = \App\Models\Usuario::getPerfilAtivo($usuario['id']);
            
            if (!$perfilAtivo) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ], 401);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ]);
                    exit;
                }
            }

            // Buscar saldo direto da tabela perfis (já vem no perfil ativo)
            $saldo = $perfilAtivo['creditos'] ?? 0.00;

            if ($response !== null) {
                return $response->withJson([
                    'success' => true,
                    'saldo' => $saldo
                ], 200);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'saldo' => $saldo
                ]);
                exit;
            }

        } catch (\Exception $e) {
            error_log("CreditoController::getSaldo falhou: " . $e->getMessage());
            if ($response !== null) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Erro ao obter saldo: ' . $e->getMessage()
                ], 500);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao obter saldo: ' . $e->getMessage()
                ]);
                exit;
            }
        }
    }

    /**
     * Obter histórico de transações do perfil ativo
     */
    public function getHistoricoTransacoes($request = null, $response = null, $args = null)
    {
        // Limpar qualquer conteúdo que possa ter sido enviado anteriormente
        if (ob_get_level()) {
            ob_clean();
        }
        
        try {
            // Verificar autenticação via AuthMiddleware
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Buscar o perfil ativo do usuário autenticado
            $perfilAtivo = \App\Models\Usuario::getPerfilAtivo($usuario['id']);
            
            if (!$perfilAtivo) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ], 401);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ]);
                    exit;
                }
            }

            $limite = (int)($request && method_exists($request, 'getQueryParam') ? $request->getQueryParam('limite', 50) : ($_GET['limite'] ?? 50));
            if ($limite > 100) {
                $limite = 100; // Limite máximo
            }

            $transacoes = CreditoTransacao::getHistoricoTransacoes($perfilAtivo['id'], $limite);

            if ($response !== null) {
                return $response->withJson([
                    'success' => true,
                    'transacoes' => $transacoes
                ], 200);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'transacoes' => $transacoes
                ]);
                exit;
            }

        } catch (\Exception $e) {
            error_log("CreditoController::getHistoricoTransacoes falhou: " . $e->getMessage());
            if ($response !== null) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Erro ao obter histórico de transações: ' . $e->getMessage()
                ], 500);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao obter histórico de transações: ' . $e->getMessage()
                ]);
                exit;
            }
        }
    }

    /**
     * Listar perfis disponíveis para transferência
     */
    public function getPerfisParaTransferencia($request = null, $response = null, $args = null)
    {
        // Limpar qualquer conteúdo que possa ter sido enviado anteriormente
        if (ob_get_level()) {
            ob_clean();
        }
        
        try {
            // Verificar autenticação via AuthMiddleware
            $usuario = \App\Middleware\AuthMiddleware::verificar();
            
            // Buscar o perfil ativo do usuário autenticado
            $perfilAtivo = \App\Models\Usuario::getPerfilAtivo($usuario['id']);
            
            if (!$perfilAtivo) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ], 401);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Usuário não tem perfil ativo'
                    ]);
                    exit;
                }
            }

            // Obter todos os perfis da mesma árvore hierárquica, exceto o perfil ativo
            $perfisArvore = Perfil::getPerfisDaArvore($perfilAtivo['id']);
            
            // Filtrar para remover o próprio perfil
            $perfisArvore = array_filter($perfisArvore, function($id) use ($perfilAtivo) {
                return $id != $perfilAtivo['id'];
            });

            if (empty($perfisArvore)) {
                if ($response !== null) {
                    return $response->withJson([
                        'success' => true,
                        'perfis' => []
                    ], 200);
                } else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'perfis' => []
                    ]);
                    exit;
                }
            }

            // Obter detalhes dos perfis
            $pdo = \Config\Database::getConnection();
            $perfisIdsStr = implode(',', array_fill(0, count($perfisArvore), '?'));

            $stmt = $pdo->prepare("
                SELECT p.id, p.creditos, u.nome, u.email, pa.nivel as papel_nivel
                FROM perfis p
                JOIN usuarios u ON p.id_usuario = u.id
                JOIN papeis pa ON p.id_papel = pa.id
                WHERE p.id IN ($perfisIdsStr)
                ORDER BY pa.nivel, u.nome
            ");

            $stmt->execute($perfisArvore);
            $perfis = $stmt->fetchAll();

            if ($response !== null) {
                return $response->withJson([
                    'success' => true,
                    'perfis' => $perfis
                ], 200);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'perfis' => $perfis
                ]);
                exit;
            }

        } catch (\Exception $e) {
            error_log("CreditoController::getPerfisParaTransferencia falhou: " . $e->getMessage());
            if ($response !== null) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'Erro ao obter perfis para transferência: ' . $e->getMessage()
                ], 500);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao obter perfis para transferência: ' . $e->getMessage()
                ]);
                exit;
            }
        }
    }
    
    /**
     * Função auxiliar para obter ou criar um perfil ativo
     */
    private function obterOuCriarPerfilAtivo()
    {
        try {
            $usuario = \App\Utils\UserContext::getUsuario();
            if (!$usuario || !isset($usuario['id'])) {
                return false;
            }
            
            // Tenta buscar o perfil ativo no banco de dados
            $perfilAtivo = \App\Models\Usuario::getPerfilAtivo($usuario['id']);
            if ($perfilAtivo) {
                // Atualizar o contexto do usuário
                \App\Utils\UserContext::setPerfilAtivo([
                    'id' => $perfilAtivo['id'],
                    'nivel' => $perfilAtivo['papel_nivel'],
                    'status' => $perfilAtivo['status'],
                    'creditos' => $perfilAtivo['creditos'],
                    'hashConvite' => $perfilAtivo['hashConvite'],
                    'hashAnfitriao' => $perfilAtivo['hashAnfitriao']
                ]);
                
                return [
                    'id' => $perfilAtivo['id'],
                    'nivel' => $perfilAtivo['papel_nivel'],
                    'status' => $perfilAtivo['status'],
                    'creditos' => $perfilAtivo['creditos'],
                    'hashConvite' => $perfilAtivo['hashConvite'],
                    'hashAnfitriao' => $perfilAtivo['hashAnfitriao']
                ];
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("CreditoController::obterOuCriarPerfilAtivo falhou: " . $e->getMessage());
            return false;
        }
    }
}