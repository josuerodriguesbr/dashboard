<!-- app/Views/vendedor/dashboard.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Área do Vendedor</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h1>Área do Vendedor</h1>
        <p>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!</p>
        
        <div class="vendas">
            <h2>Minhas Vendas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($minhasVendas as $venda): ?>
                        <tr>
                            <td><?= $venda['id'] ?></td>
                            <td><?= htmlspecialchars($venda['cliente']) ?></td>
                            <td>R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($venda['status']) ?></td>
                            <td><?= $venda['createdAt'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <a href="/logout">Sair</a>
    </div>
</body>
</html>