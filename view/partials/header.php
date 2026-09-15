<?php
require_once __DIR__ . '/../../controller/NavegacaoController.php';
// A Loja já consultou o saldo pela camada de moedas: reutiliza esse resultado.
$navegacao = NavegacaoController::carregar(basename($_SERVER['SCRIPT_NAME'] ?? '') === 'loja.php' ? ($dados['saldo'] ?? null) : null);
$paginaNavegacao = basename($_SERVER['SCRIPT_NAME'] ?? 'home.php', '.php');
if (in_array($paginaNavegacao, ['fila_fifo', 'fila_prioridade', 'pilha_encadeada'], true)) $paginaNavegacao = 'estruturas';
$linksNavegacao = ['home' => 'Home', 'estruturas' => 'Conteúdos', 'exemplos' => 'Exemplos'];
if ($navegacao['autenticado']) {
    $linksNavegacao += ['quiz' => 'Quiz', 'desempenho' => 'Desempenho', 'loja' => 'Loja', 'avatar' => 'Avatar', 'perfil' => 'Perfil'];
} else {
    $linksNavegacao += ['login' => 'Login', 'cadastrar_usuario' => 'Cadastro'];
}
?>
<header class="mn-header">
    <a class="mn-marca" href="home.php" aria-label="MindNodes — Home">∞ Mind<span>Nodes</span></a>
    <?php if ($navegacao['autenticado']): ?>
        <a class="mn-saldo" href="loja.php" aria-label="Saldo de moedas. Ir para Loja"><span aria-hidden="true">🪙</span> <span data-saldo-navegacao><?php echo $navegacao['saldo'] === null ? 'Saldo indisponível' : (int) $navegacao['saldo'] . ' moedas'; ?></span></a>
    <?php endif; ?>
    <button class="mn-toggle" type="button" aria-expanded="true" aria-controls="mn-menu" aria-label="Abrir ou fechar menu de navegação" hidden>Menu</button>
    <nav class="mn-menu" id="mn-menu" aria-label="Navegação principal">
        <div class="mn-links">
            <?php foreach ($linksNavegacao as $destino => $rotulo): ?>
                <a href="<?php echo $destino; ?>.php"<?php if ($paginaNavegacao === $destino): ?> aria-current="page" class="mn-ativo"<?php endif; ?>><?php echo $rotulo; ?></a>
            <?php endforeach; ?>
            <?php if ($navegacao['autenticado']): ?><a href="../processamento/logout.php">Sair</a><?php endif; ?>
        </div>
        <details class="mn-estruturas">
            <summary>Estruturas ensinadas</summary>
            <div><?php foreach ($navegacao['estruturas'] as $rotulo => $destino): ?><a href="<?php echo $destino; ?>"><?php echo $rotulo; ?></a><?php endforeach; ?></div>
        </details>
        <div class="mn-apoio"><a href="sobre.php">Sobre o MindNodes</a><a href="simulador.php">Simulador</a></div>
    </nav>
</header>
