<?php
// app/Views/creditos/historico.php
?>
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-8">
        <a href="/creditos" class="text-gray-500 hover:text-gray-700 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Histórico de Transações</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($transacoes)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Nenhuma transação encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transacoes as $t): ?>
                        <?php 
                            $isPositive = $t['multiplicador'] > 0;
                            $colorClass = $isPositive ? 'text-green-600' : 'text-red-600';
                            $sign = $isPositive ? '+' : '-';
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($t['createdAt'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $t['tipo_nome'] ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($t['descricao']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold <?= $colorClass ?>">
                                <?= $sign ?> <?= number_format($t['valor'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
