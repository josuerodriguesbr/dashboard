// /app/Views/recursos/usuarios/perfil-usuario.js
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
    const statusDiv = document.getElementById('status');
    
    // Mostrar mensagem de carregamento
    statusDiv.textContent = "Carregando dados do perfil...";
    statusDiv.className = "status";
    statusDiv.style.display = "block";
    
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
            statusDiv.style.display = "none";
        } else {
            statusDiv.textContent = data.message || "Erro ao carregar dados do perfil";
            statusDiv.className = "status error";
        }
    })
    .catch(error => {
        statusDiv.textContent = "Erro ao conectar com o servidor";
        statusDiv.className = "status error";
        console.error('Erro:', error);
    });
}

function atualizarPerfil() {
    const form = document.getElementById('formPerfilUsuario');
    const statusDiv = document.getElementById('status');
    
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
    
    // Mostrar mensagem de carregamento
    statusDiv.textContent = "Atualizando perfil...";
    statusDiv.className = "status";
    statusDiv.style.display = "block";
    
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
            statusDiv.textContent = "Perfil atualizado com sucesso!";
            statusDiv.className = "status success";
            
            // Atualizar o nome do usuário no cabeçalho
            const nomeUsuario = document.getElementById('nome').value;
            atualizarNomeUsuarioNoCabecalho(nomeUsuario);
            
            // Remover qualquer botão anterior se existir
            const existingBtn = statusDiv.querySelector('.btn-dashboard');
            if (existingBtn) {
                existingBtn.remove();
            }
            
        } else {
            statusDiv.textContent = data.message || "Erro ao atualizar perfil";
            statusDiv.className = "status error";
        }
    })
    .catch(error => {
        statusDiv.textContent = "Erro ao conectar com o servidor";
        statusDiv.className = "status error";
        console.error('Erro:', error);
    });
}

// Função para preencher o formulário com dados do usuário
function preencherFormulario(usuario) {
    document.getElementById('id').value = usuario.id || '';
    document.getElementById('nome').value = usuario.nome || '';
    document.getElementById('email').value = usuario.email || '';
    document.getElementById('cpf').value = usuario.cpf || '';
    document.getElementById('telefone').value = usuario.telefone || '';
    document.getElementById('nivel').value = usuario.nivel || 'cliente';
    
    // Limpar campo de senha por segurança
    document.getElementById('senha').value = '';
}

// Função para atualizar o nome do usuário no cabeçalho
function atualizarNomeUsuarioNoCabecalho(novoNome) {
    const userNameElement = document.querySelector('.user-name');
    if (userNameElement) {
        userNameElement.textContent = novoNome;
    }
    
    // Também atualizar a variável global window.usuario se existir
    if (window.usuario) {
        window.usuario.nome = novoNome;
    }
}