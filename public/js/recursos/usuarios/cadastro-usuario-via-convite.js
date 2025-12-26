// public/js/recursos/usuarios/cadastro-usuario-via-convite.js
import { attachCpfListener, focusFirstVisible, initBackButtons, mostrarNotificacao } from '/projetos/dashboard/public/js/funcoes.js';

function initFormScripts() {
    attachCpfListener(); // Atacha máscara de CPF (se existir o campo)
    // Inicializa botões de voltar (se houver .back-btn na página)
    try { initBackButtons(); } catch (e) { /* silent */ }
    // Tenta focar o primeiro campo visível dentro do formulário; faz retry curto caso autofill interfira
    let focused = focusFirstVisible('#formCadastroUsuarioViaConvite');
    if (!focused) {
        setTimeout(() => {
            focusFirstVisible('#formCadastroUsuarioViaConvite');
        }, 120);
    }

    const form = document.getElementById('formCadastroUsuarioViaConvite');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        // Adicionar o tipo de perfil do parâmetro da URL
        const urlParams = new URLSearchParams(window.location.search);
        data.tipoPerfil = urlParams.get('tipo') || 'cliente';
        
        // Exibe notificação de processamento
        mostrarNotificacao('Cadastrando via convite...', 'info', 3000);

        try {
            const res = await fetch('/projetos/dashboard/cadastro-via-convite', {
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
                // Exibe notificação de sucesso
                mostrarNotificacao('✅ Usuário cadastrado com sucesso via convite!', 'sucesso', 5000);
                e.target.reset();
                
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);

            } else {
                // Exibe notificação de erro
                mostrarNotificacao('❌ ' + result.message, 'erro', 5000);
            }
        } catch (error) {
            // Exibe notificação de erro
            mostrarNotificacao('❌ Erro: ' + error.message, 'erro', 5000);
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