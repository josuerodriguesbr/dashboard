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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-name {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .profile-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #3498db;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .profile-link:hover {
            background: #2980b9;
        }

.logo-link {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}

.logo-link:hover {
    color: #3498db;
}        
    </style>
</head>
<body>
    <div class="container">

<header class="dashboard-header">
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

    // Adicionar evento de clique ao logo para redirecionar ao dashboard
    document.querySelector('.logo-link')?.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = determinarDashboardUrl();
    });

    // Passar informações do usuário para o JavaScript
    window.usuario = {
        nivel: '<?= $usuario['nivel'] ?? 'cliente' ?>',
        nome: '<?= htmlspecialchars($usuario['nome'] ?? $usuario['name'] ?? 'Usuário') ?>'
    };
    
    // Função para determinar a URL do dashboard com base no nível do usuário
    function determinarDashboardUrl() {
        const basePath = '/projetos/dashboard';
        const rotas = {
            'admin': basePath + '/admin',
            'assinante': basePath + '/assinante',
            'vendedor': basePath + '/vendedor',
            'cliente': basePath + '/cliente'
        };
        
        return rotas[window.usuario.nivel] || rotas['cliente'];
    }    
</script>

    <?php if (isset($page_js)): ?>
        <script src="<?= $page_js ?>"></script>
    <?php endif; ?>

    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
</body>
</html>