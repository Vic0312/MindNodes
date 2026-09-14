document.querySelectorAll('.quiz-habilidade-form').forEach((formulario) => {
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const botao = formulario.querySelector('button[type="submit"]');
        if (!botao || botao.disabled) return;
        botao.disabled = true;
        const feedback = document.getElementById('poderes-feedback');
        if (feedback) feedback.textContent = '';

        try {
            const resposta = await fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            const dados = await resposta.json();
            if (!dados.sucesso) {
                if (feedback) feedback.textContent = dados.mensagem || 'Não foi possível usar a habilidade.';
                botao.disabled = false;
                return;
            }

            const indicador = document.querySelector(`[data-habilidade-contador="${dados.habilidade}"] strong`);
            if (indicador) indicador.textContent = String(dados.restante);
            if (dados.habilidade === 'dica') {
                const painel = document.querySelector(`[data-dica-pergunta="${dados.id_pergunta}"]`);
                if (painel) { painel.textContent = dados.conteudo; painel.hidden = false; }
            } else if (dados.habilidade === 'resumo_rapido') {
                const painel = document.getElementById('painel-resumo');
                if (painel) { painel.textContent = dados.conteudo; painel.hidden = false; }
            } else if (dados.habilidade === 'eliminar_alternativa') {
                const questao = document.getElementById(`questao-${dados.id_pergunta}`);
                const alternativa = questao?.querySelector(`[data-alternativa-id="${dados.id_alternativa}"]`);
                const entrada = alternativa?.querySelector('input[type="radio"]');
                if (entrada) { entrada.checked = false; entrada.disabled = true; alternativa.classList.add('alternativa-eliminada'); }
            }
            document.querySelectorAll(`[data-habilidade-botao="${dados.habilidade}"]`).forEach((controle) => {
                controle.disabled = dados.restante <= 0;
            });
            if (feedback) feedback.textContent = 'Habilidade utilizada.';
        } catch (erro) {
            if (feedback) feedback.textContent = 'Não foi possível conectar ao Quiz. Tente novamente.';
            botao.disabled = false;
        }
    });
});
