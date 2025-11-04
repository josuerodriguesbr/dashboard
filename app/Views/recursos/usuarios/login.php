<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 0 15px;
            flex: 1;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            font-size: 24px;
            color: #3498db;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 500;
        }
        
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            margin: 30px auto;
        }
        
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: #e74c3c;
            margin-top: 15px;
            text-align: center;
            display: none;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .loading {
            text-align: center;
            color: #666;
            margin: 20px 0;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            color: #777;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="container">

    <!-- 
        <header class="dashboard-header">
            <div class="header-logo">
                <div class="logo-icon">📊</div>
                <div class="logo-text">Dashboard</div>
            </div>
            <div class="header-actions">
                <!-- Espaço reservado para futuras ações 
            </div>
        </header>
        -->

        <div class="login-container">
            <h2>Acesso ao Sistema</h2>
            <div id="loadingMessage" class="loading">Verificando sessão...</div>
            <form id="loginForm" style="display: none;">
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>                
                </div>
                <button type="submit">Entrar</button>
            </form>
            <div id="errorMessage" class="error-message"></div>
            <div class="register-link">
                <a href="/projetos/dashboard/cadastro-usuario">Não tem conta? Cadastre-se</a>
            </div>
        </div>

        <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>
    </div>

    <script>
        // Verifica automaticamente se o usuário já está autenticado
        document.addEventListener('DOMContentLoaded', function() {
            verificarSessaoExistente();
        });

        function verificarSessaoExistente() {
            fetch('/projetos/dashboard/verificar-token', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.authenticated) {
                    // Usuário já está autenticado, redireciona para o painel correto
                    const nivel = data.user.nivel;
                    const basePath = '/projetos/dashboard';
                    const rotas = {
                        'admin': basePath + '/admin',
                        'assinante': basePath + '/assinante',
                        'vendedor': basePath + '/vendedor',
                        'cliente': basePath + '/cliente'
                    };
                    
                    window.location.href = rotas[nivel] || basePath;
                } else {
                    // Usuário não autenticado, mostra o formulário de login
                    document.getElementById('loadingMessage').style.display = 'none';
                    document.getElementById('loginForm').style.display = 'block';
                }
            })
            .catch(error => {
                // Em caso de erro, mostra o formulário de login
                console.error('Erro ao verificar sessão:', error);
                document.getElementById('loadingMessage').style.display = 'none';
                document.getElementById('loginForm').style.display = 'block';
            });
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const errorMessage = document.getElementById('errorMessage');
            
            fetch('/projetos/dashboard/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    email: email,
                    senha: senha
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // O servidor já definiu o cookie HttpOnly na resposta.
                    // Apenas redirecionamos o usuário.
                    window.location.href = data.redirect;
                } else {
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Erro ao fazer login';
                errorMessage.style.display = 'block';
            });
        });
    </script>
</body>
</html>