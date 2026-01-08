<!DOCTYPE html>
<html>
<head>
    <title>Cadastro - Convite</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (defined('CSS_PATH') && !empty(CSS_PATH)): ?>
        <link rel="stylesheet" href="<?= CSS_PATH ?>" />
    <?php else: ?>
        <link rel="stylesheet" href="/projetos/dashboard/public/css/style.css" />
    <?php endif; ?>
</head>
<body>
    <div class="container-pagina">

        <header>
            <div class="header-logo">
                <div class="logo-icon">✨</div>
                <div class="logo-text">Cadastro</div>
            </div>
        </header>
    
        <section>
            <h2 class="centro-horizontal">Finalizar Cadastro</h2>
            <div style="background-color: var(--cor-fundo-card); border: 1px solid var(--cor-destaque); border-radius: 8px; padding: 15px; margin: 20px auto; max-width: 400px; text-align: center;">
                <p style="margin: 0; color: var(--cor-texto-secundario); font-size: 0.9em;">Você foi convidado por</p>
                <strong style="font-size: 1.1em; color: var(--cor-destaque); display: block; margin: 5px 0;"><?= htmlspecialchars($nomeAnfitriao ?? 'Sistema') ?></strong>
                <p style="margin: 0; font-size: 0.85em; opacity: 0.8;">Para perfil: <strong><?= htmlspecialchars(ucfirst($tipoConvite ?? 'Usuário')) ?></strong></p>
            </div>
            <p class="centro-horizontal" style="margin-bottom: 20px;">Preencha seus dados para aceitar o convite.</p>
            
            <form id="cadastroForm" class="conteudo-centralizado">
                <input type="hidden" id="hash" value="<?= htmlspecialchars($hash ?? '') ?>">
                <input type="hidden" id="tipo" value="<?= htmlspecialchars($tipoConvite ?? 'cliente') ?>">
                
                <div class="campoDeTexto">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" required>
                </div>

                <div class="campoDeEmail">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" required>
                </div>
                
                <div class="campoDeTexto">
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" placeholder="000.000.000-00" required>
                </div>
                
                <div class="campoDeTexto">
                    <label for="telefone">Telefone:</label>
                    <input type="text" id="telefone" placeholder="(00) 00000-0000" required>
                </div>

                <div class="campoDeSenha">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" required>                
                </div>

                <div class="grade-botao">
                    <button type="submit" class="btn-primary" style="grid-column: span 3;">Cadastrar</button>
                </div>

                <div id="loading" class="mensagem-sistema" style="display:none; text-align:center; margin-top:10px;">Processando...</div>
                <div id="errorMessage" class="mensagem-erro oculta"></div>
            </form>

        </section>
        
         <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>         
        <div id="feedback-container" class="feedback-container"></div>
    </div>

    <script type="module">
    import { mostrarNotificacao, formatCPF, somenteDigitos } from '/projetos/dashboard/public/js/funcoes.js';

    // Máscara de Telefone Simples (XX) XXXXX-XXXX
    function formatTelefone(v) {
        v = v.replace(/\D/g,"");             //Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
        v = v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
        return v;
    }

    const telefoneEl = document.getElementById('telefone');
    telefoneEl.addEventListener('input', function() {
        this.value = formatTelefone(this.value);
    });
    
    // Máscara de CPF usando a função importada (adaptada para input event)
    const cpfEl = document.getElementById('cpf');
    cpfEl.addEventListener('input', function() {
        this.value = formatCPF(this.value);
    });

    document.getElementById('cadastroForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button');
        const loading = document.getElementById('loading');
        // const errorMsg = document.getElementById('errorMessage'); // Removido para usar notificação
        
        btn.disabled = true;
        loading.style.display = 'block';
        
        const data = {
            hash: document.getElementById('hash').value,
            tipo: document.getElementById('tipo').value,
            nome: document.getElementById('nome').value,
            email: document.getElementById('email').value,
            cpf: document.getElementById('cpf').value,
            telefone: document.getElementById('telefone').value,
            senha: document.getElementById('senha').value
        };

        try {
            const response = await fetch('/projetos/dashboard/cadastro-via-convite', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                mostrarNotificacao('Cadastro realizado com sucesso! Redirecionando...', 'sucesso');
                setTimeout(() => {
                    window.location.href = result.redirect || '/projetos/dashboard/home';
                }, 2000);
            } else {
                mostrarNotificacao(result.message || 'Erro ao cadastrar.', 'erro');
            }
        } catch (error) {
            console.error(error);
            mostrarNotificacao('Erro de conexão.', 'erro');
        } finally {
            btn.disabled = false;
            loading.style.display = 'none';
        }
    });
    </script>
</body>
</html>
