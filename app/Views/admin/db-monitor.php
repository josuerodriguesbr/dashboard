<?php
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Banco MySQL</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #1e1e2f;
      color: #ffffff;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: center;
      padding: 15px;
      background: #3a3a5c;
      color: #a29bfe;
      font-weight: bold;
      font-size: 18px;
      border-bottom: 2px solid #a29bfe;
    }
    .tables-container {
      flex: 1;
      overflow-y: auto;
      padding: 10px;
    }
    .table {
      margin-bottom: 20px;
    }
    .table h3 {
      padding: 10px;
      background: #2d2d44;
      color: #66d9ef;
      font-size: 16px;
      border-left: 4px solid #66d9ef;
    }
    .record {
      padding: 8px 12px;
      background: #2c2c3c;
      margin: 4px 0;
      border-left: 4px solid #66d9ef;
      font-size: 14px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <header>🗄️ Banco MySQL</header>
  <div class="tables-container">
    <div class="table">
      <h3>Clientes (últimos 3)</h3>
      <div class="record">ID: 105 | João Silva | CPF: 123.456.789-09</div>
      <div class="record">ID: 104 | Maria Oliveira | CPF: 987.654.321-00</div>
      <div class="record">ID: 103 | Carlos Mendes | CPF: 111.222.333-44</div>
    </div>

    <div class="table">
      <h3>Créditos (últimos 3)</h3>
      <div class="record">Cliente: 105 | Valor: R$ 50,00 | Data: 2025-08-18</div>
      <div class="record">Cliente: 104 | Valor: R$ 30,00 | Data: 2025-08-17</div>
      <div class="record">Cliente: 103 | Valor: R$ 100,00 | Data: 2025-08-16</div>
    </div>

    <div class="table">
      <h3>Pedidos (últimos 3)</h3>
      <div class="record">ID: 1023 | Cliente: 105 | Valor: R$ 45,00 | Status: Liberado</div>
      <div class="record">ID: 1022 | Cliente: 104 | Valor: R$ 25,00 | Status: Pendente</div>
      <div class="record">ID: 1021 | Cliente: 103 | Valor: R$ 80,00 | Status: Liberado</div>
    </div>

    <?php for ($i = 1; $i <= 10; $i++): ?>
      <div class="table">
        <h3>Pedidos (cópia para teste <?= $i ?>)</h3>
        <div class="record">ID: <?= 2000 + $i ?> | Cliente: 105 | Valor: R$ 20,00 | Status: Liberado</div>
      </div>
    <?php endfor; ?>
  </div>
</body>
</html>