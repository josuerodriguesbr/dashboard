<?php
// jwt-demo-cookie.php - Versão com Cookie HttpOnly

// --- CONFIGURAÇÃO E FUNÇÕES JWT (Idênticas à versão anterior) ---
define('ROOT', __DIR__ . '/');
require_once ROOT . 'config/jwt-secret.php';

$usuariosFile = ROOT . 'usuarios-jwtck.json';
if (!file_exists($usuariosFile)) file_put_contents($usuariosFile, json_encode([]));

// Funções JWT (base64url_encode, jwt_encode, etc.) são as mesmas...
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function jwt_encode($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    // Adiciona tempo de expiração de 20 minutos
    $payload['exp'] = time() + (20 * 60);
    $payload = json_encode($payload);

    $base64UrlHeader = base64url_encode($header);
    $base64UrlPayload = base64url_encode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = base64url_encode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function jwt_decode($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
    
    $signature = base64url_decode($base64UrlSignature);
    $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);

    if (!hash_equals($expectedSignature, $signature)) {
        return null; // Assinatura inválida
    }

    $payload = json_decode(base64url_decode($base64UrlPayload), true);

    // Verifica se o token expirou
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return null; // Token expirado
    }

    return $payload;
}
// 

// --- FIM DAS FUNÇÕES ---

$usuarios = json_decode(file_get_contents($usuariosFile), true) ?? [];
$user = null;
$action = $_POST['action'] ?? '';

// --- LÓGICA DE AÇÕES (POST) ---

// Resetar tudo
if (isset($_POST['reset'])) {
    @unlink($usuariosFile);
    // Limpa o cookie de autenticação ao resetar
    setcookie('authToken', '', time() - 3600, '/', '', false, true);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Ação de Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    header('Content-Type: application/json');
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $foundUser = null;

    // Lógica de encontrar/criar usuário (idêntica à anterior)
    if ($nome && $senha) {
        if (!empty($usuarios)) {
            foreach ($usuarios as $u) {
                if ($u['nome'] === $nome && $u['senha'] === $senha) {
                    $foundUser = ['nome' => $u['nome'], 'nivel' => $u['nivel']];
                    break;
                }
            }
        } else {
            $novoUsuario = ['nome' => $nome, 'senha' => $senha, 'nivel' => 'usuario', 'criado' => date('Y-m-d H:i:s')];
            file_put_contents($usuariosFile, json_encode([$novoUsuario]));
            $foundUser = ['nome' => $novoUsuario['nome'], 'nivel' => $novoUsuario['nivel']];
        }
    }
    
    if ($foundUser) {
        $jwt = jwt_encode($foundUser);
        $cookie_expiration = time() + (20 * 60); // 20 minutos
        
        // ALTERADO: Em vez de enviar token no JSON, definimos um cookie HttpOnly
        setcookie('authToken', $jwt, [
            'expires' => $cookie_expiration,
            'path' => '/',
            // 'domain' => '.seusite.com', // Descomente e ajuste se necessário
            'secure' => false, // Mude para true se estiver usando HTTPS
            'httponly' => true, // Essencial: o cookie não pode ser acessado por JS
            'samesite' => 'Lax' // Proteção contra CSRF
        ]);
        
        // Apenas informamos ao JS que o login foi um sucesso
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nome ou senha inválidos.']);
    }
    exit;
}

// NOVO: Ação de Logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'logout') {
    header('Content-Type: application/json');
    // Para "deletar" um cookie, o definimos com um tempo de expiração no passado.
    setcookie('authToken', '', time() - 3600, '/', '', false, true);
    echo json_encode(['success' => true]);
    exit;
}

// --- LÓGICA PARA CARREGAMENTO DA PÁGINA (GET) ---

// ALTERADO: A verificação agora é feita diretamente pelo cookie enviado pelo navegador.
// Não precisamos mais de `verify_token` na URL.
if (isset($_COOKIE['authToken'])) {
    $user = jwt_decode($_COOKIE['authToken']);
}

// Se o usuário foi autenticado, geramos um novo token para renovar o tempo de expiração do cookie.
if ($user) {
    $jwt = jwt_encode($user);
    setcookie('authToken', $jwt, [ 'expires' => time() + (20 * 60), 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax' ]);
} else {
    $jwt = null; // Garante que o token não seja exibido se o cookie for inválido
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo JWT Simples</title>
        <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input, button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .logout-btn {
            background: #dc3545;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .reset-btn {
            background: #6c757d;
        }
        .info {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container" id="app">
        <?php if ($user && $jwt): ?>
            <h2>🔐 Logado como: <?= htmlspecialchars($user['nome']) ?> (Cookie)</h2>
            <p class="info">Sessão válida por 20 minutos</p>
            <button class="logout-btn" onclick="logout()">Logout</button>
            <button class="reset-btn" onclick="resetAll()">Resetar Tudo</button>
            <p><small>Token está em um cookie HttpOnly e não pode ser lido via JS.</small></p>
        <?php else: ?>
            <h2>📝 Cadastro / Login (Cookie)</h2>
            <form id="loginForm">
                <input type="text" id="nome" placeholder="Seu nome" required>
                <input type="password" id="senha" placeholder="Senha" required>
                <button type="submit">Cadastrar / Entrar</button>
                <div id="errorMessage" class="error" style="margin-top: 10px;"></div>
            </form>
            <button class="reset-btn" onclick="resetAll()">Resetar Tudo</button>
        <?php endif; ?>
    </div>

<script>
    // REMOVIDO: Toda a lógica de `window.onload` para verificar token é desnecessária.
    // O PHP já sabe se o usuário está logado ou não quando a página é construída.

    // Lógica de submit do formulário de login
    document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nome = document.getElementById('nome').value;
        const senha = document.getElementById('senha').value;
        const errorMessageDiv = document.getElementById('errorMessage');
        errorMessageDiv.textContent = '';

        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('nome', nome);
        formData.append('senha', senha);

        try {
            const response = await fetch('', { method: 'POST', body: formData });
            const data = await response.json();

            // ALTERADO: Não precisamos mais receber e salvar o token.
            // Apenas verificamos se o login deu certo e recarregamos a página.
            // O navegador já recebeu e salvou o cookie.
            if (data.success) {
                window.location.href = window.location.pathname; // Recarrega para uma URL limpa
            } else {
                errorMessageDiv.textContent = data.message || 'Ocorreu um erro.';
            }
        } catch (error) {
            errorMessageDiv.textContent = 'Erro de comunicação com o servidor.';
        }
    });

    // ALTERADO: Função de logout agora chama o backend para limpar o cookie.
    async function logout() {
        console.log("🚀 Saindo...");
        const formData = new FormData();
        formData.append('action', 'logout');
        
        await fetch('', { method: 'POST', body: formData });
        
        // Após o backend limpar o cookie, redirecionamos.
        window.location.href = window.location.pathname;
    }

    // Função de reset (praticamente a mesma, só mudamos o corpo do POST)
    function resetAll() {
        if (confirm('Tem certeza que quer resetar tudo?')) {
            const formData = new FormData();
            formData.append('reset', '1');
            fetch('', {
                method: 'POST',
                body: formData
            }).then(() => {
                window.location.href = window.location.pathname;
            });
        }
    }
</script>

</body>
</html>