// public/js/admin/dashboard.js

const rotas = ['server-logs', 'db-monitor', 'frontend'];

rotas.forEach(rota => {
    fetch('/projetos/dashboard/' + rota, {
        method: 'GET',
        credentials: 'same-origin' // Garante que cookies são enviados
    })
    .then(response => {
        if (response.status === 401 || response.status === 403) {
            console.error('❌ Acesso negado para: ' + rota);
            throw new Error('Acesso negado');
        }
        if (!response.ok) {
            throw new Error('Erro ao carregar ' + rota + ': ' + response.status);
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
        const id = rota.replace('-', '');
        const container = document.getElementById(id);
        if (container) {
            container.innerHTML = '<p>Erro ao carregar conteúdo</p>';
        }
    });
});