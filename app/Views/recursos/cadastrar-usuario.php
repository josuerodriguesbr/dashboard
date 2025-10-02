<?php
// /app/Views/recursos/cadastrar-usuario.php

$title = 'Cadastrar Usuário';

$inline_css =     '* { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
    }
    
    body {
      font-family: "Segoe UI", sans-serif;
      background: #f8f9fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .container {
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    header {
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: center;
      padding: 15px;
      background: #27ae60;
      color: white;
      font-weight: bold;
      font-size: 18px;
      border-radius: 8px 8px 0 0;
      margin: 0 auto;
      max-width: 600px;
    }
    
    .content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      margin: 0 auto;
      max-width: 600px;
      width: 100%;
    }
    
    .form-wrapper {
      background: white;
      border-radius: 8px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #333;
    }
    
    .form-group input, 
    .form-group select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      transition: border-color 0.3s;
    }
    
    .form-group input:focus, 
    .form-group select:focus {
      outline: none;
      border-color: #27ae60;
      box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.2);
    }
    
    .btn {
      width: 100%;
      padding: 12px;
      background: #55efc4;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      color: #2d3436;
      transition: background 0.3s;
      margin-top: 10px;
    }
    
    .btn:hover {
      background: #00b894;
      color: white;
    }
    
    .status {
      margin-top: 15px;
      text-align: center;
      color: #27ae60;
      font-weight: 500;
      min-height: 24px;
    }
    
    .error {
      color: #e74c3c;
    }
    
    .success {
      color: #27ae60;
    }
    
    /* Desktop styles */
    @media (min-width: 768px) {
      header {
        padding: 20px;
        font-size: 20px;
      }
      
      .content {
        padding: 30px;
      }
      
      .form-wrapper {
        padding: 35px;
      }
      
      .form-group {
        margin-bottom: 25px;
      }
    }
    
    /* Mobile styles */
    @media (max-width: 767px) {
      .content {
        padding: 10px;
      }
      
      .form-wrapper {
        padding: 15px;
      }
      
      header {
        border-radius: 0;
      }
    } ';



// Conteúdo do formulário
$content = '
    <header>➕ Cadastrar Usuário</header>
    <div class="content">
        <div class="form-wrapper">
            <form id="formCadastroUsuario">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Nome completo" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="email@empresa.com" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF/CNPJ</label>
                    <input type="text" id="cpf" name="cpf" placeholder="CPF ou CNPJ">
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" placeholder="(11) 99999-8888">
                </div>
                <div class="form-group">
                    <label for="nivel">Tipo de Usuário</label>
                    <select name="nivel" id="nivel" required>
                        <option value="">Selecione um tipo</option>
                        <option value="admin">Administrador</option>
                        <option value="assinante">Assinante</option>
                        <option value="vendedor">Vendedor</option>
                        <option value="cliente" selected>Cliente</option>
                    </select>
                </div>
                <button type="submit" class="btn">Cadastrar</button>
                <div class="status" id="status"></div>
            </form>
        </div>
    </div>
';

// ✅ Script JS específico desta página
$inline_js = "
    document.getElementById('formCadastroUsuario').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        const status = document.getElementById('status');
        status.textContent = 'Cadastrando...';
        status.className = 'status';

        try {
            const res = await fetch('/projetos/dashboard/cadastrar-usuario', {
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
                    const nivel = result.user.nivel;
                    const basePath = '/projetos/dashboard';
                    const rotas = {
                        'admin': basePath + '/admin',
                        'assinante': basePath + '/assinante',
                        'vendedor': basePath + '/vendedor',
                        'cliente': basePath + '/cliente'
                    };

                    // Apenas redirecionamos. O navegador enviará o cookie automaticamente
                    // na próxima requisição.
                    window.location.href = rotas[nivel] || basePath;
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
";

// Inclui o layout
include ROOT . 'app/Views/layout.php';