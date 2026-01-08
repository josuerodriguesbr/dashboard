<?php
// app/Views/creditos/index.php
?>
<div class="layout-coluna">
    <div class="layout-grade">
        <!-- Card Saldo -->
        <div class="card" style="background-color: var(--cor-fundo-card); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--cor-borda);">
            <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">Sua Carteira</h2>
            <div style="font-size: 2.5rem; font-weight: bold; color: var(--cor-sucesso);">
                <?= number_format($saldo, 0, ',', '.') ?> <small style="font-size: 1rem; color: var(--cor-texto-secundario);">créditos</small>
            </div>
            <button onclick="abrirModalRecarga()" class="btn-primary" style="margin-top: 1rem;">
                Recarregar Carteira
            </button>
        </div>

        <!-- Card Meus Itens -->
        <div class="card" style="background-color: var(--cor-fundo-card); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--cor-borda);">
            <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">Seus Itens</h2>
            <?php if (empty($combosAtivos)): ?>
                <p style="color: var(--cor-texto-secundario);">Você não possui itens ativos.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0;">
                <?php foreach ($combosAtivos as $combo): ?>
                    <li style="border-bottom: 1px solid var(--cor-borda); padding: 0.5rem 0; display: flex; justify-content: space-between;">
                        <span><?= htmlspecialchars($combo['nome_exibicao'] ?? 'Item') ?></span>
                        <span style="background-color: rgba(52, 211, 153, 0.2); color: var(--cor-sucesso); padding: 2px 8px; border-radius: 10px; font-size: 0.8em;">Ativo</span>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recursos Disponíveis -->
    <h2 style="font-size: 1.5rem; font-weight: bold; margin: 1rem 0;">Recursos Disponíveis</h2>
    <div class="layout-grade">
        <?php foreach ($combosDisponiveis as $combo): ?>
            <div class="card" style="background-color: var(--cor-fundo-card); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--cor-borda); display: flex; flex-direction: column; align-items: center; text-align: center;">
                <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;"><?= htmlspecialchars($combo['nome']) ?></h3>
                <p style="color: var(--cor-texto-secundario); margin-bottom: 1.5rem; flex-grow: 1;"><?= htmlspecialchars($combo['descricao']) ?></p>
                
                <div style="margin-bottom: 1.5rem;">
                    <span style="font-size: 2rem; font-weight: bold; color: var(--cor-botao);"><?= number_format($combo['preco_creditos'], 0, ',', '.') ?></span>
                    <span style="color: var(--cor-texto-secundario);">créditos</span>
                </div>
                
                <button onclick="comprarCombo(<?= $combo['id'] ?>, '<?= $combo['nome'] ?>', <?= $combo['preco_creditos'] ?>)" class="btn-primary">
                    Comprar Agora
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Histórico -->
    <div style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="font-size: 1.5rem; font-weight: bold;">Últimas Transações</h2>
            <a href="/projetos/dashboard/creditos/historico" style="color: var(--cor-botao); text-decoration: none;">Ver completo &rarr;</a>
        </div>
        
        <div style="background-color: var(--cor-fundo-card); border-radius: 0.5rem; overflow: hidden; border: 1px solid var(--cor-borda);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: rgba(0,0,0,0.2);">
                    <tr>
                        <th style="padding: 1rem; text-align: left; color: var(--cor-texto-secundario);">Data</th>
                        <th style="padding: 1rem; text-align: left; color: var(--cor-texto-secundario);">Tipo</th>
                        <th style="padding: 1rem; text-align: left; color: var(--cor-texto-secundario);">Descrição</th>
                        <th style="padding: 1rem; text-align: right; color: var(--cor-texto-secundario);">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimasTransacoes)): ?>
                        <tr>
                            <td colspan="4" style="padding: 2rem; text-align: center; color: var(--cor-texto-secundario);">Nenhuma transação recente.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimasTransacoes as $t): ?>
                            <?php 
                                $isPositive = $t['multiplicador'] > 0;
                                $color = $isPositive ? 'var(--cor-sucesso)' : 'var(--cor-erro)';
                                $sign = $isPositive ? '+' : '-';
                            ?>
                            <tr style="border-top: 1px solid var(--cor-borda);">
                                <td style="padding: 1rem;"><?= date('d/m/Y H:i', strtotime($t['createdAt'])) ?></td>
                                <td style="padding: 1rem; font-weight: 500;"><?= $t['tipo_nome'] ?></td>
                                <td style="padding: 1rem; color: var(--cor-texto-secundario);"><?= htmlspecialchars($t['descricao']) ?></td>
                                <td style="padding: 1rem; text-align: right; font-weight: bold; color: <?= $color ?>">
                                    <?= $sign ?> <?= number_format($t['valor_nominal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Recarga -->
<div id="modalRecarga" class="oculta" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
    <div style="background: var(--cor-fundo-card); padding: 2rem; border-radius: 0.5rem; width: 100%; max-width: 400px; position: relative; border: 1px solid var(--cor-borda);">
        <button onclick="fecharModalRecarga()" class="btn-icon" style="position: absolute; top: 1rem; right: 1rem;">&times;</button>
        
        <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Recarregar Carteira</h2>
        
        <div id="step-amount">
            <p style="margin-bottom: 1rem; color: var(--cor-texto-secundario);">Escolha o valor da recarga (Mínimo R$ 5,00):</p>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <button onclick="setValor(10)" class="btn-secondary">R$ 10</button>
                <button onclick="setValor(20)" class="btn-secondary">R$ 20</button>
                <button onclick="setValor(50)" class="btn-secondary">R$ 50</button>
            </div>
            
            <div class="campoDeTexto">
                <label>Outro Valor (R$)</label>
                <input type="number" id="valorRecarga" placeholder="0.00">
            </div>
            
            <button onclick="gerarPix()" id="btnGerarPix" class="btn-primary" style="margin-top: 1rem;">
                Gerar Pix
            </button>
        </div>

        <div id="step-pix" class="oculta" style="text-align: center;">
            <p style="color: var(--cor-sucesso); font-weight: bold; margin-bottom: 1rem;">Cobrança Gerada com Sucesso!</p>
            <div style="display: flex; justify-content: center; margin-bottom: 1rem; background: white; padding: 10px; border-radius: 8px;">
                <img id="imgQrCode" src="" alt="QR Code Pix" style="max-height: 200px;">
            </div>
            <p style="font-size: 0.875rem; color: var(--cor-texto-secundario); margin-bottom: 0.5rem;">Copia e Cola:</p>
            <textarea id="pixCopyPaste" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; background: var(--cor-fundo-input); color: var(--cor-texto); border: 1px solid var(--cor-borda); border-radius: 0.25rem;" rows="3" readonly></textarea>
            <button onclick="copiarPix()" class="btn-secondary" style="width: 100%;">
                Copiar Código
            </button>
        </div>
    </div>
</div>

<script>
function abrirModalRecarga() {
    const modal = document.getElementById('modalRecarga');
    modal.classList.remove('oculta');
    // Forçar display flex via style, pois a classe 'oculta' usa !important display:none
    modal.style.display = 'flex';
    
    document.getElementById('step-amount').classList.remove('oculta');
    document.getElementById('step-pix').classList.add('oculta');
}

function fecharModalRecarga() {
    const modal = document.getElementById('modalRecarga');
    modal.classList.add('oculta');
    modal.style.display = 'none';
}

function setValor(v) {
    document.getElementById('valorRecarga').value = v;
}

function copiarPix() {
    const copyText = document.getElementById("pixCopyPaste");
    copyText.select();
    document.execCommand("copy");
    mostrarNotificacao("Código Pix copiado!", "sucesso");
}

function gerarPix() {
    const valor = document.getElementById('valorRecarga').value;
    const btn = document.getElementById('btnGerarPix');
    
    if (!valor || valor < 5) {
        mostrarNotificacao('Valor mínimo de R$ 5,00', 'erro');
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Gerando...';

    fetch('/projetos/dashboard/creditos/recarga', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ valor: parseFloat(valor) })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = 'Gerar Pix';
        
        if (data.success) {
            document.getElementById('step-amount').classList.add('oculta');
            document.getElementById('step-pix').classList.remove('oculta');
            
            document.getElementById('imgQrCode').src = 'data:image/png;base64,' + data.pixQrCode;
            document.getElementById('pixCopyPaste').value = data.pixCopyPaste;
        } else {
            mostrarNotificacao('Erro: ' + data.message, 'erro');
        }
    })
    .catch(error => {
        console.error(error);
        btn.disabled = false;
        btn.innerText = 'Gerar Pix';
        mostrarNotificacao('Erro ao comunicar com servidor', 'erro');
    });
}

function comprarCombo(id, nome, preco) {
    if (!confirm(`Confirmar compra do combo "${nome}" por ${preco} créditos?`)) return;

    fetch('<?= getBaseUrl() ?>/creditos/comprar/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacao('Compra realizada com sucesso!', 'sucesso');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            mostrarNotificacao('Erro: ' + data.message, 'erro');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarNotificacao('Erro ao processar a compra.', 'erro');
    });
}
</script>
