<!DOCTYPE html>
<html>
<head>
    <title>Cadastro - Convite</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        /* Estilos Embedados (Dark Mode Forçado) */
        :root {
            --cor-fundo: #0f172a; /* slate-900 */
            --cor-texto: #f1f5f9; /* slate-100 */
            --cor-texto-secundario: #94a3b8; /* slate-400 */
            --cor-borda: #334155; /* slate-700 */
            --cor-fundo-input: #1e293b; /* slate-800 */
            --cor-fundo-card: #1e293b;
            --cor-botao: #60a5fa; /* blue-400 */
            --cor-botao-hover: #3b82f6; /* blue-500 */
            --cor-erro: #f87171; /* red-400 */
            --cor-sucesso: #34d399; /* emerald-400 */
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--cor-fundo);
            color: var(--cor-texto);
            font-family: 'Roboto', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            padding: 0;
            transition: background-color 0.3s;
        }

        .container-pagina {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 60px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            height: 60px;
            width: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            background: #1e293b; /* Dark specific header bg */
            color: white;
            padding: 0 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--cor-borda);
        }

        .header-logo { display: flex; align-items: center; gap: 10px; }
        .logo-icon { font-size: 24px; color: var(--cor-botao); }
        .logo-text { font-size: 20px; font-weight: 500; }

        section { padding: 1rem; }
        .centro-horizontal { text-align: center; }
        .conteudo-centralizado { max-width: 480px; margin: 0 auto; padding: 1.5rem; }

        /* Formulários */
        .campoDeTexto, .campoDeEmail, .campoDeSenha, .campoDeCPF, .campoDeTelefone {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        label { font-weight: 500; color: var(--cor-texto); }
        input {
            padding: 0.75rem;
            border: 1px solid var(--cor-borda);
            border-radius: 0.375rem;
            background-color: var(--cor-fundo-input);
            color: var(--cor-texto);
            font-size: 1rem;
            width: 100%;
        }
        input:focus {
            outline: none;
            border-color: var(--cor-botao);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.3);
        }

        .btn-primary {
            background-color: var(--cor-botao);
            color: white; /* Sempre branco no botão */
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
            width: 100%;
            font-size: 1rem;
        }
        .btn-primary:hover { background-color: var(--cor-botao-hover); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

        .grade-botao { margin-top: 1rem; }

        footer {
            margin-top: auto;
            padding: 1rem;
            background-color: var(--cor-fundo-card);
            text-align: center;
            font-size: 0.875rem;
            color: var(--cor-texto-secundario);
            border-top: 1px solid var(--cor-borda);
        }

        /* Notificações */
        .feedback-container {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 9999;
            width: 320px;
            max-width: calc(100vw - 40px);
        }
        .feedback-mensagem {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto);
            transform: translateX(150%);
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            border-left: 4px solid transparent;
        }
        .feedback-sucesso { border-left-color: var(--cor-sucesso); }
        .feedback-erro { border-left-color: var(--cor-erro); }
        .feedback-conteudo { flex: 1; font-size: 0.9rem; }
        .feedback-fechar {
            background: none; border: none; color: var(--cor-texto-secundario);
            font-size: 1.25rem; cursor: pointer; margin-left: 0.5rem;
        }
        .feedback-fechar:hover { color: var(--cor-texto); }
        .oculta { display: none !important; }

        /* Card de Convite */
        .card-info {
            background-color: var(--cor-fundo-card);
            border: 1px solid var(--cor-botao);
            border-radius: 8px;
            padding: 15px;
            margin: 20px auto;
            max-width: 400px;
            text-align: center;
        }
    </style>
    <script>window.BASE_PATH = "<?= getBasePath() ?>";</script>
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
            <h2 class="centro-horizontal" style="margin-bottom: 1rem;">Finalizar Cadastro</h2>
            <div class="card-info">
                <p style="margin: 0; color: var(--cor-texto-secundario); font-size: 0.9em;">Você foi convidado por</p>
                <strong style="font-size: 1.2em; color: var(--cor-botao); display: block; margin: 5px 0;"><?= htmlspecialchars($nomeAnfitriao ?? 'Sistema') ?></strong>
                <p style="margin: 0; font-size: 0.85em; opacity: 0.8;">Para perfil: <strong><?= htmlspecialchars(ucfirst($tipoConvite ?? 'Usuário')) ?></strong></p>
            </div>
            <p class="centro-horizontal" style="margin-bottom: 20px; color: var(--cor-texto-secundario);">Preencha seus dados para aceitar o convite.</p>
            
            <form id="cadastroForm" class="conteudo-centralizado">
                <input type="hidden" id="hash" value="<?= htmlspecialchars($hash ?? '') ?>">
                <input type="hidden" id="tipo" value="<?= htmlspecialchars($tipoConvite ?? 'cliente') ?>">
                
                <div class="campoDeTexto">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" required placeholder="Seu nome completo">
                </div>

                <div class="campoDeEmail">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" required placeholder="seu@email.com">
                </div>
                
                <div class="campoDeCPF">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" placeholder="000.000.000-00" required maxlength="14">
                </div>
                
                <div class="campoDeTelefone">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" placeholder="(00) 00000-0000" required maxlength="15">
                </div>

                <div class="campoDeSenha">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" required placeholder="Crie uma senha segura">                
                </div>

                <div class="grade-botao">
                    <button type="submit" class="btn-primary">Cadastrar</button>
                </div>

                <div id="loading" style="display:none; text-align:center; margin-top:10px; color: var(--cor-texto-secundario);">Processando...</div>
            </form>

        </section>
        
         <footer>
            &copy; <?= date('Y') ?> - Sistema de Integração
        </footer>         
        <div id="feedback-container" class="feedback-container"></div>
    </div>

    <script>
    // --- Funções Embutidas para Evitar Erros de Importação ---

    function mostrarNotificacao(mensagem, tipo = 'info', duracao = 5000) {
        const container = document.getElementById('feedback-container');
        if (!container) return;

        const notificacao = document.createElement('div');
        notificacao.className = `feedback-mensagem feedback-${tipo}`;
        notificacao.innerHTML = `
            <div class="feedback-conteudo">${mensagem}</div>
            <button class="feedback-fechar" title="Fechar">×</button>
        `;

        notificacao.querySelector('.feedback-fechar').addEventListener('click', () => {
             fecharNotificacao(notificacao);
        });

        container.appendChild(notificacao);

        // Animation Frame para garantir transição
        requestAnimationFrame(() => {
            notificacao.style.transform = 'translateX(0)';
            notificacao.style.opacity = '1';
        });

        if (duracao > 0) {
            setTimeout(() => {
                fecharNotificacao(notificacao);
            }, duracao);
        }

        function fecharNotificacao(el) {
            el.style.transform = 'translateX(150%)';
            el.style.opacity = '0';
            setTimeout(() => {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 300);
        }
    }

    function formatCPF(v) {
        v = v.replace(/\D/g,""); 
        v = v.replace(/(\d{3})(\d)/,"$1.$2");
        v = v.replace(/(\d{3})(\d)/,"$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
        return v;
    }

    function formatTelefone(v) {
        v = v.replace(/\D/g,"");
        v = v.replace(/^(\d{2})(\d)/g,"($1) $2");
        v = v.replace(/(\d)(\d{4})$/,"$1-$2");
        return v;
    }

    // --- Lógica da Página ---

    document.addEventListener('DOMContentLoaded', () => {
        const cpfEl = document.getElementById('cpf');
        const telefoneEl = document.getElementById('telefone');

        if(cpfEl) {
            cpfEl.addEventListener('input', function() {
                this.value = formatCPF(this.value);
            });
        }

        if(telefoneEl) {
            telefoneEl.addEventListener('input', function() {
                this.value = formatTelefone(this.value);
            });
        }

        document.getElementById('cadastroForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button');
            const loading = document.getElementById('loading');
            
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
                // Usando window.BASE_PATH garantido pelo PHP
                const basePath = window.BASE_PATH || '';
                const response = await fetch(basePath + '/cadastro-via-convite', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    mostrarNotificacao('Cadastro realizado com sucesso! Redirecionando...', 'sucesso');
                    setTimeout(() => {
                        window.location.href = result.redirect || (basePath + '/home');
                    }, 2000);
                } else {
                    mostrarNotificacao(result.message || 'Erro ao cadastrar.', 'erro');
                    btn.disabled = false;
                    loading.style.display = 'none';
                }
            } catch (error) {
                console.error(error);
                mostrarNotificacao('Erro de conexão.', 'erro');
                btn.disabled = false;
                loading.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
