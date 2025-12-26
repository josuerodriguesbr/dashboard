// Exemplo de uso da função de notificação no dashboard admin

// Importar a função de notificação do módulo de funções
import { mostrarNotificacao } from '/projetos/dashboard/public/js/funcoes.js';

// Exemplo de como usar a função de notificação em diferentes situações
document.addEventListener('DOMContentLoaded', function() {
    // Exemplo: Exibir uma notificação de boas-vindas
    mostrarNotificacao('Bem-vindo ao painel de administração!', 'info', 5000);
    
    // Exemplo de notificação de sucesso
    // mostrarNotificacao('Operação realizada com sucesso!', 'sucesso', 3000);
    
    // Exemplo de notificação de erro
    // mostrarNotificacao('Ocorreu um erro ao processar a solicitação.', 'erro', 5000);
    
    // Exemplo de notificação de alerta
    // mostrarNotificacao('Atenção: Verifique as informações antes de continuar.', 'alerta', 4000);
    
    // Você pode chamar a função mostrarNotificacao em qualquer parte do código
    // quando precisar notificar o usuário sobre algo
    
    // Exemplo de uso em uma ação de formulário
    /*
    const form = document.getElementById('meuFormulario');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simular processamento
            // ...
            
            // Mostrar notificação de sucesso
            mostrarNotificacao('Dados salvos com sucesso!', 'sucesso', 4000);
        });
    }
    */
});