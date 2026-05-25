<?php
if(isset($_POST['entrar'])){
    header("Location: view/home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes - Login</title>
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
            <h2>Bem-vindo de volta</h2>
            <p>
                Aprenda Estruturas de Dados de forma visual,
                prática e interativa.
            </p>
        </section>

        <form method="POST" action="../processamento/processamento.php">

            <section class="input-box">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" placeholder="Digite seu e-mail" name="inputEmailLog" required>
            </section>

            <section class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" placeholder="Digite sua senha" name="inputSenhaLog" required>
            </section>

            <section class="links_esquecisenha">
                    <a href="../view/redefinir_senha.php" id="esqueci_senha">Esqueci minha senha</a>
            </section>

            <button type="submit" name="entrar" class="login-btn">
                Entrar
            </button>

            <a href="../view/cadastrar_usuario.php" id="cadastro_funcionario">Cadastre-se</a>

        </form>

        <section class="login-footer">
            <p>MindNodes © 2026</p>
        </section>

    </section>

</section>

</body>
</html>