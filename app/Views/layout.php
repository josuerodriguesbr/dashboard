<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $title ?? 'Dashboard' ?></title>
    
    <?php if (defined('CSS_PADRAO')): ?>
        <link rel="stylesheet" href="<?= CSS_PADRAO ?>" />
    <?php endif; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Ocultar os elementos de status antigos, já que estamos usando o novo sistema de notificações */
        .status {
            display: none !important;
        }
        
        .mensagem-sistema {
            display: none !important;
        }
        
        /* Ajustar posição do container de notificações */
        .feedback-container {
            position: fixed;
            top: 70px !important; /* Garantir que as notificações apareçam abaixo do cabeçalho */
            right: 20px;
            z-index: 9999;
            width: 320px;
            max-width: calc(100vw - 40px);
        }
    </style>

</head>
<body>



    <div class="container-pagina">

        <header>
            <div class="header-logo">
                <?php 
                // Determinar se o botão de voltar deve ser exibido
                $mostrarBotaoVoltar = true;
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                
                // Lista de páginas onde o botão de voltar não deve aparecer
                $paginasSemBotaoVoltar = [
                    '/projetos/dashboard/',
                    '/projetos/dashboard/admin',
                    '/projetos/dashboard/assinante',
                    '/projetos/dashboard/cliente',
                    '/projetos/dashboard/operador',
                    '/projetos/dashboard/vendedor'
                ];
                
                // Verificar se a página atual está na lista de páginas sem botão voltar
                foreach ($paginasSemBotaoVoltar as $pagina) {
                    if (strpos($uri, $pagina) !== false && strlen($uri) <= strlen($pagina) + 1) {
                        $mostrarBotaoVoltar = false;
                        break;
                    }
                }
                
                // Mostrar botão de voltar apenas se necessário
                if ($mostrarBotaoVoltar): ?>
                    <button class="back-btn" type="button" aria-label="Voltar">◀</button>
                <?php endif; ?>

                <a href="#" class="logo-link">

                    <?php if (isset($logo_icone) && $logo_icone): ?>
                        <div class="logo-icon"><?php echo $logo_icone; ?></div>
                    <?php else: ?>
                        <div class="logo-icon">📊</div>
                    <?php endif; ?>

                    <?php if (isset($logo_titulo) && $logo_titulo): ?>
                        <div class="logo-text"><?php echo $logo_titulo; ?></div>
                    <?php else: ?>
                        <div class="logo-text">Dashboard</div>
                    <?php endif; ?>

                </a>
                
            </div>
            
            <div class="header-actions">
                <?php if (isset($usuario) && $usuario): ?>
                    <div class="user-info">
                        <span class="user-name">
                            <?= htmlspecialchars($usuario['nome'] ?? $usuario['name'] ?? 'Usuário') ?>
                        </span>
                        <a href="/projetos/dashboard/perfil" class="profile-link" title="Meu Perfil">
                            👤
                        </a>
                    </div>
                    <button class="logout-btn" id="logoutBtn">
                        Sair
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <!-- Container para mensagens de notificação -->
        <div id="feedback-container" class="feedback-container"></div>

        <section>
            <?= $content ?>
        </section>
        
         <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>         

    </div>

<?php
// Preparar valores do usuário de forma segura para uso no script
// Evita notices quando $usuario pode ser false (boolean) em vez de array
$__usuario_nivel = (is_array($usuario) && isset($usuario['nivel'])) ? $usuario['nivel'] : 'cliente';
$__usuario_nome = (is_array($usuario)) ? ($usuario['nome'] ?? $usuario['name'] ?? 'Usuário') : 'Usuário';
$__rota_dashboard = (is_array($usuario) && isset($usuario['nivel'])) ? getRotaPorUserNivel($usuario['nivel']) : '/projetos/dashboard/';
?>

<script>
    // Função para exibir notificações
    function mostrarNotificacao(mensagem, tipo = 'info', duracao = 5000) {
        // Certificar-se de que o container existe
        const container = document.getElementById('feedback-container');
        if (!container) {
            console.error('Container para notificações não encontrado!');
            return;
        }

        // Criar elemento da notificação
        const notificacao = document.createElement('div');
        notificacao.className = `feedback-mensagem feedback-${tipo}`;
        notificacao.innerHTML = `
            <div class="feedback-conteudo">${mensagem}</div>
            <button class="feedback-fechar" title="Fechar">×</button>
            ${duracao > 0 ? '<div class="feedback-progresso"><div class="feedback-progresso-barra"></div></div>' : ''}
        `;

        // Adicionar evento para fechar a notificação ao clicar nela
        notificacao.addEventListener('click', function(e) {
            if (e.target.classList.contains('feedback-fechar') || e.target.classList.contains('feedback-conteudo') || e.target === notificacao) {
                fecharNotificacao(notificacao);
            }
        });

        // Adicionar a notificação ao container
        container.appendChild(notificacao);

        // Animação de entrada
        setTimeout(() => {
            notificacao.style.transform = 'translateX(0)';
            notificacao.style.opacity = '1';
        }, 10);

        // Fechar automaticamente após o tempo especificado
        if (duracao > 0) {
            setTimeout(() => {
                fecharNotificacao(notificacao);
            }, duracao);
        }

        // Função para fechar a notificação
        function fecharNotificacao(element) {
            element.style.transform = 'translateX(150%)';
            element.style.opacity = '0';
            element.style.maxHeight = '0';
            element.style.marginBottom = '0';
            element.style.overflow = 'hidden';

            setTimeout(() => {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            }, 300);
        }
    }

    // Adicionando a função globalmente para ser acessível em todo o sistema
    window.mostrarNotificacao = mostrarNotificacao;

    // Função para verificar token atualizado (COM DEBUG)
    function verificarTokenAtualizado() {
        console.log('[DEBUG] Executando verificarTokenAtualizado...');
        try {
            const cookies = document.cookie.split(';');
            let authToken = null;
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'authToken') {
                    authToken = value;
                    break;
                }
            }
            console.log('[DEBUG] authToken encontrado:', authToken ? 'Sim' : 'Não');
            
            if (authToken) {
                const tokenParts = authToken.split('.');
                if (tokenParts.length === 3) {
                    try {
                        const payload = JSON.parse(atob(tokenParts[1].replace(/-/g, '+').replace(/_/g, '/')));
                        console.log('[DEBUG] Payload do token:', payload);
                        console.log('[DEBUG] Nome no payload (do token):', payload.nome);
                        console.log('[DEBUG] Nome na window (da página atual):', window.usuario ? window.usuario.nome : 'N/A');

                        if (payload.nome && window.usuario && payload.nome !== window.usuario.nome) {
                            console.log('[DEBUG] Nomes são diferentes! ATUALIZANDO o nome na tela.');
                            const userNameElement = document.querySelector('.user-name');
                            if (userNameElement) {
                                userNameElement.textContent = payload.nome;
                                window.usuario.nome = payload.nome;
                                console.log('[DEBUG] DOM e window.usuario.nome atualizados para:', payload.nome);
                            } else {
                                console.log('[DEBUG] ERRO: Elemento .user-name não encontrado no DOM.');
                            }
                        } else {
                            console.log('[DEBUG] Nomes são iguais ou dados ausentes. Nenhuma atualização é necessária.');
                        }
                    } catch (e) {
                        console.error('[DEBUG] Erro ao decodificar token:', e);
                    }
                }
            }
        } catch (e) {
            console.error('[DEBUG] Erro ao verificar token no cookie:', e);
        }
    }

    // Função de logout
    function handleLogout() {
        if (confirm('Tem certeza que deseja sair?')) {
            fetch('/projetos/dashboard/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                document.cookie = "authToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/projetos/dashboard/;";
                setTimeout(() => {
                    window.location.href = '/projetos/dashboard/';
                }, 100);
            })
            .catch(error => {
                console.error('Erro ao fazer logout:', error);
                document.cookie = "authToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/projetos/dashboard/;";
                window.location.href = '/projetos/dashboard/';
            });
        }
    }

    // Inicializar botão de logout
    function initLogoutButton() {
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            // Remover qualquer evento anterior para evitar duplicatas
            logoutBtn.removeEventListener('click', handleLogout);
            // Adicionar o evento de clique
            logoutBtn.addEventListener('click', handleLogout);
        }
    }

    // Inicializar logo link
    function initLogoLink() {
        const logoLink = document.querySelector('.logo-link');
        if (logoLink) {
            logoLink.addEventListener('click', function(e) {
                e.preventDefault();
                getDashboardRoute();
            });
        }
    }

    // Passar informações do usuário para o JavaScript
    window.usuario = {
        nivel: <?= json_encode($usuario['nivel'] ?? 'cliente') ?>,
        nome: <?= json_encode($usuario['nome'] ?? $usuario['name'] ?? 'Usuário') ?>
    };

    // Função para obter a rota do dashboard via AJAX
    function getDashboardRoute() {
        fetch('/projetos/dashboard/verificar-token', {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.authenticated) {
                window.location.href = <?= json_encode($__rota_dashboard) ?>;
            } else {
                window.location.href = '/projetos/dashboard/';
            }
        })
        .catch(error => {
            console.error('Erro ao obter rota:', error);
            window.location.href = '/projetos/dashboard/';
        });
    }
    
    <?php if ($mostrarBotaoVoltar): ?>
    // Inicializar botão de voltar
    function initBackButton() {
        import('/projetos/dashboard/public/js/funcoes.js')
            .then(module => {
                module.initBackButtons();
            })
            .catch(error => {
                console.error('Erro ao carregar funcoes.js:', error);
            });
    }
    <?php endif; ?>
    
    // Inicializar todos os componentes quando o DOM estiver pronto
    function initializeComponents() {
        initLogoutButton();
        initLogoLink();
        // A verificação do token será feita pelo evento 'pageshow' para garantir a atualização constante.
        // verificarTokenAtualizado(); 
        <?php if ($mostrarBotaoVoltar): ?>
        initBackButton();
        <?php endif; ?>
    }

    // Adiciona um listener para o evento 'pageshow'. (COM DEBUG)
    window.addEventListener('pageshow', function(event) {
        console.log('[DEBUG] Evento "pageshow" disparado. A página veio do cache (bfcache)? ' + event.persisted);
        verificarTokenAtualizado();
    });
    
    // Manter a inicialização dos componentes que não precisam ser recarregados
    if (document.readyState === 'loading') {
        // DOM ainda está carregando, aguardar
        document.addEventListener('DOMContentLoaded', initializeComponents);
    } else {
        // DOM já está pronto, inicializar imediatamente
        initializeComponents();
    }
      
</script>

    <!-- back.js removido; use `goBack` de public/js/funcoes.js quando este módulo for importado nas páginas -->

    <?php if (isset($page_js_module)): ?>
        <script type="module" src="<?= $page_js_module ?>"></script>
    <?php endif; ?>

</body>
</html>