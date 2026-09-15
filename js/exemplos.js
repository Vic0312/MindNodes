(() => {
    'use strict';
    const exemplos = JSON.parse(document.getElementById('dados-exemplos').textContent);
    const links = document.querySelectorAll('[data-exemplo]');
    const campos = {
        'titulo-codigo': 'titulo', 'texto-explicacao': 'descricao',
        'codigo-exemplo': 'codigo', 'uso-exemplo': 'uso', 'saida-exemplo': 'saida',
        'execucao-exemplo': 'execucao', 'complexidade-exemplo': 'complexidade'
    };

    function selecionar(chave) {
        if (!Object.hasOwn(exemplos, chave)) chave = 'tad';
        const exemplo = exemplos[chave];
        for (const [id, campo] of Object.entries(campos)) {
            document.getElementById(id).textContent = exemplo[campo];
        }
        for (const [id, campo] of Object.entries({ 'operacoes-exemplo': 'operacoes', 'observar-exemplo': 'observar' })) {
            document.getElementById(id).replaceChildren(...exemplo[campo].map(texto => {
                const item = document.createElement('li');
                item.textContent = texto;
                return item;
            }));
        }
        document.getElementById('teoria-exemplo').href = exemplo.teoria;
        document.getElementById('quiz-exemplo').href = 'quiz.php?assunto=' + encodeURIComponent(exemplo.quiz);
        links.forEach(link => {
            const ativo = link.dataset.exemplo === chave;
            link.classList.toggle('ativo', ativo);
            if (ativo) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
        document.querySelectorAll('.codigo-box pre').forEach(pre => { pre.scrollLeft = 0; });
    }

    links.forEach(link => link.addEventListener('click', evento => {
        if (evento.button !== 0 || evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) return;
        evento.preventDefault();
        history.pushState(null, '', link.href);
        selecionar(link.dataset.exemplo);
    }));
    window.addEventListener('popstate', () => selecionar(new URLSearchParams(location.search).get('estrutura')));
})();
