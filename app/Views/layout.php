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

</head>
<body>



    <div class="container-pagina">

        <header>
            <div class="header-logo">
                <a href="#" class="logo-link">
                    <div class="logo-icon">📊</div>
                    <div class="logo-text">Dashboard</div>
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

        <section>
            <?= $content ?>
        </section>

         <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>

    </div>

<script>

    // Alternar tema claro/escuro (exemplo simples)
    document.documentElement.classList.toggle('tema-claro');

    // Função de logout
    document.getElementById('logoutBtn')?.addEventListener('click', function() {
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
                if (data.success) {
                    // Limpar cookie manualmente também (backup)
                    document.cookie = "authToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/projetos/dashboard/;";
                    // Adicionar um pequeno delay para garantir que o cookie foi limpo
                    setTimeout(() => {
                        window.location.href = '/projetos/dashboard/';
                    }, 100);
                }
            })
            .catch(error => {
                console.error('Erro ao fazer logout:', error);
                // Mesmo com erro, limpar cookie e redirecionar para login
                document.cookie = "authToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/projetos/dashboard/;";
                window.location.href = '/projetos/dashboard/';
            });
        }
    });

    // Adicionar evento de clique ao logo para redirecionar ao dashboard
    document.querySelector('.logo-link')?.addEventListener('click', function(e) {
        e.preventDefault();
        
        getDashboardRoute(); // Obter a rota correta do servidor
    });

    // Passar informações do usuário para o JavaScript
    window.usuario = {
        nivel: '<?php echo $usuario['nivel'] ?? 'cliente'; ?>',
        nome: '<?php echo htmlspecialchars($usuario['nome'] ?? $usuario['name'] ?? 'Usuário'); ?>'
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
                // Redirecionar para a rota correta
                window.location.href = '<?php echo isset($usuario) ? getRotaPorUserNivel($usuario['nivel']) : '/projetos/dashboard/'; ?>';
            } else {
                window.location.href = '/projetos/dashboard/';
            }
        })
        .catch(error => {
            console.error('Erro ao obter rota:', error);
            // Fallback para a rota padrão
            window.location.href = '/projetos/dashboard/';
        });
    }
      
</script>

    <?php if (isset($page_js_module)): ?>
        <script type="module" src="<?= $page_js_module ?>"></script>
    <?php endif; ?>

</body>
</html>