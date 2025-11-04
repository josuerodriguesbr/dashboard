<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Formulário' ?></title>
    
    <link rel="stylesheet" href="/projetos/dashboard/public/css/main.css">
    <link rel="stylesheet" href="/projetos/dashboard/public/css/form.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= $page_css ?>">
    <?php endif; ?>
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
</head>
<body class="form-body">
    <div class="form-container">
        <header class="form-header">
            <div class="form-logo">
                <div class="form-logo-icon">📊</div>
                <div class="form-logo-text"><?= $title ?></div>
            </div>
        </header>

        <main class="form-main-content">
            <?= $content ?>
        </main>

        <footer class="form-footer">
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>
    </div>

    <?php if (isset($page_js)): ?>
        <script src="<?= $page_js ?>"></script>
    <?php endif; ?>

    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
</body>
</html>