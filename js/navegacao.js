(() => {
    const botao = document.querySelector('.mn-toggle');
    const menu = document.getElementById('mn-menu');
    if (!botao || !menu) return;
    const mobile = matchMedia('(max-width: 650px)');
    function ajustar() {
        botao.hidden = !mobile.matches;
        menu.hidden = mobile.matches;
        botao.setAttribute('aria-expanded', String(!menu.hidden));
    }
    botao.addEventListener('click', () => {
        menu.hidden = !menu.hidden;
        botao.setAttribute('aria-expanded', String(!menu.hidden));
    });
    menu.addEventListener('keydown', evento => {
        if (evento.key === 'Escape' && mobile.matches) {
            menu.hidden = true;
            botao.setAttribute('aria-expanded', 'false');
            botao.focus();
        }
    });
    mobile.addEventListener('change', ajustar);
    ajustar();
})();
