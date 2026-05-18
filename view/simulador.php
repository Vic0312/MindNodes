<?php $pagina = 'simulador'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Simulador</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/simulador.css">
</head>
<body>
<header class="topo">

    <a class="marca" href="index.php">
        <span class="simbolo-logo">∞</span>
        <strong>Mind<span>Nodes</span></strong>
    </a>
    
    <nav class="menu">
        <a href="index.php">Início</a>
        <a href="estruturas.php">Estruturas</a>
        <a href="exemplos.php">Exemplos em C#</a>
        <a class="ativo" href="simulador.php">Simulador</a>
        <a href="quiz.php">Quiz</a>
        <a href="sobre.php">Sobre</a>
    </nav>
    
    <a class="botao-conta" href="#">👤</a>

</header>
<main>
    
    <section class="banner-interno">
        <span class="etiqueta">Simulador</span>
        <h1>Visualize os <span>nós conectados</span></h1>
        <p>Área reservada para simular inserção, remoção e percurso nas listas.</p>
    </section>
    
    <section class="simulador-area">
    <section class="controles-simulador">
        <input type="number" id="valorNo" placeholder="Digite um valor">
        <button onclick="adicionarInicio()">Adicionar no início</button>
        <button onclick="adicionarFim()">Adicionar no final</button>
        <button onclick="removerValor()">Remover valor</button>
        <button onclick="buscarNo()">Buscar valor</button>
        <button onclick="percorrerLista()">Percorrer</button>
        <button onclick="limparLista()">Limpar</button>
    </section>

    <section class="simulacao" id="lista"></section>
    <section class="box-explicacao">
        <h2>O que aconteceu?</h2>
        <p id="mensagem">
            A lista começa com alguns nós. Use os botões para interagir com a estrutura.
        </p>
    </section>

    
</section>

</main>

<script>
let lista = [10, 20, 30,40,50];

function atualizarLista() {
    const area = document.getElementById("lista");
    area.innerHTML = "";

    if (lista.length === 0) {
        area.innerHTML = "<p class='lista-vazia'>Lista vazia → NULL</p>";
        return;
    }

    lista.forEach((valor) => {
        area.innerHTML += `<span>${valor}</span><strong>→</strong>`;
    });

    area.innerHTML += `<span class="null">NULL</span>`;
}

function pegarValor() {
    const input = document.getElementById("valorNo");

    if (input.value === "") {
        document.getElementById("mensagem").innerText = "Digite um valor primeiro.";
        return null;
    }

    return input.value;
}

function adicionarInicio() {
    const valor = pegarValor();
    if (valor === null) return;

    lista.unshift(valor);
    inputLimpar();
    atualizarLista();

    document.getElementById("mensagem").innerText =
        "Um novo nó foi adicionado no início da lista.";
}

function adicionarFim() {
    const valor = pegarValor();
    if (valor === null) return;

    lista.push(valor);
    inputLimpar();
    atualizarLista();

    document.getElementById("mensagem").innerText =
        "Um novo nó foi adicionado no final da lista.";
}

function removerValor() {
    const valor = pegarValor();
    if (valor === null) return;

    const posicao = lista.indexOf(valor);

    if (posicao === -1) {
        document.getElementById("mensagem").innerText = "Valor não encontrado.";
        return;
    }

    lista.splice(posicao, 1);
    inputLimpar();
    atualizarLista();

    document.getElementById("mensagem").innerText =
        "O nó com valor " + valor + " foi removido da lista.";
}

function buscarNo() {
    const valor = pegarValor();
    if (valor === null) return;

    const nos = document.querySelectorAll("#lista span:not(.null)");
    let encontrado = false;

    nos.forEach(no => no.classList.remove("no-destaque", "no-encontrado"));

    nos.forEach((no, index) => {
        setTimeout(() => {
            no.classList.add("no-destaque");

            if (no.innerText == valor) {
                no.classList.add("no-encontrado");
                encontrado = true;

                document.getElementById("mensagem").innerText =
                    "Valor encontrado no nó " + (index + 1) + ".";
            }

            setTimeout(() => {
                no.classList.remove("no-destaque");
            }, 600);

            if (index === nos.length - 1 && !encontrado) {
                setTimeout(() => {
                    document.getElementById("mensagem").innerText =
                        "Valor não encontrado na lista.";
                }, 650);
            }
        }, index * 700);
    });
}

function percorrerLista() {
    const nos = document.querySelectorAll("#lista span:not(.null)");

    if (lista.length === 0) {
        document.getElementById("mensagem").innerText = "A lista está vazia.";
        return;
    }

    nos.forEach(no => no.classList.remove("no-destaque", "no-encontrado"));

    nos.forEach((no, index) => {
        setTimeout(() => {
            no.classList.add("no-destaque");

            document.getElementById("mensagem").innerText =
                "Percorrendo o nó " + (index + 1) + " com valor " + no.innerText + ".";

            setTimeout(() => {
                no.classList.remove("no-destaque");
            }, 600);

            if (index === nos.length - 1) {
                setTimeout(() => {
                    document.getElementById("mensagem").innerText =
                        "Percurso finalizado: " + lista.join(" → ") + " → NULL";
                }, 700);
            }
        }, index * 700);
    });
}

function limparLista() {
    lista = [];
    atualizarLista();

    document.getElementById("mensagem").innerText =
        "Todos os nós foram removidos.";
}

function inputLimpar() {
    document.getElementById("valorNo").value = "";
}

atualizarLista();
</script>

</body></html>
