<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $title ?? 'Dashboard' ?></title>
    
    <link rel="stylesheet" href="/projetos/dashboard/public/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
    
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <h1>📊 Dashboard Integrado</h1>
        </header>

        <?= $content ?>

        <footer style="text-align: center; padding: 20px; color: #777;">
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>
    </div>

<script>
    // A VERIFICAÇÃO DEVE OCORRER NO LADO DO SERVIDOR, NÃO NO CLIENTE
    // Não precisamos mais do token no JavaScript, pois o navegador o envia automaticamente.

    // A lógica de redirecionamento para páginas restritas
    const paginaRestrita = ['/admin', '/assinante', '/vendedor', '/cliente'].some(page => 
        window.location.pathname.includes('/projetos/dashboard' + page));

    // Se estiver em uma página restrita, faz uma requisição ao backend
    // para que ele verifique o cookie e retorne a validação.
    // O backend pode, inclusive, fazer o redirecionamento diretamente.
    if (paginaRestrita) {
        console.log('🔹 Página restrita detectada. Verificando sessão...');
        
        // Requisição para o endpoint de verificação.
        // O navegador enviará o cookie 'authToken' automaticamente.
        fetch('/projetos/dashboard/verificar-token', {
            // Nenhuma header 'Authorization' é necessária, pois o cookie vai sozinho.
            method: 'GET' 
        })
        .then(response => {
            if (response.ok) {
                // Se o servidor responde com sucesso (200 OK), o token é válido.
                // Isso pode ser uma resposta com o usuário, por exemplo.
                return response.json();
            } else if (response.status === 401) {
                // O servidor retornou "Não autorizado", o que significa que o token é inválido/inexistente.
                console.warn('⚠️ Token inválido ou não autorizado. Redirecionando...');
                window.location.href = '/projetos/dashboard/';
                return Promise.reject('Não autorizado');
            } else {
                // Outro erro de servidor
                throw new Error('Erro ao verificar sessão');
            }
        })
        .then(data => {
            console.log('✅ Sessão válida.');
            // Opcional: Se a resposta JSON incluir o nível do usuário, você pode usá-lo.
            // Ex: if (data.user.nivel !== 'admin') { window.location.href = '/...'; }
        })
        .catch(error => {
            console.error('🚨 Erro na verificação:', error);
            // Se o catch for ativado por um erro diferente de 401, ainda é seguro redirecionar.
            // window.location.href = '/projetos/dashboard/'; 
        });
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