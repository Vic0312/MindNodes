(() => {
    'use strict';
    const form = document.getElementById('arvore-form');
    if (!form) return;
    const Model = window.ArvoreBinaria;
    const tree = new Model();
    const byId = id => document.getElementById('arvore-' + id);
    const svg = byId('svg');
    const parent = byId('pai');
    const side = byId('lado');
    const input = byId('valor');
    const operations = [...form.querySelectorAll('[data-operacao]')];
    let frames = [], current = 0, busy = false;
    const ns = 'http://www.w3.org/2000/svg';
    function element(name, attrs, text) {
        const node = document.createElementNS(ns, name);
        Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, value));
        if (text !== undefined) node.textContent = text;
        return node;
    }
    function draw(frame) {
        const root = frame.tree;
        svg.replaceChildren();
        const nodes = Model.dfs(root), positions = new Map();
        let slot = 0;
        function place(node, depth) {
            if (!node) return;
            place(node.left, depth + 1);
            positions.set(node.id, { x: 90 + slot++ * 150, y: 75 + depth * 130, depth });
            place(node.right, depth + 1);
        }
        place(root, 0);
        const width = Math.max(600, nodes.length * 150 + 30);
        const height = Math.max(180, (Model.height(root) + 1) * 130 + 40);
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        // Preserve readable labels on small screens; scrolling stays inside the viewport.
        svg.style.minWidth = width + 'px';
        const title = element('title', { id: 'arvore-svg-titulo' }, root ? `Árvore com ${nodes.length} nós. Raiz ${root.value}. ${Model.nested(root)}` : 'Árvore vazia');
        svg.append(title);
        if (!root) svg.append(element('text', { x: 300, y: 90, 'text-anchor': 'middle' }, 'Árvore vazia · insira a raiz'));
        for (const node of nodes) {
            const p = positions.get(node.id);
            for (const key of ['left', 'right']) {
                if (!node[key]) continue;
                const c = positions.get(node[key].id);
                svg.append(element('line', { x1: p.x, y1: p.y + 25, x2: c.x, y2: c.y - 25 }));
                svg.append(element('text', { x: (p.x + c.x) / 2 + (key === 'left' ? -12 : 12), y: (p.y + c.y) / 2, 'text-anchor': 'middle' }, key === 'left' ? 'E' : 'D'));
            }
        }
        for (const node of nodes) {
            const p = positions.get(node.id), leaf = !node.left && !node.right;
            const classes = ['arvore-no', ...(leaf ? ['arvore-folha'] : []), ...(frame.visited.includes(node.id) ? ['arvore-visitado'] : []), ...(frame.result === node.id ? ['arvore-resultado'] : [])];
            const group = element('g', { class: classes.join(' '), 'data-node-id': node.id });
            group.append(element('title', {}, `Valor ${node.value}, nível ${p.depth}${node === root ? ', raiz' : ''}${leaf ? ', folha' : ''}`));
            group.append(element('rect', { x: p.x - 56, y: p.y - 25, width: 112, height: 50, rx: 16 }));
            group.append(element('text', { x: p.x, y: p.y + 5, 'text-anchor': 'middle', class: 'arvore-valor' }, node.value));
            if (node === root) group.append(element('text', { x: p.x, y: p.y - 38, 'text-anchor': 'middle' }, 'RAIZ'));
            if (leaf) group.append(element('text', { x: p.x, y: p.y + 45, 'text-anchor': 'middle' }, 'folha'));
            svg.append(group);
        }
        byId('metricas').textContent = `Nós: ${nodes.length} · altura: ${Model.height(root)} · folhas: ${nodes.filter(n => !n.left && !n.right).map(n => n.value).join(', ') || 'nenhuma'}.`;
        byId('aninhada').textContent = Model.nested(root);
    }
    function parents() {
        const selected = parent.value;
        parent.replaceChildren();
        const nodes = Model.dfs(tree.root);
        if (!nodes.length) parent.add(new Option('Criar raiz', ''));
        else nodes.forEach(node => parent.add(new Option(`${node.value} · E ${node.left ? 'ocupado' : 'livre'} / D ${node.right ? 'ocupado' : 'livre'}`, String(node.id))));
        if ([...parent.options].some(option => option.value === selected)) parent.value = selected;
    }
    function controls() {
        operations.forEach(button => { button.disabled = busy; });
        input.disabled = busy;
        parent.disabled = busy || !tree.root;
        side.disabled = busy || !tree.root;
        byId('exemplo').disabled = busy;
        byId('limpar').disabled = false;
        byId('anterior').disabled = !frames.length || current === 0;
        byId('proximo').disabled = !frames.length || current === frames.length - 1;
        byId('concluir').disabled = !busy;
    }
    function show() {
        const frame = frames[current];
        draw(frame);
        byId('mensagem').textContent = frame.message;
        byId('progresso').textContent = `Passo ${current + 1} de ${frames.length}${current === frames.length - 1 ? ' · operação concluída' : ' · estado intermediário'}.`;
        [...byId('passos').children].forEach((li, index) => { if (index === current) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current'); });
        // Reviewing earlier snapshots locks mutations until the final state is shown again.
        busy = current !== frames.length - 1;
        parents(); controls();
        const active = svg.querySelector('.arvore-resultado') || [...svg.querySelectorAll('.arvore-visitado')].pop();
        if (active) {
            const rect = active.querySelector('rect');
            const viewport = svg.parentElement;
            const x = Number(rect.getAttribute('x'));
            viewport.scrollLeft = Math.max(0, x - viewport.clientWidth / 2 + 56);
            viewport.scrollTop = Math.max(0, Number(rect.getAttribute('y')) - viewport.clientHeight / 2);
        }
    }
    function begin(nextFrames) {
        frames = nextFrames; current = 0;
        byId('passos').replaceChildren(...frames.map(frame => { const li = document.createElement('li'); li.textContent = frame.message; return li; }));
        show();
    }
    function run(operation) {
        if (busy) return;
        try {
            if (!input.checkValidity() || !input.value.trim()) throw new Error('Informe um inteiro entre −999999 e 999999.');
            const value = Model.value(input.value);
            const next = operation === 'inserir' ? tree.insert(value, parent.value, side.value) : operation === 'buscar' ? tree.search(value) : tree.remove(value);
            begin(next);
        } catch (error) { byId('mensagem').textContent = error.message; }
    }
    form.addEventListener('submit', event => { event.preventDefault(); run('inserir'); });
    operations.filter(button => button.type !== 'submit').forEach(button => button.addEventListener('click', () => run(button.dataset.operacao)));
    byId('anterior').addEventListener('click', () => { if (current > 0) { current--; show(); } });
    byId('proximo').addEventListener('click', () => { if (current < frames.length - 1) { current++; show(); } });
    byId('concluir').addEventListener('click', () => { current = frames.length - 1; show(); });
    byId('limpar').addEventListener('click', () => {
        tree.clear(); frames = []; current = 0; busy = false; input.value = '';
        byId('passos').replaceChildren(); byId('progresso').textContent = '';
        byId('mensagem').textContent = 'Árvore reiniciada. A operação anterior foi cancelada; insira uma nova raiz.';
        draw(tree.frame('')); parents(); controls();
    });
    byId('exemplo').addEventListener('click', () => {
        tree.clear(); tree.insert(10); tree.insert(20, tree.root.id, 'left'); tree.insert(5, tree.root.id, 'right');
        begin([tree.frame('Exemplo autoral: raiz 10, filho esquerdo 20 e direito 5. Não há ordenação de árvore de busca.')]);
    });
    draw(tree.frame('')); parents(); controls();
})();
