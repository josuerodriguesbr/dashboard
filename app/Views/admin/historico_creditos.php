<?php
// app/Views/admin/historico_creditos.php
?>
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">
            Histórico de Transações: <span class="text-indigo-600"><?= htmlspecialchars($usuario['nome']) ?></span>
        </h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <div>
                <span class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Saldo Atual</span>
                <div class="text-2xl font-bold text-gray-800 mt-1">
                    <?= number_format(App\Models\Usuario::getSaldo($usuario['id']), 0, ',', '.') ?>
                </div>
            </div>
            <div class="text-sm text-gray-400">
                Exibindo as últimas 100 transações
            </div>
        </div>

        <div class="tabela-container border-0 shadow-none m-0 rounded-none">
            <div class="tabela-scroll" style="max-height: 600px;">
                <table class="tabela-padrao">
                    <thead>
                        <tr>
                            <th class="col-pequena">Data/Hora</th>
                            <th class="col-media">Tipo</th>
                            <th class="col-grande">Descrição</th>
                            <th class="col-pequena">Valor</th>
                            <th class="col-pequena">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transacoes)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-500">
                                    Nenhuma transação encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transacoes as $t): ?>
                                <?php 
                                    $isEntrada = $t['multiplicador'] > 0;
                                    $classeValor = $isEntrada ? 'text-green-600' : 'text-red-600';
                                    $sinal = $isEntrada ? '+' : '-';
                                ?>
                                <tr>
                                    <td class="col-pequena text-sm text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($t['createdAt'])) ?>
                                    </td>
                                    <td class="col-media">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            <?= $isEntrada ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= htmlspecialchars($t['tipo_nome']) ?>
                                        </span>
                                    </td>
                                    <td class="col-grande text-sm text-gray-700">
                                        <?= htmlspecialchars($t['descricao']) ?>
                                    </td>
                                    <td class="col-pequena font-bold <?= $classeValor ?>">
                                        <?= $sinal ?><?= number_format($t['valor'], 0, ',', '.') ?>
                                    </td>
                                    <td class="col-pequena">
                                        <span class="text-xs text-gray-400 uppercase">
                                            <?= htmlspecialchars($t['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
