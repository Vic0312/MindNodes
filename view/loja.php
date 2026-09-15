<?php
session_start();
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['estaLogado'])) {
    header('Location: login.php');
    exit();
}
require_once __DIR__ . '/../controller/LojaController.php';
if (empty($_SESSION['loja_csrf'])) $_SESSION['loja_csrf'] = bin2hex(random_bytes(32));
$mensagem = $_SESSION['loja_mensagem'] ?? null;
unset($_SESSION['loja_mensagem']);
function lojaEscapar($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function lojaUrl($caminho) {
    if (!is_string($caminho) || !preg_match('~^img/avatar/(?:base|cabelo|rosto|roupa|acessorio)/[a-z0-9_]+\.png$~D', $caminho)) return '';
    return '../' . $caminho;
}
$categorias = ['cabelo' => 'Cabelo / chapéu', 'rosto' => 'Rosto', 'roupa' => 'Roupa', 'acessorio' => 'Acessório'];
$agrupados = array_fill_keys(array_keys($categorias), []);
try {
    $dados = (new LojaController())->carregarDados();
    foreach ($dados['itens'] as $item) {
        if (isset($agrupados[$item['categoria']])) $agrupados[$item['categoria']][] = $item;
    }
} catch (Throwable $erro) {
    error_log('Falha ao carregar loja: ' . $erro->getMessage());
    http_response_code(503);
    $dados = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Loja</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/loja.css">
    <link rel="stylesheet" href="../css/navegacao.css">
    <script src="../js/navegacao.js" defer></script>
</head>
<body>
<?php require __DIR__ . '/partials/header.php'; ?>
<main class="loja-page">
    <section class="loja-hero">
        <div><span class="loja-eyebrow">Coleção MindNodes</span><h1>Loja</h1><p>Escolha peças para o seu Avatar. Depois da compra, equipe-as em Meu Avatar.</p></div>
        <?php if ($dados !== null): ?><div class="loja-saldo" aria-label="Saldo atual"><span>Seu saldo</span><strong><?= (int) $dados['saldo'] ?></strong><span>moedas</span></div><?php endif; ?>
    </section>
    <?php if ($mensagem): ?><div class="loja-alert <?= $mensagem['tipo'] === 'sucesso' ? 'sucesso' : 'erro' ?>" role="status"><?= lojaEscapar($mensagem['texto']) ?></div><?php endif; ?>
    <?php if ($dados === null): ?>
        <div class="loja-alert erro" role="alert">Não foi possível carregar a Loja. Tente novamente mais tarde.</div>
    <?php else: ?>
        <div class="loja-intro"><div><span class="loja-eyebrow">Itens disponíveis</span><h2>Explore por categoria</h2></div><a href="avatar.php">Ir para Meu Avatar</a></div>
        <?php foreach ($categorias as $categoria => $titulo): ?>
            <section class="loja-category" aria-labelledby="loja-<?= lojaEscapar($categoria) ?>">
                <div class="loja-category-heading"><h3 id="loja-<?= lojaEscapar($categoria) ?>"><?= lojaEscapar($titulo) ?></h3><span><?= count($agrupados[$categoria]) ?> <?= count($agrupados[$categoria]) === 1 ? 'item' : 'itens' ?></span></div>
                <?php if (!$agrupados[$categoria]): ?>
                    <p class="loja-empty">Nenhum item disponível nesta categoria no momento.</p>
                <?php else: ?>
                    <div class="loja-grid">
                        <?php foreach ($agrupados[$categoria] as $item): ?>
                            <article class="loja-card <?= $item['adquirido'] ? 'adquirido' : '' ?>">
                                <div class="loja-card-image"><?php if (lojaUrl($item['imagem']) !== ''): ?><img src="<?= lojaEscapar(lojaUrl($item['imagem'])) ?>" alt="" loading="lazy"><?php endif; ?></div>
                                <div class="loja-card-content">
                                    <span class="loja-card-type"><?= lojaEscapar($titulo) ?></span>
                                    <h4><?= lojaEscapar($item['nome']) ?></h4>
                                    <?php if (!empty($item['descricao'])): ?><p class="loja-description"><?= lojaEscapar($item['descricao']) ?></p><?php endif; ?>
                                    <?php if (!empty($item['habilidade'])): ?><p class="loja-skill"><strong>Habilidade:</strong> <?= lojaEscapar(str_replace('_', ' ', $item['habilidade'])) ?></p><?php endif; ?>
                                    <?php if (!empty($item['descricao_habilidade'])): ?><p class="loja-skill-detail"><?= lojaEscapar($item['descricao_habilidade']) ?></p><?php endif; ?>
                                    <div class="loja-card-bottom"><span class="loja-price"><strong><?= (int) $item['preco'] ?></strong> moedas</span>
                                        <?php if ($item['adquirido']): ?>
                                            <span class="loja-owned">Adquirido</span>
                                        <?php else: ?>
                                            <form method="post" action="../processamento/processamento.php">
                                                <input type="hidden" name="acao" value="comprarItem">
                                                <input type="hidden" name="csrf" value="<?= lojaEscapar($_SESSION['loja_csrf']) ?>">
                                                <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                <button type="submit">Comprar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
