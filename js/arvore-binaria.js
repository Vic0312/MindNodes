/* Convenções locais documentadas em arvores.php. Sem regra de árvore de busca. */
(function (root) {
    'use strict';
    class ArvoreBinaria {
        constructor() { this.root = null; this.nextId = 1; }
        static clone(node) { return node ? { ...node, left: ArvoreBinaria.clone(node.left), right: ArvoreBinaria.clone(node.right) } : null; }
        static dfs(node) { return node ? [node, ...this.dfs(node.left), ...this.dfs(node.right)] : []; }
        static height(node) { return node ? 1 + Math.max(this.height(node.left), this.height(node.right)) : -1; }
        static nested(node) { return node ? `${node.value}(${this.nested(node.left)}, ${this.nested(node.right)})` : '∅'; }
        static value(raw) {
            if (typeof raw !== 'string' && typeof raw !== 'number') throw new Error('Informe um valor inteiro.');
            if (!String(raw).trim() || !Number.isSafeInteger(Number(raw)) || Math.abs(Number(raw)) > 999999) throw new Error('Informe um inteiro entre −999999 e 999999.');
            return Number(raw);
        }
        frame(message, visited = [], result = null) { return { tree: ArvoreBinaria.clone(this.root), message, visited: [...visited], result }; }
        clear() { this.root = null; this.nextId = 1; }
        insert(raw, parentId, side) {
            const value = ArvoreBinaria.value(raw);
            const nodes = ArvoreBinaria.dfs(this.root);
            if (nodes.some(n => n.value === value)) throw new Error('Este valor já existe. A convenção local utiliza valores únicos.');
            if (nodes.length >= 31) throw new Error('Limite visual de 31 nós atingido. Remova um nó antes de inserir.');
            const parent = nodes.find(n => n.id === Number(parentId));
            if (this.root && (!parent || !['left', 'right'].includes(side))) throw new Error('Escolha um pai e um lado para inserir.');
            if (this.root && parent[side]) throw new Error('Este lado do pai já está ocupado. Escolha um lado livre.');
            const frames = [this.frame(this.root ? `Pai ${parent.value} selecionado. Verifique seu lado ${side === 'left' ? 'esquerdo' : 'direito'}.` : 'A árvore está vazia. O primeiro nó será a raiz.', parent ? [parent.id] : [])];
            const node = { id: this.nextId++, value, left: null, right: null };
            if (!this.root) this.root = node;
            else parent[side] = node;
            frames.push(this.frame(`${value} inserido ${parent ? `como filho ${side === 'left' ? 'esquerdo' : 'direito'} de ${parent.value}` : 'como raiz'}. Nenhuma ordenação por valor foi aplicada.`, [], node.id));
            return frames;
        }
        search(raw) {
            const value = ArvoreBinaria.value(raw);
            const frames = [], visited = [];
            for (const node of ArvoreBinaria.dfs(this.root)) {
                visited.push(node.id);
                const found = node.value === value;
                frames.push(this.frame(`Visita ${node.value}: ${found ? `valor ${value} encontrado` : `diferente de ${value}; continuar em raiz–esquerda–direita`}.`, visited, found ? node.id : null));
                if (found) return frames;
            }
            frames.push(this.frame(this.root ? `${value} não encontrado após visitar todos os nós. Nenhum ramo foi descartado pelo valor.` : 'Árvore vazia: não há valores para buscar.', visited));
            return frames;
        }
        remove(raw) {
            const value = ArvoreBinaria.value(raw);
            const frames = this.search(value);
            const target = ArvoreBinaria.dfs(this.root).find(n => n.value === value);
            if (!target) { frames.push(this.frame('Nenhum nó removido: o valor não está na árvore.')); return frames; }
            const queue = [{ node: this.root, parent: null, side: null }];
            for (let i = 0; i < queue.length; i++) {
                const node = queue[i].node;
                for (const side of ['left', 'right']) if (node[side]) queue.push({ node: node[side], parent: node, side });
            }
            const last = queue[queue.length - 1];
            frames.push(this.frame(`Ordem em largura: ${queue.map(item => item.node.value).join(' → ')}. A última folha é ${last.node.value}.`, queue.map(item => item.node.id), last.node.id));
            const replacement = last.node.value;
            if (last.node !== target) {
                target.value = replacement;
                frames.push(this.frame(`Copie ${replacement} para a posição de ${value}. O próximo passo retira a folha original ${replacement}; este é um estado intermediário.`, [target.id, last.node.id], target.id));
            }
            if (last.parent) last.parent[last.side] = null;
            else this.root = null;
            frames.push(this.frame(`${value} removido. ${last.node === target ? 'O nó já era a última folha em largura.' : `Sua posição agora contém ${replacement}; a folha original foi retirada.`} ${this.root ? 'Os demais valores foram preservados.' : 'A árvore ficou vazia.'}`, [], last.node === target ? null : target.id));
            return frames;
        }
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = ArvoreBinaria;
    else root.ArvoreBinaria = ArvoreBinaria;
})(typeof globalThis !== 'undefined' ? globalThis : this);
