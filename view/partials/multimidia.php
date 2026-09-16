<?php
require_once __DIR__ . '/multimidia_conteudo.php';

function textoMultimidia($texto) {
    echo htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cadeiaMultimidia($passo, $tipo) {
    $vertical = $tipo === 'pilha' || $tipo === 'tad';
    ?>
    <div class="midia-cadeia <?php echo $vertical ? 'midia-vertical' : ''; ?>" tabindex="0" role="group" aria-label="Nós e referências; use a rolagem horizontal se necessário">
        <?php if (!$passo['nos']): ?><strong>Sem nós · null</strong><?php endif; ?>
        <?php foreach ($passo['nos'] as $posicao => $valor): ?>
            <?php if ($posicao > 0): ?><span class="midia-seta" aria-hidden="true"><?php echo $vertical ? '↓' : ($tipo === 'dupla' ? '⇄' : '→'); ?></span><?php endif; ?>
            <div class="midia-no">
                <small>VALOR</small><strong><?php textoMultimidia($valor); ?></strong>
                <?php if (isset($passo['prioridades'])): ?><b>Prioridade P<?php textoMultimidia($passo['prioridades'][$posicao]); ?></b><?php endif; ?>
                <?php if ($tipo === 'dupla'): ?><span>Anterior ← <?php textoMultimidia($passo['nos'][$posicao - 1] ?? 'null'); ?></span><?php endif; ?>
                <span>Proximo <?php echo $vertical ? '↓' : '→'; ?> <?php textoMultimidia($passo['nos'][$posicao + 1] ?? 'null'); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function renderizarMultimidia($chave) {
    global $multimidia;
    $recurso = $multimidia[$chave];
    $id = 'midia-' . $chave;
    ?>
    <section class="midia-aula" id="<?php textoMultimidia($id); ?>" aria-labelledby="<?php textoMultimidia($id); ?>-titulo" data-midia>
        <p class="midia-etiqueta">Aprender com imagens e passos</p>
        <h2 id="<?php textoMultimidia($id); ?>-titulo"><?php textoMultimidia($recurso['titulo']); ?></h2>
        <p><?php textoMultimidia($recurso['objetivo']); ?></p>
        <?php if ($recurso['tipo'] === 'tad'): ?>
            <figure class="midia-contrato">
                <div class="midia-interface"><strong>INTERFACE / OPERAÇÕES · TAD PILHA</strong><span>Empilhar() · Desempilhar() · Topo() · EstaVazia()</span><b>Regra: LIFO</b></div>
                <span aria-hidden="true">↓</span><strong>IMPLEMENTAÇÃO INTERNA · alternativas</strong>
                <div class="midia-alternativas"><div><strong>Vetor</strong><p>Valores em posições indexadas; um índice controla o topo.</p></div><div><strong>Nós encadeados</strong><p>Referências ligam os nós; topo aponta para o mais recente.</p></div></div>
                <figcaption>O programa usa as operações. A representação interna pode variar mantendo o contrato do TAD.</figcaption>
            </figure>
        <?php endif; ?>
        <div class="midia-controles" hidden>
            <button type="button" data-midia-anterior aria-controls="<?php textoMultimidia($id); ?>-passos">← Anterior</button>
            <button type="button" data-midia-proximo aria-controls="<?php textoMultimidia($id); ?>-passos">Próximo →</button>
            <button type="button" data-midia-reiniciar>Reiniciar</button>
            <button type="button" data-midia-todos aria-pressed="false">Mostrar todos os passos</button>
            <p class="midia-status" role="status" aria-live="polite" aria-atomic="true"></p>
        </div>
        <div class="midia-passos" id="<?php textoMultimidia($id); ?>-passos">
            <?php foreach ($recurso['passos'] as $passo): ?>
                <article class="midia-passo">
                    <h3><?php textoMultimidia($passo['titulo']); ?></h3>
                    <p><?php textoMultimidia($passo['texto']); ?></p>
                    <figure>
                        <figcaption><?php textoMultimidia($passo['rotulo']); ?></figcaption>
                        <?php if (isset($passo['extra'])): ?><p class="midia-novo"><?php textoMultimidia($passo['extra']); ?></p><?php endif; ?>
                        <?php if ($recurso['tipo'] === 'tad'): ?>
                            <div class="midia-alternativas"><div><h4>Vetor · base à esquerda</h4><div class="midia-vetor"><?php foreach (array_reverse($passo['nos']) as $indice => $valor): ?><span><small>índice <?php textoMultimidia($indice); ?></small><b><?php textoMultimidia($valor); ?></b></span><?php endforeach; ?></div><p>Índice do topo: <?php textoMultimidia(count($passo['nos']) - 1); ?></p></div><div><h4>Nós · topo acima</h4><?php cadeiaMultimidia($passo, 'tad'); ?></div></div>
                        <?php else: cadeiaMultimidia($passo, $recurso['tipo']); endif; ?>
                    </figure>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="midia-conclusao"><?php textoMultimidia($recurso['conclusao']); ?></p>
        <?php if (isset($recurso['video'])): $video = $recurso['video']; ?>
            <aside class="midia-video">
                <h3>Vídeo complementar</h3>
                <p><strong><?php textoMultimidia($video['titulo']); ?></strong> — <?php textoMultimidia($video['autor']); ?></p>
                <p><?php textoMultimidia($video['orientacao']); ?></p>
                <button type="button" data-midia-video="<?php textoMultimidia($video['id']); ?>" data-video-titulo="<?php textoMultimidia($video['titulo']); ?>" hidden>Carregar vídeo nesta página</button>
                <div class="midia-player" hidden></div>
                <p><a href="https://www.youtube.com/watch?v=<?php textoMultimidia($video['id']); ?>" target="_blank" rel="noopener noreferrer">Assistir no YouTube (nova aba)</a> · <a href="<?php textoMultimidia($video['fonte']); ?>" target="_blank" rel="noopener noreferrer">Fonte e autoria</a></p>
                <p class="midia-nota">O vídeo depende de internet e da disponibilidade no YouTube. Se não carregar aqui, use o link acima. Os diagramas e passos continuam disponíveis sem o vídeo.</p>
            </aside>
        <?php endif; ?>
    </section>
    <?php
}
