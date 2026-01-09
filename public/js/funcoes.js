// Módulo de utilitários genéricos
export function formatCPF(value) {
  const digits = String(value).replace(/\D/g, '').slice(0, 11);
  let v = digits;
  v = v.replace(/(\d{3})(\d)/, '$1.$2');
  v = v.replace(/(\d{3})(\d)/, '$1.$2');
  v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  return v;
}

export function somenteDigitos(s) {
  return String(s).replace(/\D/g, '');
}

// Função para exibir notificações
export function mostrarNotificacao(mensagem, tipo = 'info', duracao = 5000) {
  // Certificar-se de que o container existe
  const container = document.getElementById('feedback-container');
  if (!container) {
    console.error('Container para notificações não encontrado!');
    return;
  }

  // Criar elemento da notificação
  const notificacao = document.createElement('div');
  notificacao.className = `feedback-mensagem feedback-${tipo}`;
  notificacao.innerHTML = `
    <div class="feedback-conteudo">${mensagem}</div>
    <button class="feedback-fechar" title="Fechar">×</button>
    ${duracao > 0 ? '<div class="feedback-progresso"><div class="feedback-progresso-barra"></div></div>' : ''}
  `;

  // Adicionar evento para fechar a notificação ao clicar nela
  notificacao.addEventListener('click', function (e) {
    if (e.target.classList.contains('feedback-fechar') || e.target.classList.contains('feedback-conteudo') || e.target === notificacao) {
      fecharNotificacao(notificacao);
    }
  });

  // Adicionar a notificação ao container
  container.appendChild(notificacao);

  // Animação de entrada
  setTimeout(() => {
    notificacao.style.transform = 'translateX(0)';
    notificacao.style.opacity = '1';
  }, 10);

  // Fechar automaticamente após o tempo especificado
  if (duracao > 0) {
    setTimeout(() => {
      fecharNotificacao(notificacao);
    }, duracao);
  }

  // Função para fechar a notificação
  function fecharNotificacao(element) {
    element.style.transform = 'translateX(150%)';
    element.style.opacity = '0';
    element.style.maxHeight = '0';
    element.style.marginBottom = '0';
    element.style.overflow = 'hidden';

    setTimeout(() => {
      if (element.parentNode) {
        element.parentNode.removeChild(element);
      }
    }, 300);
  }
}

// Attacha listeners de CPF ao elemento identificado por selector (padrão '#cpf').
// Retorna true se o elemento foi encontrado e listeners adicionados.
export function attachCpfListener(selector = '#cpf') {
  if (typeof document === 'undefined') return false;
  const cpfEl = document.querySelector(selector);
  if (!cpfEl) return false;

  cpfEl.addEventListener('input', (e) => {
    const pos = e.target.selectionStart;
    const before = e.target.value;
    e.target.value = formatCPF(e.target.value);
    if (e.target.value.length > before.length && pos) {
      e.target.setSelectionRange(pos + (e.target.value.length - before.length), pos + (e.target.value.length - before.length));
    }
  });

  cpfEl.addEventListener('paste', (e) => {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text');
    cpfEl.value = formatCPF(text);
  });

  return true;
}

// Foca o primeiro elemento visível e focável dentro do root (document por padrão).
// Retorna o elemento focado ou null se nenhum encontrado.
export function focusFirstVisible(root = document, selector = 'input,select,textarea,button,a[href],[tabindex]') {
  const rootEl = (typeof root === 'string') ? document.querySelector(root) : (root || document);
  if (!rootEl) return null;

  const nodes = Array.from(rootEl.querySelectorAll(selector));
  for (const el of nodes) {
    try {
      if (el.disabled) continue;
      if (el.getAttribute && el.getAttribute('aria-hidden') === 'true') continue;
      if (el.name && String(el.name).startsWith('fake-')) continue; // pular campos fictícios anti-autofill
      const type = el.type;
      if (type === 'hidden') continue;
      if (el.hasAttribute && el.hasAttribute('readonly')) continue;
      const style = window.getComputedStyle(el);
      if (style.visibility === 'hidden' || style.display === 'none') continue;

      // Verifica se está no fluxo/layout visível
      if (el.offsetParent === null) {
        // pode ainda ser visível (ex: position fixed), então cheque bounding rect
        const r = el.getBoundingClientRect();
        if (r.width === 0 && r.height === 0) continue;
      }

      // Verifica se o elemento está dentro da viewport (ajuda a pular elementos off-screen)
      const rect = el.getBoundingClientRect();
      const vh = (window.innerHeight || document.documentElement.clientHeight);
      const vw = (window.innerWidth || document.documentElement.clientWidth);
      if (rect.bottom < 0 || rect.top > vh || rect.right < 0 || rect.left > vw) continue;

      el.focus();
      if (typeof el.select === 'function') el.select();
      return el;
    } catch (err) {
      // ignorar e tentar próximo
      continue;
    }
  }
  return null;
}

// Voltar à página anterior com fallback.
export function goBack() {
  try {
    if (document.referrer && new URL(document.referrer).origin === location.origin) {
      history.back();
      return;
    }
  } catch (e) {
    // ignore
  }

  if (history.length > 1) {
    history.back();
  } else {
    window.location.href = (window.BASE_PATH || '') + '/';
  }
}

// Inicializa botões de voltar: associa handlers a elementos com a classe .back-btn
// e exporta a função para uso direto.
export function initBackButtons(root = document) {
  try {
    if (typeof window !== 'undefined') window.goBack = goBack;
  } catch (e) {
    // ignore
  }

  const rootEl = (typeof root === 'string') ? document.querySelector(root) : (root || document);
  if (!rootEl) return;

  const elems = Array.from(rootEl.querySelectorAll('.back-btn'));
  elems.forEach((btn) => {
    try {
      btn.addEventListener('click', goBack);
    } catch (err) {
      // ignore
    }
  });
}