// /app/Views/recursos/usuarios/cadastro-usuario.js

import { focusFirstVisible, attachCpfListener, mostrarNotificacao } from '/projetos/dashboard/public/js/funcoes.js';

focusFirstVisible(); // Foca o primeiro campo do formulário

attachCpfListener(); // Atacha máscara de CPF (se existir o campo)

document.getElementById('formCadastroUsuario').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    // Exibe notificação de processamento
    mostrarNotificacao('Cadastrando...', 'info', 3000);

    try {
        const res = await fetch('/projetos/dashboard/cadastro-usuario', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Resposta inválida do servidor.');
        }

        const result = await res.json();
        
        if (result.success) {
            // AQUI É A GRANDE MUDANÇA: Não salvamos mais o token no localStorage
            // O token foi definido como cookie pelo servidor.
            status.textContent = '✅ Usuário cadastrado com sucesso!';
            status.className = 'status success';
            e.target.reset();
            
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);

        } else {
            status.textContent = '❌ ' + result.message;
            status.className = 'status error';
        }
    } catch (error) {
        mostrarNotificacao('❌ Erro: ' + error.message, 'erro', 5000);
    }
});