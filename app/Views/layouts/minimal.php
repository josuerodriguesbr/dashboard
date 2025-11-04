<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema' ?></title>
    
    <link rel="stylesheet" href="/projetos/dashboard/public/css/main.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= $page_css ?>">
    <?php endif; ?>
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
</head>
<body class="minimal-body">
    <div class="container">
        <header class="dashboard-header">
            <div class="header-logo">
                <a href="#" class="logo-link" id="dashboardLink">
                    <div class="logo-icon">📊</div>
                    <div class="logo-text"><?= $title ?></div>
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

        <main class="main-content">
            <?= $content ?>
        </main>

        <footer style="text-align: center; padding: 20px; color: #777; margin-top: 30px;">
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>
    </div>

    <script>
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
        document.getElementById('dashboardLink')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/projetos/dashboard/';
        });
    </script>

    <?php if (isset($page_js)): ?>
        <script src="<?= $page_js ?>"></script>
    <?php endif; ?>

    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
</body>
</html>