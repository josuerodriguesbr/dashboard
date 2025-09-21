<!-- /dashboard/app/Views/frontend.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Playground - Asaas</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Courier New', monospace;
      background: #d3d3d3;
      color: #e0e0e0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      padding: 0;
      margin: 0;
    }

    /* Cabeçalho Fixo no Topo */
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

    /* Container de rolagem para os recursos */
    .resources-container {
      flex: 1;
      overflow-y: auto;
      padding: 10px;
    }
    iframe {
      width: 100%;
      height: 320px;
      border: none;
      display: block;
      margin-bottom: 15px;
    }
    .carregando {
      text-align: center;
      padding: 20px;
      color: #aaa;
      font-style: italic;
    }
  </style>
</head>
<body>
  <header>🔧 Playground de Integração</header>
  <div class="resources-container">
    
    <!-- Seção: Atualizar Logs -->
    <div class="carregando">Carregando...</div>
    <iframe src="app/Views/recursos/atualizar-logs.php" title="Atualizar Logs" 
            onload="this.style.display='block'; this.previousElementSibling.textContent='';">
    </iframe>

    <!-- Seção: Cadastrar Usuário -->
    <div class="carregando">Carregando...</div>
    <iframe src="app/Views/recursos/cadastrar-usuario.php" title="Cadastrar Usuário"
            onload="this.style.display='block'; this.previousElementSibling.textContent='';">
    </iframe>

  </div>
</body>
</html>