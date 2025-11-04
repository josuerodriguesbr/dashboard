<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $title ?? 'Painel Administrativo' ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/projetos/dashboard/public/css/main.css" />
    <link rel="stylesheet" href="/projetos/dashboard/public/css/admin.css" />
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= $page_css ?>" />
    <?php endif; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
    
</head>
<body class="admin-body">
    <div class="container">
        <header class="dashboard-header admin-header">
            <div class="header-logo">
                <a href="/projetos/dashboard/admin" class="logo-link">
                    <div class="logo-icon">⚙️</div>
                    <div class="logo-text"><?= $title ?? 'Admin' ?></div>
                </a>
            </div>
            
            <div class="header-actions">
                <?php if (isset($usuario) && $usuario): ?>
                    <div class="user-info">
                        <span class="user-name">
                            <?= htmlspecialchars($usuario['nome'] ?? $usuario['name'] ?? 'Administrador') ?>
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

        <div class="content-wrapper">
            <?php if (isset($usuario) && $usuario): ?>
                <aside class="sidebar-menu admin-menu" id="sidebarMenu">
                    <h3 class="menu-title">Administração</h3>
                    <ul class="menu-list">
                        <?php renderAdminMenuItems(); ?>
                    </ul>
                </aside>
                
                <button class="menu-toggle" id="menuToggle">☰ Menu</button>
            <?php endif; ?>
            
            <main class="main-content">
                <?= $content ?>
            </main>
        </div>

        <footer style="text-align: center; padding: 20px; color: #777; margin-top: 30px;">
            &copy; <?= date('Y') ?> - Sistema de Integração - Área Administrativa
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
                        document.cookie = "authToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/projetos/dashboard/;";
                        setTimeout(() => {
                            window.location.href = '/projetos/dashboard/';
                        }, 100);
                    }
                })
                .catch(error => {
                    console.error('Erro ao fazer logout:', error);
                    document.cookie = "authToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/projetos/dashboard/;";
                    window.location.href = '/projetos/dashboard/';
                });
            }
        });

        // Toggle do menu mobile
        document.getElementById('menuToggle')?.addEventListener('click', function() {
            const menu = document.getElementById('sidebarMenu');
            menu.classList.toggle('active');
        });

        // Fechar menu mobile ao clicar em um item
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function() {
                const menu = document.getElementById('sidebarMenu');
                menu.classList.remove('active');
            });
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