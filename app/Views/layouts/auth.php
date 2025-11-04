<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Autenticação' ?></title>
    
    <link rel="stylesheet" href="/projetos/dashboard/public/css/auth.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= $page_css ?>">
    <?php endif; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-header">
            <h1><?= $title ?? 'Sistema de Integração' ?></h1>
        </div>
        
        <div class="auth-content">
            <?= $content ?>
        </div>
        
        <div class="auth-footer">
            &copy; <?= date('Y') ?> - Sistema de Integração
        </div>
    </div>
    
    <?php if (isset($page_js)): ?>
        <script src="<?= $page_js ?>"></script>
    <?php endif; ?>
    
    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
</body>
</html>