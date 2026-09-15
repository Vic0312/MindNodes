(() => {
    'use strict';
    document.querySelectorAll('[data-midia]').forEach(aula => {
        const passos = [...aula.querySelectorAll('.midia-passo')];
        const controles = aula.querySelector('.midia-controles');
        const anterior = controles.querySelector('[data-midia-anterior]');
        const proximo = controles.querySelector('[data-midia-proximo]');
        const todos = controles.querySelector('[data-midia-todos]');
        let atual = 0;
        let mostrarTodos = false;
        function atualizar() {
            passos.forEach((passo, indice) => { passo.hidden = !mostrarTodos && indice !== atual; });
            anterior.disabled = mostrarTodos || atual === 0;
            proximo.disabled = mostrarTodos || atual === passos.length - 1;
            todos.setAttribute('aria-pressed', String(mostrarTodos));
            todos.textContent = mostrarTodos ? 'Voltar ao passo a passo' : 'Mostrar todos os passos';
            controles.querySelector('.midia-status').textContent = mostrarTodos
                ? `Todos os ${passos.length} passos visíveis.`
                : `Passo ${atual + 1} de ${passos.length}: ${passos[atual].querySelector('h3').textContent.replace(/^\d+\.\s*/, '')}`;
        }
        anterior.addEventListener('click', () => { atual = Math.max(0, atual - 1); atualizar(); });
        proximo.addEventListener('click', () => { atual = Math.min(passos.length - 1, atual + 1); atualizar(); });
        controles.querySelector('[data-midia-reiniciar]').addEventListener('click', () => { atual = 0; mostrarTodos = false; atualizar(); });
        todos.addEventListener('click', () => { mostrarTodos = !mostrarTodos; atualizar(); });
        atualizar();
        controles.hidden = false;
        aula.dataset.iniciada = 'true';
        const botao = aula.querySelector('[data-midia-video]');
        if (!botao) return;
        botao.hidden = false;
        botao.addEventListener('click', () => {
            const player = aula.querySelector('.midia-player');
            if (player.firstChild) {
                player.replaceChildren();
                player.hidden = true;
                botao.textContent = 'Carregar vídeo nesta página';
                return;
            }
            if (!/^[\w-]{11}$/.test(botao.dataset.midiaVideo)) return;
            const iframe = document.createElement('iframe');
            iframe.src = `https://www.youtube-nocookie.com/embed/${botao.dataset.midiaVideo}?autoplay=0`;
            iframe.title = botao.dataset.videoTitulo;
            iframe.allow = 'encrypted-media; picture-in-picture; fullscreen';
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'strict-origin-when-cross-origin';
            player.replaceChildren(iframe);
            player.hidden = false;
            botao.textContent = 'Fechar vídeo';
        });
    });
})();
