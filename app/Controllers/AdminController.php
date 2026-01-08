<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\CreditoTransacao;
use App\Utils\UserContext;

class AdminController
{
    public function dashboard()
    {
        // Verifica autenticação
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/login');
            return;
        }

        // Verifica permissão admin
        $nivel = UserContext::getNivelAtivo();
        if ($nivel !== 'admin') {
            // Se não for admin, redireciona para o dashboard correto do nível dele
            $rotaCorreta = getRotaPorUserNivel($nivel);
            redirect(str_replace('/projetos/dashboard', '', $rotaCorreta)); // redirect já adiciona base
            return;
        }

        view('admin/dashboard');
    }

    /**
     * Lista usuários para gestão de créditos
     */
    public function gerenciarCreditos()
    {
        // Verifica autenticação primeiro
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/login');
            return;
        }

        // Verifica se é admin
        $nivel = UserContext::getNivelAtivo();
        if ($nivel !== 'admin') {
            redirect('/'); // ou erro 403
            return;
        }

        $usuarios = Usuario::listar(100);
        
        // Adiciona o saldo atual a cada usuário da lista
        foreach ($usuarios as &$user) {
            $user['saldo_atual'] = Usuario::getSaldo($user['id']);
        }

        view('admin/gerenciar_creditos', [
            'usuarios' => $usuarios,
            'active_tab' => 'admin_creditos'
        ]);
    }

    /**
     * Adiciona (ou remove) créditos manualmente
     */
    public function adicionarCreditos()
    {
        // Verifica autenticação primeiro
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => 'Não autenticado'], 401);
            return;
        }

        $nivel = UserContext::getNivelAtivo();
        if ($nivel !== 'admin') {
            json_response(['success' => false, 'message' => 'Acesso negado'], 403);
            return;
        }

        $usuarioId = $_POST['usuario_id'] ?? null;
        $valor = $_POST['valor'] ?? null; // Valor absoluto
        $tipoOperacao = $_POST['tipo_operacao'] ?? 'credito'; // credito ou debito
        $descricao = $_POST['descricao'] ?? '';

        if (!$usuarioId || !$valor || $valor <= 0) {
            json_response(['success' => false, 'message' => 'Dados inválidos'], 400);
            return;
        }

        // Determinar o Tipo de Transação
        // 3 = Ajuste Manual (Crédito) [+1]
        // 4 = Ajuste Manual (Débito) [-1]
        $tipoId = ($tipoOperacao === 'debito') ? 4 : 3;

        try {
            Usuario::adicionarTransacao(
                $usuarioId,
                $tipoId,
                $valor, 
                "Ajuste Manual Admin: " . $descricao
            );

            json_response(['success' => true, 'message' => 'Saldo atualizado com sucesso!']);
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => 'Erro ao atualizar saldo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exibe o histórico de transações de um usuário específico
     */
    public function verHistorico($id)
    {
        // Verifica autenticação
        try {
            \App\Middleware\AuthMiddleware::verificar();
        } catch (\Exception $e) {
            redirect('/login');
            return;
        }

        // Verifica permissão admin
        $nivel = UserContext::getNivelAtivo();
        if ($nivel !== 'admin') {
            redirect('/');
            return;
        }

        $usuario = Usuario::buscarPorId($id);
        if (!$usuario) {
            redirect('/admin/gerenciar-creditos');
            return;
        }

        $transacoes = CreditoTransacao::getHistorico($id, 100);

        view('admin/historico_creditos', [
            'usuario' => $usuario,
            'transacoes' => $transacoes,
            'active_tab' => 'admin_creditos'
        ]);
    }
}