<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $title ?? 'Dashboard' ?></title>
    
    <link rel="stylesheet" href="/projetos/dashboard/public/css/style.css" />
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= $page_css ?>" />
    <?php endif; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
    
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            font-size: 24px;
            color: #3498db;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 500;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .user-info {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <div class="header-logo">
                <div class="logo-icon">📊</div>
                <div class="logo-text">Dashboard</div>
            </div>
            
            <div class="header-actions">
                <?php if (isset($usuario) && $usuario): ?>
                    <div class="user-info">
                        <?= htmlspecialchars($usuario['nome'] ?? $usuario['name'] ?? 'Usuário') ?>
                    </div>
                    <button class="logout-btn" id="logoutBtn">
                        Sair
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <?= $content ?>

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
</script>

    <?php if (isset($page_js)): ?>
        <script src="<?= $page_js ?>"></script>
    <?php endif; ?>

    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
</body>
</html>