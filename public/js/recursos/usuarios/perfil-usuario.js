// public/js/recursos/usuarios/perfil-usuario.js
import { attachCpfListener, focusFirstVisible, mostrarNotificacao } from '/projetos/dashboard/public/js/funcoes.js';

document.addEventListener('DOMContentLoaded', function() {
    // Atach listeners
    attachCpfListener();
    focusFirstVisible('#formPerfilUsuario');

    // Carregar dados do perfil ao iniciar
    carregarPerfil();

    // Adicionar listener para o formulário
    const form = document.getElementById('formPerfilUsuario');
    if (form) {
        form.addEventListener('submit', atualizarPerfil);
    }
});

async function carregarPerfil() {
    const statusDiv = document.getElementById('status');
    
    try {
        const response = await fetch('/projetos/dashboard/perfil/carregar');
        const result = await response.json();
        
        if (result.success) {
            preencherFormulario(result.usuario);
        } else {
            mostrarNotificacao(result.message || "Erro ao carregar perfil", 'erro', 5000);
        }
    } catch (error) {
        mostrarNotificacao("Erro ao conectar com o servidor", 'erro', 5000);
        console.error('Erro:', error);
    }
}

async function atualizarPerfil(event) {
    event.preventDefault();
    
    const form = document.getElementById('formPerfilUsuario');
    const statusDiv = document.getElementById('status');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Se a senha estiver vazia, removê-la dos dados
    if (!data.senha) {
        delete data.senha;
    }
    
    // Exibe notificação de processamento
    mostrarNotificacao("Atualizando perfil...", 'info', 3000);
    
    try {
        const response = await fetch('/projetos/dashboard/atualiza-usuario', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        // Verificar se a resposta é realmente JSON
        const responseText = await response.text();
        console.log('Resposta do servidor:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            throw new Error('Resposta inválida do servidor: ' + responseText);
        }
        
        if (result.success) {
            // Exibe notificação de sucesso
            mostrarNotificacao("✅ Perfil atualizado com sucesso!", 'sucesso', 5000);
            
            // Atualizar o nome do usuário no cabeçalho
            const nomeUsuario = document.getElementById('nome').value;
            atualizarNomeUsuarioNoCabecalho(nomeUsuario);
            
            // Atualizar o cookie authToken com os novos dados
            // Isso vai garantir que quando o usuário voltar, os dados estejam atualizados
            if (result.token) {
                document.cookie = `authToken=${result.token}; path=/projetos/dashboard/;`;
            }
            
            // Atualizar também o objeto window.usuario
            if (window.usuario) {
                window.usuario.nome = nomeUsuario;
            }
            
            // Opcionalmente, recarregar a página após um curto intervalo para garantir que todos os dados estejam sincronizados
            setTimeout(() => {
                // Não recarregar a página, apenas mostrar mensagem de sucesso
            }, 1500);
        } else {
            mostrarNotificacao(result.message || "Erro ao atualizar perfil", 'erro', 5000);
        }
    } catch (error) {
        mostrarNotificacao("Erro ao conectar com o servidor: " + error.message, 'erro', 5000);
        console.error('Erro:', error);
    }
}

function preencherFormulario(usuario) {
    document.getElementById('id').value = usuario.id || '';
    document.getElementById('nome').value = usuario.nome || usuario.name || '';
    document.getElementById('email').value = usuario.email || '';
    document.getElementById('cpf').value = usuario.cpf || '';
    document.getElementById('telefone').value = usuario.telefone || '';
    
    // Atualizar o nome do usuário no cabeçalho
    atualizarNomeUsuarioNoCabecalho(usuario.nome);
}

function atualizarNomeUsuarioNoCabecalho(nome) {
    const userNameElement = document.querySelector('.user-name');
    if (userNameElement) {
        userNameElement.textContent = nome;
    }
}

function determinarDashboardUrl(nivel) {
    const basePath = '/projetos/dashboard';
    switch(nivel) {
        case 'admin': return basePath + '/admin';
        case 'assinante': return basePath + '/assinante';
        case 'operador': return basePath + '/operador';
        case 'vendedor': return basePath + '/vendedor';
        default: return basePath + '/cliente';
    }
}