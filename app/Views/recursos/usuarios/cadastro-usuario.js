document.getElementById('formCadastroUsuario').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    const status = document.getElementById('status');
    status.textContent = 'Cadastrando...';
    status.className = 'status';

    try {
        const res = await fetch('/projetos/dashboard/cadastro-usuario', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Resposta inválida do servidor.');
        }

        const result = await res.json();
        
        if (result.success) {
            // AQUI É A GRANDE MUDANÇA: Não salvamos mais o token no localStorage
            // O token foi definido como cookie pelo servidor.
            status.textContent = '✅ Usuário cadastrado com sucesso!';
            status.className = 'status success';
            e.target.reset();
            
            setTimeout(() => {
                const nivel = result.user.nivel;
                const basePath = '/projetos/dashboard';
                const rotas = {
                    'admin': basePath + '/admin',
                    'assinante': basePath + '/assinante',
                    'vendedor': basePath + '/vendedor',
                    'cliente': basePath + '/cliente'
                };

                // Apenas redirecionamos. O navegador enviará o cookie automaticamente
                // na próxima requisição.
                window.location.href = rotas[nivel] || basePath;
            }, 1000);

        } else {
            status.textContent = '❌ ' + result.message;
            status.className = 'status error';
        }
    } catch (error) {
        status.textContent = '❌ Erro: ' + error.message;
        status.className = 'status error';
    }

    setTimeout(() => { 
        status.textContent = ''; 
        status.className = 'status';
    }, 10000);
});