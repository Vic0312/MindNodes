<?php
session_start();
require_once __DIR__ . '/../controller/AuthController.php';

header('Cache-Control: no-store');
$token = AuthController::tokenRecuperacao();
$mensagens = [
    'campos' => 'Preencha todos os campos obrigatórios.',
    'dados' => 'Os dados informados não correspondem a uma conta cadastrada.',
    'sessao' => 'Sessão inválida ou expirada. Inicie novamente a recuperação.',
    'senha' => 'A senha deve possuir pelo menos 8 caracteres.',
    'confirmacao' => 'A confirmação da senha não corresponde.',
    'banco' => 'Não foi possível concluir a recuperação. Tente novamente.'
];
$mensagem = $mensagens[$_SESSION['recuperacao_erro'] ?? ''] ?? null;
unset($_SESSION['recuperacao_erro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes - Recuperar senha</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="login-body">
<section class="login-background">
    <section class="login-container">
        <section class="login-logo">
            <i class="fa-solid fa-circle-nodes"></i>
            <h1>Mind<span>Nodes</span></h1>
        </section>
        <section class="login-text">
            <h2>Recuperar senha</h2>
            <p>Informe os dados da sua conta para recuperar sua senha.</p>
        </section>
        <?php if ($mensagem): ?>
            <p role="alert"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="POST" action="../processamento/processamento.php">
            <input type="hidden" name="acao" value="recuperarSenha">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <section class="input-box">
                <i class="fa-solid fa-id-card"></i>
                <input type="text" name="cpf" placeholder="CPF (000.000.000-00)" aria-label="CPF" required>
            </section>
            <section class="input-box">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail" aria-label="E-mail" autocomplete="email" required>
            </section>
            <section class="input-box">
                <i class="fa-solid fa-calendar-days"></i>
                <input type="date" name="dataNascimento" title="Data de nascimento" aria-label="Data de nascimento" required>
            </section>
            <button type="submit" class="login-btn">Continuar</button>
            <section class="links_esquecisenha"><a href="login.php">Voltar ao login</a></section>
        </form>
        <section class="login-footer"><p>MindNodes © 2026</p></section>
    </section>
</section>
</body>
</html>

