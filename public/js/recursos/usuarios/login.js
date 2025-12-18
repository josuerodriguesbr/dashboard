// public/js/recursos/usuarios/login.js
import { focusFirstVisible } from '/projetos/dashboard/public/js/funcoes.js';

async function verificarSessaoExistente() {
  try {
    console.log('Verificando sessão existente...');
    const response = await fetch('/projetos/dashboard/verificar-token', {
      method: 'GET',
      credentials: 'same-origin'
    });
    
    console.log('Status da resposta:', response.status);
    const text = await response.text();
    console.log('Texto da resposta:', text);
    
    if (!text) {
      console.error('Resposta vazia do servidor');
      document.getElementById('loadingMessage')?.classList.add('oculta');
      document.getElementById('loginForm')?.classList.remove('oculta');
      return false;
    }
    
    let data;
    try {
      data = JSON.parse(text);
    } catch (parseError) {
      console.error('Erro ao parsear JSON:', parseError);
      console.error('Conteúdo recebido:', text);
      document.getElementById('loadingMessage')?.classList.add('oculta');
      document.getElementById('loginForm')?.classList.remove('oculta');
      return false;
    }
    
    if (data.authenticated) {
      const nivel = data.user.nivel;
      const basePath = '/projetos/dashboard';
      const rotas = {
        'admin': basePath + '/admin',
        'assinante': basePath + '/assinante',
        'operador': basePath + '/operador',
        'vendedor': basePath + '/vendedor',
        'cliente': basePath + '/cliente'
      };
      window.location.href = rotas[nivel] || basePath;
      return true;
    } else {
      document.getElementById('loadingMessage')?.classList.add('oculta');
      document.getElementById('loginForm')?.classList.remove('oculta');
      return false;
    }
  } catch (error) {
    console.error('Erro ao verificar sessão:', error);
    document.getElementById('loadingMessage')?.classList.add('oculta');
    document.getElementById('loginForm')?.classList.remove('oculta');
    return false;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Atacha máscara de CPF se houver (não é comum nesta página, mas seguro)
  try { attachCpfListener(); } catch (e) { /* ignore */ }

  // Verifica sessão e mostra formulário se necessário
  verificarSessaoExistente();

  // Foca o primeiro campo visível do formulário de login
  try { focusFirstVisible('#loginForm'); } catch (e) { /* ignore */ }

  // Handler do submit do formulário de login
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const email = document.getElementById('email')?.value || '';
      const senha = document.getElementById('senha')?.value || '';
      const errorMessage = document.getElementById('errorMessage');
      if (errorMessage) errorMessage.classList.add('oculta');

      try {
        const res = await fetch('/projetos/dashboard/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ email: email, senha: senha })
        });

        const text = await res.text();
        console.log('Resposta do login:', text);
        
        if (!text) {
          throw new Error('Resposta vazia do servidor');
        }
        
        let data;
        try {
          data = JSON.parse(text);
        } catch (parseError) {
          console.error('Erro ao parsear JSON:', parseError);
          throw new Error('Resposta inválida do servidor: ' + text);
        }

        if (data.success) {
          window.location.href = data.redirect;
        } else if (errorMessage) {
          errorMessage.textContent = data.message;
          errorMessage.classList.remove('oculta');
        }
      } catch (error) {
        console.error('Erro durante o login:', error);
        if (errorMessage) {
          errorMessage.textContent = 'Erro ao fazer login: ' + error.message;
          errorMessage.classList.remove('oculta');
        }
      }
    });
  }

  // Registrar service worker (se suportado)
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/projetos/dashboard/public/sw.js')
        .then(reg => console.log('SW registrado:', reg))
        .catch(err => console.log('Erro no SW:', err));
    });
  }
});