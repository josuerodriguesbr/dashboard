<!-- app/Views/cliente/dashboard.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Área do Cliente</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h1>Área do Cliente</h1>
        <p>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!</p>
        
        <p>Você está logado como cliente.</p>
        
        <a href="/logout">Sair</a>
    </div>
</body>
</html>