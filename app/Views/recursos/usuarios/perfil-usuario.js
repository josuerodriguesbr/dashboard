// /app/Views/recursos/usuarios/perfil-usuario.js
import { mostrarNotificacao } from '/projetos/dashboard/public/js/funcoes.js';

document.addEventListener('DOMContentLoaded', function() {
    // Carregar os dados do usuário quando a página for carregada
    carregarDadosUsuario();
    
    // Adicionar listener para o formulário
    const form = document.getElementById('formPerfilUsuario');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            atualizarPerfil();
        });
    }
    
    // Adicionar listener para o botão de cancelar
    const cancelarBtn = document.getElementById('cancelarBtn');
    if (cancelarBtn) {
        cancelarBtn.addEventListener('click', function() {
            if (confirm('Tem certeza que deseja cancelar e voltar ao dashboard?')) {
                window.location.href = determinarDashboardUrl();
            }
        });
    }
});

function carregarDadosUsuario() {
    // Mostrar notificação de carregamento
    mostrarNotificacao("Carregando dados do perfil...", 'info', 3000);
    
    // Fazer requisição para carregar os dados do perfil
    fetch('/projetos/dashboard/perfil/carregar', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherFormulario(data.usuario);
        } else {
            mostrarNotificacao(data.message || "Erro ao carregar dados do perfil", 'erro', 5000);
        }
    })
    .catch(error => {
        mostrarNotificacao("Erro ao conectar com o servidor", 'erro', 5000);
        console.error('Erro:', error);
    });
}

function atualizarPerfil() {
    const form = document.getElementById('formPerfilUsuario');
    
    // Obter dados do formulário
    const formData = new FormData(form);
    let usuarioData = Object.fromEntries(formData.entries());
    
    // Remover campo de ID se estiver vazio
    if (!usuarioData.id) {
        delete usuarioData.id;
    }
    
    // Se a senha estiver vazia, remova do objeto para não atualizar
    if (!usuarioData.senha) {
        delete usuarioData.senha;
    }
    
    // Mostrar notificação de carregamento
    mostrarNotificacao("Atualizando perfil...", 'info', 3000);
    
    // Enviar dados para o servidor
    fetch('/projetos/dashboard/atualiza-usuario', {
        method: 'POST',
        body: JSON.stringify(usuarioData),
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacao("✅ Perfil atualizado com sucesso!", 'sucesso', 5000);
            
            // Atualizar o nome do usuário no cabeçalho
            const nomeUsuario = document.getElementById('nome').value;
            atualizarNomeUsuarioNoCabecalho(nomeUsuario);
            
        } else {
            mostrarNotificacao(data.message || "Erro ao atualizar perfil", 'erro', 5000);
        }
    })
    .catch(error => {
        mostrarNotificacao("Erro ao conectar com o servidor", 'erro', 5000);
        console.error('Erro:', error);
    });
}

// Função para preencher o formulário com dados do usuário
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