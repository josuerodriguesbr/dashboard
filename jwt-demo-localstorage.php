<?php
// jwt-demo.php - Demonstração simples de JWT com PHP + localStorage

// --- CONFIGURAÇÃO E FUNÇÕES ---
define('ROOT', __DIR__ . '/');
// Crie este arquivo com define('JWT_SECRET', 'sua-chave-secreta-muito-longa');
require_once ROOT . 'config/jwt-secret.php';

$usuariosFile = ROOT . 'usuarios-jwtls.json';

// Cria arquivo se não existir
if (!file_exists($usuariosFile)) {
    file_put_contents($usuariosFile, json_encode([]));
}

// Funções JWT (Implementação de exemplo)
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
// --- FIM DAS FUNÇÕES ---


// Carrega usuários do arquivo
$usuarios = json_decode(file_get_contents($usuariosFile), true) ?? []; // ?? [] para garantir array

$user = null;

// Resetar tudo
if (isset($_POST['reset'])) {
    @unlink($usuariosFile);
    // Para requisições fetch, apenas retornamos sucesso.
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => true]);
        exit;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// NOVO: Lógica para tratar a submissão do formulário de login via fetch/AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $foundUser = null;

    if ($nome && $senha) {
        if (!empty($usuarios)) {
            // Lógica de login: procura o usuário
            // ALTERADO: Itera sobre os usuários em vez de checar apenas o primeiro
            foreach ($usuarios as $u) {
                if ($u['nome'] === $nome && $u['senha'] === $senha) {
                    $foundUser = ['nome' => $u['nome'], 'nivel' => $u['nivel']];
                    break;
                }
            }
        } else {
            // Primeiro cadastro
            $novoUsuario = [
                'nome' => $nome,
                'senha' => $senha,
                'nivel' => 'usuario',
                'criado' => date('Y-m-d H:i:s')
            ];
            file_put_contents($usuariosFile, json_encode([$novoUsuario]));
            $foundUser = ['nome' => $novoUsuario['nome'], 'nivel' => $novoUsuario['nivel']];
        }
    }
    
    if ($foundUser) {
        $jwt = jwt_encode($foundUser);
        echo json_encode(['success' => true, 'token' => $jwt]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nome ou senha inválidos.']);
    }
    exit; // Termina o script aqui para não enviar o HTML
}


// --- LÓGICA PARA CARREGAMENTO DA PÁGINA (GET) ---

// 1. Tenta pelo token na URL (enviado pelo JS para verificação)
$token = $_GET['verify_token'] ?? null;
$token_invalido = false; // NOVO: Flag para token inválido

if ($token) {
    $user = jwt_decode($token);
    if (!$user) { // ALTERADO: Se a decodificação falhar (expirado, inválido)...
        $token_invalido = true; // ...marcamos a flag.
    }
}

// Se o usuário foi autenticado, gera um novo token (renova a expiração)
$jwt = $user ? jwt_encode($user) : null;

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
            <h2>🔐 Logado como: <?= htmlspecialchars($user['nome']) ?></h2>
            <p class="info">Token válido por 20 minutos</p>
            <button class="logout-btn" onclick="logout()">Logout</button>
            <button class="reset-btn" onclick="resetAll()">Resetar Tudo</button>
            <p><small>Token: <code><?= htmlspecialchars($jwt) ?></code></small></p>
        <?php else: ?>
            <h2>📝 Cadastro / Login</h2>
            <form id="loginForm">
                <input type="text" id="nome" placeholder="Seu nome" required>
                <input type="password" id="senha" placeholder="Senha" required>
                <button type="submit">Cadastrar / Entrar</button>
                <div id="errorMessage" class="error" style="margin-top: 10px;"></div>
            </form>
            <button class="reset-btn" onclick="resetAll()">Resetar Tudo</button>
        <?php endif; ?>
    </div>

<?php if ($token_invalido): ?>
<script>
    console.error("Token de verificação inválido ou expirado. Limpando o localStorage.");
    localStorage.removeItem('authToken');
</script>
<?php endif; ?>

<script>
    // Se a página foi carregada com um usuário logado, salva/atualiza o token.
    <?php if ($jwt): ?>
    console.log("🔐 Token válido, salvando no localStorage:", '<?= addslashes($jwt) ?>');
    localStorage.setItem('authToken', '<?= addslashes($jwt) ?>');
    <?php endif; ?>

    // Tenta autenticar via localStorage ao carregar a página
    window.onload = () => {
        // Se já existe um botão de logout, significa que o PHP já renderizou a página logada. Não faz nada.
        if (document.querySelector('.logout-btn')) {
            return;
        }

        const token = localStorage.getItem('authToken');
        if (token) {
            console.log("🔎 Encontrado token no localStorage, verificando...");
            // Redireciona para a URL com o token para verificação no backend
            window.location.href = `?verify_token=${encodeURIComponent(token)}`;
        }
    };

    // ALTERADO: Lógica de submit do formulário de login
    document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nome = document.getElementById('nome').value;
        const senha = document.getElementById('senha').value;
        const errorMessageDiv = document.getElementById('errorMessage');
        errorMessageDiv.textContent = ''; // Limpa erros antigos

        const formData = new FormData();
        formData.append('action', 'login'); // NOVO: envia uma ação para o PHP saber o que fazer
        formData.append('nome', nome);
        formData.append('senha', senha);

        try {
            const response = await fetch('', { // Envia para a própria página
                method: 'POST',
                body: formData
            });

            const data = await response.json(); // Espera uma resposta JSON

            if (data.success && data.token) {
                // SUCESSO! Salva o token e recarrega a página
                console.log("✅ Login bem-sucedido, token recebido:", data.token);
                localStorage.setItem('authToken', data.token);
                // ALTERADO: Redireciona para a URL base (limpa)
                window.location.href = window.location.pathname; 
            } else {
                // FALHA! Mostra a mensagem de erro
                errorMessageDiv.textContent = data.message || 'Ocorreu um erro.';
                console.error("❌ Falha no login:", data.message);
            }
        } catch (error) {
            errorMessageDiv.textContent = 'Erro de comunicação com o servidor.';
            console.error("💥 Erro no fetch:", error);
        }
    });

    function logout() {
        console.log("🚀 Saindo...");
        localStorage.removeItem('authToken');
        // Redireciona para a página limpa, sem o token na URL
        window.location.href = window.location.pathname;
    }

    function resetAll() {
        if (confirm('Tem certeza que quer resetar tudo?')) {
            localStorage.removeItem('authToken');
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