// public/js/recursos/usuarios/cadastro-usuario.js
import { attachCpfListener, focusFirstVisible } from '/projetos/dashboard/public/js/funcoes.js';

function initFormScripts() {
    attachCpfListener(); // Atacha máscara de CPF (se existir o campo)
    // Tenta focar o primeiro campo visível dentro do formulário; faz retry curto caso autofill interfira
    let focused = focusFirstVisible('#formCadastroUsuario');
    if (!focused) {
        setTimeout(() => {
            focusFirstVisible('#formCadastroUsuario');
        }, 120);
    }

    const form = document.getElementById('formCadastroUsuario');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    const status = document.getElementById('status');
    status.textContent = 'Cadastrando...';
    status.className = 'status';

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
        status.textContent = '❌ Erro: ' + error.message;
        status.className = 'status error';
    }

    setTimeout(() => { 
        status.textContent = ''; 
        status.className = 'status';
    }, 10000);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFormScripts);
} else {
    // módulo já executando após carregamento do DOM na maioria dos casos, mas
    // chamamos init para garantir comportamento
    initFormScripts();
}
