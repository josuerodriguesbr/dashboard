//public/tema.js
// Gerencia o toggle entre tema claro e escuro com persistência no localStorage

(function () {
  'use strict';

  // Verifica e aplica o tema salvo ao carregar a página
  function aplicarTemaSalvo() {
    const temaSalvo = localStorage.getItem('tema');
    if (temaSalvo === 'escuro') {
      document.documentElement.classList.add('tema-escuro');
    }
  }

  // Atualiza o ícone e título do botão conforme o tema ativo
  function atualizarBotaoTema(botao) {
    const estaEscuro = document.documentElement.classList.contains('tema-escuro');
    botao.textContent = estaEscuro ? '☀️' : '🌙';
    botao.title = estaEscuro ? 'Mudar para tema claro' : 'Mudar para tema escuro';
  }

  // Alterna o tema e salva a preferência
  function alternarTema(botao) {
    document.documentElement.classList.toggle('tema-escuro');
    const estaEscuro = document.documentElement.classList.contains('tema-escuro');
    localStorage.setItem('tema', estaEscuro ? 'escuro' : 'claro');
    atualizarBotaoTema(botao);
  }

  // Cria ou retorna o botão de toggle de tema
  function criarBotaoTema() {
    let botao = document.getElementById('botao-tema');
    if (!botao) {
      botao = document.createElement('button');
      botao.id = 'botao-tema';
      Object.assign(botao.style, {
        position: 'fixed',
        top: '1rem',
        right: '1rem',
        background: 'none',
        border: 'none',
        fontSize: '1.5rem',
        cursor: 'pointer',
        zIndex: '1000',
        width: '40px',
        height: '40px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        borderRadius: '50%',
        transition: 'background 0.2s'
      });
      botao.addEventListener('click', () => alternarTema(botao));
      document.body.appendChild(botao);
    }
    return botao;
  }

  // Inicializa o sistema de tema quando a página carregar
  function iniciarTema() {
    aplicarTemaSalvo();
    const botao = criarBotaoTema();
    atualizarBotaoTema(botao);
  }

  // Espera o DOM estar pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarTema);
  } else {
    iniciarTema();
  }
})();