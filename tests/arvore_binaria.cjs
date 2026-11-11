'use strict';
const assert = require('node:assert/strict');
const Tree = require('../js/arvore-binaria.js');
const tree = new Tree();
const values = () => Tree.dfs(tree.root).map(n => n.value).sort((a, b) => a - b);
assert.equal(Tree.height(tree.root), -1);
assert.equal(Tree.nested(tree.root), '∅');
assert.match(tree.search(1).at(-1).message, /vazia/);
assert.match(tree.remove(1).at(-1).message, /Nenhum/);
for (const invalid of ['', ' ', 'x', 1.5, Infinity, 1000000, null, [], true]) assert.throws(() => tree.insert(invalid));
tree.insert(10);
const rootId = tree.root.id;
tree.insert(20, rootId, 'left');
tree.insert(5, rootId, 'right');
assert.equal(Tree.nested(tree.root), '10(20(∅, ∅), 5(∅, ∅))');
assert.equal(Tree.height(tree.root), 1);
assert.throws(() => tree.insert(20, rootId, 'left'), /já existe/);
assert.throws(() => tree.insert(30, rootId, 'left'), /ocupado/);
assert.throws(() => tree.insert(30, 999, 'right'), /pai/);
assert.throws(() => tree.insert(30, rootId, 'unknown'));
const search = tree.search(5);
assert.deepEqual(search.at(-1).visited, [rootId, tree.root.left.id, tree.root.right.id]);
assert.equal(search.at(-1).result, tree.root.right.id);
assert.equal(tree.search(999).at(-1).visited.length, 3);
const removal = tree.remove(10);
assert.deepEqual(values(), [5, 20]);
assert.equal(tree.root.value, 5);
assert.equal(removal[0].tree.value, 10, 'Snapshots anteriores são imutáveis');
assert.equal(removal.at(-1).tree.right, null);
tree.remove(20); assert.deepEqual(values(), [5]);
tree.remove(5); assert.equal(tree.root, null);
tree.insert(-10); tree.insert(0, tree.root.id, 'right'); tree.remove(-10);
assert.equal(tree.root.value, 0); assert.equal(Tree.height(tree.root), 0);
tree.clear(); tree.insert(0);
for (let i = 1; i < 31; i++) tree.insert(i, Tree.dfs(tree.root).at(-1).id, 'right');
assert.equal(Tree.height(tree.root), 30);
assert.throws(() => tree.insert(31, Tree.dfs(tree.root).at(-1).id, 'right'), /31/);
// Remove every value, checking that only the requested value disappears.
for (let i = 0; i < 31; i++) {
    const expected = values().filter(v => v !== i);
    tree.remove(i);
    assert.deepEqual(values(), expected);
}
assert.equal(tree.root, null);
// Ramificações em vários níveis: remover raiz, nós internos e folhas sem perder outros valores.
tree.insert(0);
for (let i = 1; i < 15; i++) {
    const nodes = Tree.dfs(tree.root);
    const parentValue = Math.floor((i - 1) / 2);
    tree.insert(i, nodes.find(n => n.value === parentValue).id, i % 2 ? 'left' : 'right');
}
assert.equal(Tree.height(tree.root), 3);
for (const value of [0, 3, 1, 14, 8, 2, 7, 4, 10, 5, 13, 6, 9, 11, 12]) {
    const expected = values().filter(v => v !== value);
    tree.remove(value);
    assert.deepEqual(values(), expected);
}
assert.equal(tree.root, null);
console.log('PASSOU: entradas, posições explícitas, busca sem ordenação, snapshots, remoção, altura, limite e reinício.');
