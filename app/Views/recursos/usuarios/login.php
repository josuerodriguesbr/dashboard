<!DOCTYPE html>
<html>
<head>

<!-- Manifest -->
<link rel="manifest" href="public/manifest.json">

<!-- Tema do Android -->
<meta name="theme-color" content="#0066cc">

<!-- Para iOS (opcional, mas recomendado) -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="apple-touch-icon" href="public/icone-192.png">

    <title>Login - Sistema</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (defined('CSS_PATH') && !empty(CSS_PATH)): ?>
        <link rel="stylesheet" href="<?= CSS_PATH ?>" />
    <?php else: ?>
        <link rel="stylesheet" href="<?= getBasePath() ?>/public/css/style.css" />
    <?php endif; ?>

    <script>
        window.BASE_PATH = "<?= getBasePath() ?>";
    </script>

</head>
<body>
    <div class="container-pagina">

        <header>
            <div class="header-logo">
                <div class="logo-icon">📊</div>
                <div class="logo-text">Dashboard</div>
            </div>
            <div class="header-actions">
                <!-- Espaço reservado para futuras ações -->
            </div>
        </header>

    
        <section>

            <h2 class="centro-horizontal">Seja bem vindo!</h2>
            <div id="loadingMessage" class="mensagem-sistema mensagem-sucesso">Verificando sessão...</div>
            <form id="loginForm" class="conteudo-centralizado" method="POST">
                <div class="campoDeEmail">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="campoDeSenha">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>                
                </div>

                <div class="grade-botao">
                    <div></div> <!-- Célula vazia -->
                        <button type="submit" class="btn-primary">Entrar</button>
                    <div></div> <!-- Célula vazia -->
                </div>

            </form>
            <div id="errorMessage" class="mensagem-erro oculta"></div>

        </section>
        
         <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>         

    </div>

 

    <!-- Módulo específico da página de login -->
    <!-- Módulo específico da página de login -->
    <script type="module" src="<?= getBasePath() ?>/public/js/recursos/usuarios/login.js"></script>
</body>
</html>
```
<?php
// /app/Views/recursos/usuarios/login.php

$title = 'Login';
$logo_icone = '🔐';
$logo_titulo = 'Área Restrita';