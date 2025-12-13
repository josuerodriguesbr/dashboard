<!DOCTYPE html>
<html>
<head>

<!-- Manifest -->
<link rel="manifest" href="public/manifest.json">

<!-- Tema do Android -->
<meta name="theme-color" content="#0066cc">

<!-- Para iOS (opcional, mas recomendado) -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="apple-touch-icon" href="public/icone-192.png">

    <title>Login - Sistema</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (defined('CSS_PATH') && !empty(CSS_PATH)): ?>
        <link rel="stylesheet" href="<?= CSS_PATH ?>" />
    <?php else: ?>
        <link rel="stylesheet" href="/projetos/dashboard/public/css/style.css" />
    <?php endif; ?>

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
            <form id="loginForm" class="conteudo-centralizado">
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
            <div class="link-primario">
                <a href="/projetos/dashboard/mostra-cadastro-usuario">Não tem conta? Cadastre-se</a>
            </div>

        </section>
        
         <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>         

    </div>

 

    <script>
        // Verifica automaticamente se o usuário já está autenticado
        document.addEventListener('DOMContentLoaded', function() {
            verificarSessaoExistente();
        });

function verificarSessaoExistente() {
    fetch('/projetos/dashboard/verificar-token', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.authenticated) {
            // Usuário já está autenticado, redireciona para o painel correto
            const nivel = data.user.nivel;
            const basePath = '/projetos/dashboard';
            const rotas = {
                'admin': basePath + '/admin',
                'assinante': basePath + '/assinante',
                'vendedor': basePath + '/vendedor',
                'cliente': basePath + '/cliente'
            };
            
            window.location.href = rotas[nivel] || basePath;
        } else {
            // Usuário não autenticado, mostra o formulário de login
            document.getElementById('loadingMessage').classList.add('oculta');
            document.getElementById('loginForm').classList.remove('oculta');
        }
    })
    .catch(error => {
        // Em caso de erro, mostra o formulário de login
        console.error('Erro ao verificar sessão:', error);
        document.getElementById('loadingMessage').classList.add('oculta');
        document.getElementById('loginForm').classList.remove('oculta');
    });
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;
    const errorMessage = document.getElementById('errorMessage');
    
    // Oculta mensagens anteriores
    errorMessage.classList.add('oculta');
    
    fetch('/projetos/dashboard/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            email: email,
            senha: senha
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // O servidor já definiu o cookie HttpOnly na resposta.
            // Apenas redirecionamos o usuário.
            window.location.href = data.redirect;
        } else {
            errorMessage.textContent = data.message;
            errorMessage.classList.remove('oculta');
        }
    })
    .catch(error => {
        errorMessage.textContent = 'Erro ao fazer login';
        errorMessage.classList.remove('oculta');
    });
});



  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('public/sw.js')
        .then(reg => console.log('SW registrado:', reg))
        .catch(err => console.log('Erro no SW:', err));
    });
  }


    </script>
</body>
</html>