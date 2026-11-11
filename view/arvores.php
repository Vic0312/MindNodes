<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }
require __DIR__ . '/partials/arvores_conteudo.php';
$tipo = $_GET['tipo'] ?? 'binaria';
if (!is_string($tipo) || !isset($arvores[$tipo])) $tipo = 'binaria';
$aula = $arvores[$tipo];
function arvoreTexto($valor) { echo htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | <?php arvoreTexto($aula['nome']); ?></title>
    <link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/estruturas.css">
    <link rel="stylesheet" href="../css/fila_fifo.css"><link rel="stylesheet" href="../css/arvores.css">
    <link rel="stylesheet" href="../css/navegacao.css"><script src="../js/navegacao.js" defer></script>
</head>
<body>
<?php require __DIR__ . '/partials/header.php'; ?>
<main class="aula-fila modulo-arvores">
    <section class="banner-interno"><span class="etiqueta">Árvores · aulas 08, 09 e 10</span>
        <h1><?php arvoreTexto($aula['nome']); ?></h1><p><?php arvoreTexto($aula['resumo']); ?></p>
        <a class="fila-voltar" href="estruturas.php">← Todos os conteúdos</a>
    </section>
    <nav class="fila-indice" aria-label="Tipos de árvores">
        <?php foreach ($arvores as $chave => $item): ?><a href="?tipo=<?php echo $chave; ?>" <?php if ($tipo === $chave): ?>aria-current="page"<?php endif; ?>><?php arvoreTexto($item['nome']); ?></a><?php endforeach; ?>
    </nav>
    <p>Escopo informado das aulas do professor Bruno Santos de Lima. Os exemplos visuais abaixo são autorais do MindNodes.</p>
    <section class="fila-secao"><span class="fila-kicker">01 · Conceito</span><h2>Como a estrutura organiza os dados</h2><p><?php arvoreTexto($aula['conceito']); ?></p>
        <h3>Características principais</h3><ul><?php foreach ($aula['caracteristicas'] as $texto): ?><li><?php arvoreTexto($texto); ?></li><?php endforeach; ?></ul>
    </section>
    <section class="fila-secao"><span class="fila-kicker">02 · Representação visual</span><h2>Observe as ligações</h2>
        <figure><pre class="arvore-esquema" tabindex="0" aria-label="Esquema textual da árvore"><?php arvoreTexto($aula['diagrama']); ?></pre><figcaption><?php arvoreTexto($aula['exemplo']); ?></figcaption></figure>
        <?php if ($tipo === 'binaria'): ?><p>Neste módulo, a notação autoral é <code>valor(esquerda, direita)</code>, com <code>∅</code> para ausência de nó. O exemplo fica <code>10(20(∅, ∅), 5(∅, ∅))</code>. Contando arestas, a raiz está no nível 0 e esta árvore tem altura 1.</p><?php endif; ?>
    </section>
    <section class="fila-secao"><span class="fila-kicker">03 · Operações</span><h2>O que muda na árvore</h2><ul><?php foreach ($aula['operacoes'] as $texto): ?><li><?php arvoreTexto($texto); ?></li><?php endforeach; ?></ul></section>
    <?php if ($tipo === 'binaria'): ?>
    <section class="fila-secao"><span class="fila-kicker">Convenções locais · a confirmar</span><h2>Como funciona este simulador</h2>
        <p>Estas são decisões da implementação, não regras atribuídas ao professor. Não há ordenação automática por valor.</p>
        <ul><li>Valores inteiros únicos entre −999999 e 999999; limite visual de 31 nós.</li><li>Inserção na raiz vazia ou em um lado livre do pai escolhido pelo aluno.</li><li>Busca em profundidade: raiz, subárvore esquerda e subárvore direita, sem descartar ramos pela comparação numérica.</li><li>Remoção: localiza o valor. Se não for o último nó visitado em largura (nível a nível, esquerda antes de direita), copia o valor desse último nó para o alvo e retira a última folha. Os demais valores são preservados; as posições podem mudar.</li><li>Níveis e altura contam arestas: raiz no nível 0, folha com altura 0 e árvore vazia com altura −1.</li><li>Representação aninhada: valor(esquerda, direita); ∅ indica referência ausente.</li></ul>
    </section>
    <?php endif; ?>
    <section class="fila-secao"><span class="fila-kicker">04 · Fixação</span><h2>Revise e experimente</h2><p><?php arvoreTexto($aula['revisao']); ?></p>
        <?php if ($tipo !== 'binaria'): ?><p><strong>Simulador em preparação.</strong> A seleção e a área de estudo estão disponíveis; as operações serão implementadas em uma próxima etapa, após confirmar as convenções.</p><?php endif; ?>
        <a class="fila-cta" href="simulador_arvores.php?tipo=<?php echo $tipo; ?>"><?php echo $tipo === 'binaria' ? 'Experimentar o simulador' : 'Ver preparação do simulador'; ?> →</a>
    </section>
</main>
</body></html>
