<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }
require __DIR__ . '/partials/arvores_conteudo.php';
$tipo = $_GET['tipo'] ?? 'binaria';
if (!is_string($tipo) || !isset($arvores[$tipo])) $tipo = 'binaria';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Simuladores de Árvores</title>
    <link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/estruturas.css">
    <link rel="stylesheet" href="../css/fila_fifo.css"><link rel="stylesheet" href="../css/arvores.css">
    <link rel="stylesheet" href="../css/navegacao.css"><script src="../js/navegacao.js" defer></script>
    <script src="../js/arvore-binaria.js" defer></script><script src="../js/simulador-arvores.js" defer></script>
</head>
<body>
<?php require __DIR__ . '/partials/header.php'; ?>
<main class="aula-fila modulo-arvores" data-simulador-arvores>
    <section class="banner-interno"><span class="etiqueta">Laboratório de Árvores</span><h1>Explore as <span>ligações entre nós</span></h1><p>Construa a hierarquia e acompanhe cada operação por etapas.</p><a class="fila-voltar" href="simulador.php">← Simulador de listas</a></section>
    <nav class="fila-indice" aria-label="Simuladores de árvores"><?php foreach ($arvores as $chave => $item): ?><a href="?tipo=<?php echo $chave; ?>" <?php if ($tipo === $chave): ?>aria-current="page"<?php endif; ?>><?php echo $item['nome']; ?><?php if ($chave !== 'binaria'): ?> · em preparação<?php endif; ?></a><?php endforeach; ?></nav>
    <section class="fila-secao"><h2><?php echo $arvores[$tipo]['nome']; ?></h2><p><?php echo $arvores[$tipo]['resumo']; ?></p><a class="arvore-link" href="arvores.php?tipo=<?php echo $tipo; ?>">Ler a explicação teórica e as convenções →</a>
    <?php if ($tipo === 'binaria'): ?>
        <details class="arvore-convencoes"><summary>Convenções deste simulador (a confirmar com o professor)</summary><p>Árvore binária sem ordenação de busca. Você escolhe pai e lado. Inteiros únicos de −999999 a 999999; até 31 nós. Busca em raiz–esquerda–direita. Na remoção, o valor da última folha em largura substitui o alvo e essa folha é retirada. Altura conta arestas: vazia −1, raiz isolada 0. Notação: valor(esquerda, direita), ∅ para vazio. São escolhas locais do MindNodes.</p></details>
        <noscript><p>Ative JavaScript para desenhar e operar a árvore. A teoria continua disponível no link acima.</p></noscript>
        <form id="arvore-form" class="arvore-controles">
            <label>Valor inteiro<input id="arvore-valor" type="number" min="-999999" max="999999" step="1" required></label>
            <label>Pai da inserção<select id="arvore-pai"><option value="">Criar raiz</option></select></label>
            <label>Lado do filho<select id="arvore-lado"><option value="left">Esquerdo</option><option value="right">Direito</option></select></label>
            <button type="submit" data-operacao="inserir" disabled>Inserir</button><button type="button" data-operacao="buscar" disabled>Buscar</button><button type="button" data-operacao="remover" disabled>Remover</button>
        </form>
        <div class="arvore-controles"><button type="button" id="arvore-limpar" disabled>Limpar / reiniciar</button><button type="button" id="arvore-exemplo" disabled>Carregar exemplo 10, 20, 5</button></div>
    <?php else: ?>
        <p class="arvore-aviso"><strong>Em preparação.</strong> Inserção<?php echo $tipo === 'avl' ? ' e rotações' : ', busca e remoção'; ?> ainda não estão implementadas. Nenhuma operação é simulada nesta tela.</p>
        <fieldset disabled class="arvore-controles"><legend>Controles previstos · indisponíveis</legend><label><?php echo $tipo === 'avl' ? 'Valor' : 'Palavra'; ?><input type="<?php echo $tipo === 'avl' ? 'number' : 'text'; ?>"></label><button>Inserir</button><?php if ($tipo !== 'avl'): ?><button>Buscar</button><button>Remover</button><?php endif; ?><button>Limpar</button></fieldset>
    <?php endif; ?>
    </section>
    <section class="fila-secao" aria-labelledby="arvore-visual-titulo"><h2 id="arvore-visual-titulo">Visualização da árvore</h2>
        <p>Legenda: raiz identificada acima do nó; folhas com contorno tracejado; visita com contorno destacado; resultado com preenchimento escuro. E e D indicam os lados.</p>
        <div class="arvore-viewport" tabindex="0" role="region" aria-label="Desenho da árvore; use a rolagem para explorar árvores maiores"><svg id="arvore-svg" viewBox="0 0 600 180" role="img" aria-labelledby="arvore-svg-titulo"><title id="arvore-svg-titulo"><?php echo $tipo === 'binaria' ? 'Árvore vazia' : 'Simulador em preparação'; ?></title><text x="300" y="90" text-anchor="middle"><?php echo $tipo === 'binaria' ? 'Árvore vazia · insira a raiz' : 'Visualização disponível na próxima etapa'; ?></text></svg></div>
        <?php if ($tipo === 'binaria'): ?><p id="arvore-metricas">Nós: 0 · altura: −1 · folhas: nenhuma.</p><h3>Representação aninhada</h3><pre id="arvore-aninhada" class="arvore-esquema" tabindex="0">∅</pre><?php endif; ?>
    </section>
    <section class="fila-secao"><h2>O que aconteceu?</h2><p id="arvore-mensagem" role="status" aria-live="polite"><?php echo $tipo === 'binaria' ? 'A árvore começa vazia. Insira uma raiz ou carregue o exemplo autoral.' : 'Este simulador está em preparação. Consulte a teoria para estudar os conceitos disponíveis.'; ?></p>
    <?php if ($tipo === 'binaria'): ?><div class="arvore-controles"><button type="button" id="arvore-anterior" disabled>Passo anterior</button><button type="button" id="arvore-proximo" disabled>Próximo passo</button><button type="button" id="arvore-concluir" disabled>Concluir operação</button></div><p id="arvore-progresso"></p><ol id="arvore-passos" aria-label="Etapas da operação"></ol><p>Durante uma operação, use os passos ou conclua para liberar novas alterações. Limpar cancela a operação e esvazia a árvore.</p><?php endif; ?>
    </section>
</main></body></html>
