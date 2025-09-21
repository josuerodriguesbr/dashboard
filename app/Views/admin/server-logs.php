<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Logs PHP</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Courier New', monospace;
      background: #0f1620;
      color: #e0e0e0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      padding: 0;
      margin: 0;
    }
    header {
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: center;
      padding: 15px;
      background: #1a2733;
      color: #4ecdc4;
      font-weight: bold;
      font-size: 18px;
      border-bottom: 2px solid #4ecdc4;
    }
    .log-container {
      flex: 1;
      overflow-y: auto;
      padding: 10px;
    }
    .log {
      padding: 10px 12px;
      border-left: 4px solid #88d498;
      margin: 6px 0;
      font-size: 14px;
      line-height: 1.5;
      word-wrap: break-word;
    }
    .info    { color: #88d498; border-left-color: #88d498; }
    .success { color: #4ecdc4; border-left-color: #4ecdc4; }
    .error   { color: #ff6b6b; border-left-color: #ff6b6b; }
    .warning { color: #feca57; border-left-color: #feca57; }
    .access  { color: #a29bfe; border-left-color: #a29bfe; }
  </style>
</head>
<body>
  <header>🖥️ Servidor PHP</header>
  <div class="log-container" id="log-container">
    <div class="log info">[Carregando...] Conectando ao servidor...</div>
  </div>

  <script>
    // Mapeia ações para classes de estilo
    function getTipo(acao) {
      if (acao.includes('erro') || acao.includes('falhou')) return 'error';
      if (acao.includes('sucesso') || acao.includes('criado') || acao.includes('liberado')) return 'success';
      if (acao.includes('Acesso')) return 'access';
      if (acao.includes('Webhook')) return 'warning';
      return 'info';
    }

    async function carregarLogs() {
      try {
        const res = await fetch('logs');
        if (!res.ok) throw new Error('Falha HTTP: ' + res.status);

        const logs = await res.json();
        const container = document.getElementById('log-container');

        if (!logs || logs.length === 0) {
          container.innerHTML = '<div class="log info">[INFO] Nenhum log registrado ainda.</div>';
          return;
        }

        // Ordena do mais novo para o mais antigo
        //logs.reverse();

        container.innerHTML = logs.map(log => {
          const data = new Date(log.createdAt).toLocaleString('pt-BR');
          const usuario = log.nomeUsuario ? log.nomeUsuario : 'Sistema';
          const acao = log.acao;
          const tipo = getTipo(acao.toLowerCase());

          return `<div class="log ${tipo}">[${data}] [${usuario}] ${acao}</div>`;
        }).join('');
      } catch (e) {
        console.error('Erro ao carregar logs:', e);
        const container = document.getElementById('log-container');
        container.innerHTML = '<div class="log error">[ERRO] Falha ao carregar logs do servidor.</div>';
      }
    }

    // Carrega imediatamente
    carregarLogs();

    // Atualiza a cada 10 segundos
    setInterval(carregarLogs, 10000);
  </script>
</body>
</html>