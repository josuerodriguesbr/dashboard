<!-- app/Views/assinante/dashboard.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Área do Assinante</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h1>Área do Assinante</h1>
        <p>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!</p>
        
        <div class="perfil">
            <h2>Meus Dados</h2>
            <p>Nome: <?= htmlspecialchars($meusDados['nome']) ?></p>
            <p>Email: <?= htmlspecialchars($meusDados['email']) ?></p>
            <p>Telefone: <?= htmlspecialchars($meusDados['telefone']) ?></p>
        </div>
        
        <a href="/logout">Sair</a>
    </div>
</body>
</html>