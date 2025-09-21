<!-- /dashboard/app/Views/recursos/atualizar-logs.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Atualizar Logs</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f8f9fa;
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
      background: #4a6fa5;
      color: white;
      font-weight: bold;
      font-size: 18px;
      border-radius: 8px 8px 0 0;
    }
    .content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
    .btn {
      padding: 10px 20px;
      background: #55efc4;
      color: #2d3436;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .status {
      margin-top: 10px;
      color: #27ae60;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <header>🔄 Atualizar Logs</header>
  <div class="content">
    <h3>Controle de Logs</h3>
    <button class="btn" id="btnAtualizar">Atualizar Logs do Servidor</button>
    <div class="status" id="status">Pronto</div>
  </div>

  <script>
    document.getElementById('btnAtualizar').addEventListener('click', async () => {
      const status = document.getElementById('status');
      status.textContent = 'Atualizando...';

      try {
        const iframe = parent.parent.document.querySelector('iframe[src="server-logs"]');
        if (iframe && typeof iframe.contentWindow.carregarLogs === 'function') {
          iframe.contentWindow.carregarLogs();
        }
        status.textContent = '✅ Atualizado!';
      } catch (e) {
        status.textContent = '❌ Erro';
        console.error(e);
      }

      setTimeout(() => { status.textContent = 'Pronto'; }, 2000);
    });
  </script>
</body>
</html>