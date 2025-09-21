<?php
$content = '
    <div class="row">
        <div class="col"><div id="server-logs"></div></div>
        <div class="col"><div id="db-monitor"></div></div>
        <div class="col"><div id="frontend"></div></div>
    </div>
';

$title = 'Dashboard Admin';

$inline_js = "
    // O JavaScript não precisa mais manipular o token, pois o navegador o envia automaticamente.
    // As linhas a seguir foram removidas por serem incorretas e inseguras:
    // const token = localStorage.getItem('authToken');
    // const token = document.cookie = 'authToken=' + token + '; path=/';

    // A lógica de verificação deve ser feita no PHP antes de renderizar a página.
    // O backend já deve ter verificado o cookie e redirecionado o usuário se ele não for válido.

    const rotas = ['server-logs', 'db-monitor', 'frontend'];
    
    rotas.forEach(rota => {
        fetch('/projetos/dashboard/' + rota, {
            method: 'GET'
            // Não é necessário o cabeçalho 'Authorization'. O navegador enviará o cookie automaticamente.
        })
        .then(response => {
            // Se o servidor negar o acesso (e.g., status 401), redirecione para o login.
            if (response.status === 401 || response.status === 403) {
                console.error('❌ Acesso negado. Redirecionando...');
                window.location.href = '/projetos/dashboard/';
                // A throw new Error garante que o código não continue.
                throw new Error('Acesso negado');
            }
            if (!response.ok) {
                throw new Error('Erro ao carregar os dados.');
            }
            return response.text();
        })
        .then(html => {
            const id = rota.replace('-', '');
            const container = document.getElementById(id);
            if (container) {
                container.innerHTML = html;
            }
        })
        .catch(err => {
            console.error('Erro ao carregar:', rota, err);
            // Redireciona em caso de erro de rede ou qualquer outro erro na requisição.
            window.location.href = '/projetos/dashboard/';
        });
    });
";

include ROOT . 'app/Views/layout.php';