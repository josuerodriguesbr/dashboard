<?php
// app/Views/admin/gerenciar_creditos.php
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Gerenciamento de Créditos</h1>

    <div class="barra-ferramentas">
        <div class="grupo-filtros">
            <!-- Filtro de Texto (Nome/Email/ID) -->
            <div class="campoDeTexto w-full">
                <input type="text" id="filtroTexto" placeholder="Buscar por Nome, Email ou ID..." onkeyup="filtrarTabela()">
            </div>
            <!-- Filtro de Nível (Exemplo de filtro extra solicitada) -->
            <div class="campoDeOpcoes w-48">
                <select id="filtroNivel" onchange="filtrarTabela()">
                    <option value="">Todos os Níveis</option>
                    <option value="admin">Administrador</option>
                    <option value="assinante">Assinante</option>
                    <option value="operador">Operador</option>
                </select>
            </div>
        </div>
        <!-- Botão de Ação Global (ex: Exportar) poderia vir aqui -->
    </div>

    <div class="lista-container" id="listaUsuarios">
        <?php foreach ($usuarios as $u): ?>
            <div class="cartao-padrao" data-nivel="<?= htmlspecialchars($u['papel_nivel'] ?? '') ?>">
                <!-- Conteúdo Principal -->
                <div class="cartao-conteudo">
                    
                    <div class="cartao-info-grupo" style="flex: 0 0 50px;">
                        <span class="cartao-label">ID</span>
                        <span class="cartao-valor text-gray-500">#<?= $u['id'] ?></span>
                    </div>

                    <div class="cartao-info-grupo" style="flex: 2;">
                        <span class="cartao-label">Usuário</span>
                        <div class="flex flex-col">
                            <span class="cartao-valor font-medium"><?= htmlspecialchars($u['nome']) ?></span>
                            <span class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></span>
                        </div>
                    </div>

                    <div class="cartao-info-grupo">
                        <span class="cartao-label">Nível</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 w-max">
                            <?= htmlspecialchars(ucfirst($u['papel_nivel'] ?? '')) ?>
                        </span>
                    </div>

                    <div class="cartao-info-grupo">
                        <span class="cartao-label">Saldo Atual</span>
                        <span class="cartao-valor destaque text-indigo-600">
                            <?= number_format($u['saldo_atual'], 0, ',', '.') ?>
                        </span>
                    </div>

                </div>

                <!-- Ações -->
                <div class="cartao-acoes">
                    <button onclick="abrirModal(<?= $u['id'] ?>, '<?= $u['nome'] ?>')" 
                            class="btn btn-primary w-full text-center justify-center">
                        Ajustar Saldo
                    </button>
                    <!-- Botão de Histórico -->
                    <a href="<?= getBaseUrl() ?>/admin/historico-creditos/<?= $u['id'] ?>" 
                       class="btn btn-secondary w-full text-center justify-center no-underline hover:bg-gray-50">
                       Ver Histórico
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="px-2 py-3 text-xs text-gray-500 mt-2" id="contadorRegistros">
        Mostrando <?= count($usuarios) ?> usuários
    </div>
</div>

<!-- Modal de Ajuste (Mantido igual) -->
<div id="modalAjuste" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="relative bg-white rounded-lg shadow-xl w-96 max-w-full m-4 p-6">
        
        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center" id="modalTitle">Ajustar Saldo</h3>
        
        <form id="formAjuste" class="layout-coluna">
            <input type="hidden" id="usuario_id" name="usuario_id">
            
            <div class="campoDeOpcoes">
                <label for="tipo_operacao">Operação</label>
                <select id="tipo_operacao" name="tipo_operacao">
                     <option value="credito">Adicionar Créditos (+)</option>
                     <option value="debito">Remover Créditos (-)</option>
                </select>
            </div>

            <div class="campoDeTexto">
                <label for="valor">Valor</label>
                <input id="valor" name="valor" type="number" min="1" placeholder="Ex: 100">
            </div>

            <div class="campoDeMensagem">
                <label for="descricao" style="font-weight: 500; color: var(--cor-texto);">Motivo</label>
                <textarea id="descricao" name="descricao" rows="2" placeholder="Ex: Pagamento confirmado via Pix"></textarea>
            </div>
        </form>

        <div class="flex gap-4 mt-6">
            <button onclick="fecharModal()" class="btn btn-secondary flex-1 justify-center">
                Cancelar
            </button>
            <button id="btnSalvar" class="btn btn-primary flex-1 justify-center">
                Confirmar
            </button>
        </div>

    </div>
</div>

<script>
function filtrarTabela() {
    const termoTexto = document.getElementById("filtroTexto").value.toUpperCase();
    const filtroNivel = document.getElementById("filtroNivel").value.toUpperCase();
    
    const container = document.getElementById("listaUsuarios");
    const cards = container.getElementsByClassName("cartao-padrao");
    let contagem = 0;

    for (let i = 0; i < cards.length; i++) {
        const card = cards[i];
        let mostrar = true;

        // Filtro de Texto (Buscando em todo o conteúdo textual do cartão)
        const textoCartao = card.innerText.toUpperCase();
        
        if (termoTexto && textoCartao.indexOf(termoTexto) === -1) {
            mostrar = false;
        }

        // Filtro de Nível
        if (mostrar && filtroNivel) {
            const nivelCard = card.getAttribute('data-nivel') ? card.getAttribute('data-nivel').toUpperCase() : '';
            if (nivelCard !== filtroNivel) {
                 mostrar = false;
            }
        }

        if (mostrar) {
            card.style.display = "grid"; // Grid para manter layout, ou flex se mudado
            contagem++;
        } else {
            card.style.display = "none";
        }
    }
    
    // Atualiza contador
    document.getElementById('contadorRegistros').textContent = `Mostrando ${contagem} usuários`;
}

function abrirModal(id, nome) {
    document.getElementById('usuario_id').value = id;
    document.getElementById('modalTitle').innerText = `Ajustar Saldo: ${nome}`;
    document.getElementById('modalAjuste').classList.remove('hidden');
    document.getElementById('modalAjuste').classList.add('flex');
}

function fecharModal() {
    document.getElementById('modalAjuste').classList.add('hidden');
    document.getElementById('modalAjuste').classList.remove('flex');
    document.getElementById('formAjuste').reset();
}

document.getElementById('btnSalvar').onclick = function() {
    const btn = this;
    const originalText = btn.innerText;
    btn.innerText = 'Salvando...';
    btn.disabled = true;

    const formData = new FormData(document.getElementById('formAjuste'));
    
    fetch('<?= getBaseUrl() ?>/admin/creditos/adicionar', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
            btn.innerText = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar.');
        btn.innerText = originalText;
        btn.disabled = false;
    });
};
</script>
