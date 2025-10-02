<?php
// app/Views/admin/dashboard.php

$content = '
    <div class="row">
        <div class="col">
            <iframe src="/projetos/dashboard/server-logs" class="frame" id="server-logs-frame"></iframe>
        </div>
        <div class="col">
            <iframe src="/projetos/dashboard/db-monitor" class="frame" id="db-monitor-frame"></iframe>
        </div>
        <div class="col">
            <iframe src="/projetos/dashboard/frontend" class="frame" id="frontend-frame"></iframe>
        </div>
    </div>
';

$title = 'Dashboard Admin';

$inline_js = "
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
";

// Inclui o layout diretamente aqui
include ROOT . 'app/Views/layout.php';