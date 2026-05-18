<?php
// Lógica simples para redirecionar ou processar (exemplo)
if(isset($_POST['cadastrar'])){
    // Aqui viria sua lógica de INSERT
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes - Cadastro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/index.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Ajuste para telas com muitos campos */
        .login-container { width: 500px !important; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 550px) {
            .login-container { width: 90% !important; }
            .grid-inputs { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="login-body">

<section class="login-background">

    <section class="login-container">

        <section class="login-logo">
            <i class="fa-solid fa-circle-nodes"></i>
            <h1>Mind<span>Nodes</span></h1>
        </section>

        <section class="login-text">
            <h2>Crie sua conta</h2>
            <p>Comece sua jornada nas estruturas de dados.</p>
        </section>

        <form method="POST" action="../processamento/processamento.php" enctype="multipart/form-data">
            
            <div class="grid-inputs">
                <section class="input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="inputNome" placeholder="Nome" required>
                </section>

                <section class="input-box">
                    <i class="fa-solid fa-user-tag"></i>
                    <input type="text" name="inputSobrenome" placeholder="Sobrenome" required>
                </section>
            </div>

            <section class="input-box">
                <i class="fa-solid fa-id-card"></i>
                <input type="text" name="inputCPF" placeholder="CPF (000.000.000-00)" required>
            </section>

            <div class="grid-inputs">
                <section class="input-box">
                    <i class="fa-solid fa-calendar-days"></i>
                    <input type="date" name="inputDataNasc" title="Data de Nascimento" required>
                </section>

                <section class="input-box">
                    <i class="fa-solid fa-phone"></i>
                    <input type="tel" name="inputTelefone" placeholder="Telefone" required>
                </section>
            </div>

            <section class="input-box">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="inputEmail" placeholder="E-mail" required>
            </section>

            <section class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="inputSenha" placeholder="Crie uma senha" required>
            </section>

            <section class="input-box" style="height: auto; padding: 10px 15px;">
                <i class="fa-solid fa-image"></i>
                <label style="color: #CAD2C5; font-size: 13px; margin-bottom: 5px; display: block;">Foto de Perfil:</label>
                <input type="file" name="inputFoto" accept="image/*" style="font-size: 12px;">
            </section>

            <button type="submit" name="cadastrar" class="login-btn">
                Finalizar Cadastro
            </button>

            <a href="login.php" id="cadastro_usuario" style="text-align: center; display: block; color: #CAD2C5; margin-top: 10px;">
                Já tem uma conta? Faça login
            </a>

        </form>

        <section class="login-footer">
            <p>MindNodes © 2026</p>
        </section>

    </section>

</section>

</body>
</html>