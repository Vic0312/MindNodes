<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../controller/AvatarController.php';
require_once __DIR__ . '/../controller/InventarioController.php';

if (empty($_SESSION['avatar_csrf'])) $_SESSION['avatar_csrf'] = bin2hex(random_bytes(32));
$mensagem = $_SESSION['avatar_mensagem'] ?? null;
unset($_SESSION['avatar_mensagem']);

function avatarEscapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function avatarUrl($caminho) {
    if (!is_string($caminho) || !preg_match('~^img/avatar/(?:base|cabelo|rosto|roupa|acessorio)/[a-z0-9_]+\.png$~D', $caminho)) {
        return '';
    }
    return '../' . $caminho;
}

try {
    $avatar = (new AvatarController())->buscarDoUsuario();
    $itens = (new InventarioController())->listarDoUsuario($_SESSION['usuario_id']);
} catch (Throwable $erro) {
    error_log('Falha ao carregar avatar: ' . $erro->getMessage());
    http_response_code(503);
    $avatar = null;
    $itens = [];
}

$categorias = [
    'cabelo' => 'Cabelo / chapéu',
    'rosto' => 'Rosto',
    'roupa' => 'Roupa',
    'acessorio' => 'Acessório',
];
$itensPorCategoria = array_fill_keys(array_keys($categorias), []);
foreach ($itens as $item) {
    if (isset($itensPorCategoria[$item['categoria']])) $itensPorCategoria[$item['categoria']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Meu Avatar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/avatar.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="../index.php" aria-label="MindNodes"><span class="simbolo-logo">∞</span><strong>Mind<span>Nodes</span></strong></a>
        <nav class="menu" aria-label="Navegação principal">
            <a href="home.php">Início</a>
            <a href="estruturas.php">Estruturas</a>
            <a href="quiz.php">Quiz</a>
            <a href="desempenho.php">Desempenho</a>
            <a href="loja.php">Loja</a>
            <a href="perfil.php">Meu perfil</a>
        </nav>
    </header>

    <main class="avatar-page">
        <div class="avatar-heading">
            <div><span class="avatar-eyebrow">Sua identidade no MindNodes</span><h1>Meu Avatar</h1><p>Escolha entre os itens que já fazem parte do seu inventário.</p></div>
            <a class="avatar-back" href="perfil.php">Voltar ao perfil</a>
        </div>

        <?php if ($mensagem): ?>
            <div class="avatar-alert <?= $mensagem['tipo'] === 'sucesso' ? 'sucesso' : 'erro' ?>" role="status"><?= avatarEscapar($mensagem['texto']) ?></div>
        <?php endif; ?>

        <?php if ($avatar === null): ?>
            <div class="avatar-alert erro" role="alert">Não foi possível carregar o Avatar. Tente novamente mais tarde.</div>
        <?php else: ?>
            <div class="avatar-layout">
                <section class="avatar-stage-card" aria-labelledby="preview-titulo">
                    <div class="avatar-stage-top"><span class="avatar-eyebrow">Visual atual</span><h2 id="preview-titulo">Node</h2></div>
                    <div class="avatar-preview" role="img" aria-label="Avatar Node com <?= avatarEscapar($avatar['roupa']['nome']) ?>, <?= avatarEscapar($avatar['rosto']['nome']) ?> e <?= avatarEscapar($avatar['cabelo']['nome']) ?>">
                        <?php foreach (['base', 'roupa', 'rosto', 'cabelo', 'acessorio'] as $camada): ?>
                            <?php if ($avatar[$camada] !== null && avatarUrl($avatar[$camada]['imagem']) !== ''): ?>
                                <img class="avatar-layer avatar-<?= avatarEscapar($camada) ?>" src="<?= avatarEscapar(avatarUrl($avatar[$camada]['imagem'])) ?>" alt="" aria-hidden="true">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <p class="avatar-stage-caption">Equipado agora</p>
                    <div class="avatar-equipped-list">
                        <?php foreach (['cabelo', 'rosto', 'roupa', 'acessorio'] as $camada): ?>
                            <span><strong><?= avatarEscapar($categorias[$camada]) ?></strong><?= avatarEscapar($avatar[$camada]['nome'] ?? 'Nenhum') ?></span>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="avatar-wardrobe" aria-labelledby="inventario-titulo">
                    <div class="avatar-wardrobe-head"><span class="avatar-eyebrow">Seu inventário</span><h2 id="inventario-titulo">Personalizar</h2><p>Selecione uma peça para equipá-la no Avatar.</p></div>
                    <?php foreach ($categorias as $categoria => $titulo): ?>
                        <section class="avatar-category" aria-labelledby="categoria-<?= avatarEscapar($categoria) ?>">
                            <div class="avatar-category-heading"><h3 id="categoria-<?= avatarEscapar($categoria) ?>"><?= avatarEscapar($titulo) ?></h3><span><?= count($itensPorCategoria[$categoria]) ?> <?= count($itensPorCategoria[$categoria]) === 1 ? 'item' : 'itens' ?></span></div>
                            <?php if (!$itensPorCategoria[$categoria]): ?>
                                <p class="avatar-empty"><?= $categoria === 'acessorio' ? 'Nenhum acessório disponível.' : 'Nenhum item desta categoria no seu inventário.' ?></p>
                            <?php else: ?>
                                <div class="avatar-items">
                                    <?php foreach ($itensPorCategoria[$categoria] as $item): ?>
                                        <?php $equipado = (int) ($avatar[$categoria]['id_item'] ?? 0) === (int) $item['id_item']; ?>
                                        <article class="avatar-item <?= $equipado ? 'equipado' : '' ?>">
                                            <div class="avatar-item-image">
                                                <?php if (avatarUrl($item['imagem']) !== ''): ?><img src="<?= avatarEscapar(avatarUrl($item['imagem'])) ?>" alt="" loading="lazy"><?php endif; ?>
                                            </div>
                                            <div class="avatar-item-content">
                                                <span class="avatar-item-type"><?= avatarEscapar($titulo) ?></span>
                                                <h4><?= avatarEscapar($item['nome']) ?></h4>
                                                <?php if (!empty($item['habilidade'])): ?><p class="avatar-skill"><strong>Habilidade:</strong> <?= avatarEscapar(str_replace('_', ' ', $item['habilidade'])) ?></p><?php endif; ?>
                                                <?php if (!empty($item['descricao_habilidade'])): ?><p class="avatar-description"><?= avatarEscapar($item['descricao_habilidade']) ?></p><?php endif; ?>
                                                <?php if ($equipado): ?>
                                                    <span class="avatar-equipped-badge">Equipado</span>
                                                <?php else: ?>
                                                    <form method="post" action="../processamento/processamento.php">
                                                        <input type="hidden" name="acao" value="equiparAvatar">
                                                        <input type="hidden" name="csrf" value="<?= avatarEscapar($_SESSION['avatar_csrf']) ?>">
                                                        <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                        <button type="submit">Equipar</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </section>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
