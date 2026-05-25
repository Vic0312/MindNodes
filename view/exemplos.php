<?php
$pagina = 'exemplos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindNodes | Exemplos em C#</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/exemplos.css">
</head>
<body>
    <header class="topo">
        <a class="marca" href="../index.php"><span class="simbolo-logo">∞</span><strong>Mind<span>Nodes</span></strong></a>
        <nav class="menu">
            <a href="../index.php">Início</a>
            <a href="../view/estruturas.php">Estruturas</a>
            <a class="ativo" href="../view/exemplos.php">Exemplos em C#</a>
            <a href="../view/simulador.php">Simulador</a>
            <a href="../view/quiz.php">Quiz</a>
            <a href="../view/sobre.php">Sobre</a>
        </nav>
        <a class="botao-conta" href="#">👤</a>
    </header>

    <main>
        <section class="banner-interno">
            <span class="etiqueta">Exemplos práticos</span>
            <h1>Exemplos em <span>C#</span></h1>
            <p>Veja como as estruturas de dados podem ser representadas em código.</p>
        </section>

        <section class="area-codigo">
            <aside class="menu-lateral">
                <a class="ativo" href="#" data-exemplo="tad">TAD - Tipo Abstrato de Dados</a>
                <a href="#" data-exemplo="simples">Lista Simplesmente Encadeada</a>
                <a href="#" data-exemplo="dupla">Lista Duplamente Encadeada</a>
            </aside>

            <section class="codigo-box">
                <h2 id="titulo-codigo">TAD - Tipo Abstrato de Dados</h2>

<pre><code id="codigo-exemplo">// Exemplo de TAD em C#
public class Pilha
{
    private int[] elementos;
    private int topo;

    public Pilha(int tamanho)
    {
        elementos = new int[tamanho];
        topo = -1;
    }

    public void Empilhar(int valor)
    {
        topo++;
        elementos[topo] = valor;
    }

    public int Desempilhar()
    {
        int valor = elementos[topo];
        topo--;

        return valor;
    }
}</code></pre>
            </section>

            <section class="quiz-card explicacao-card">
                <h2>Entenda o exemplo</h2>

                <p id="texto-explicacao">
                    O TAD mostra o que a estrutura faz, sem obrigar o usuário a saber como ela funciona por dentro.
                </p>

                <strong id="dica-explicacao">
                    Exemplo: quem usa Empilhar e Desempilhar não precisa saber como os dados estão guardados internamente.
                </strong>
            </section>
        </section>
    </main>

    <script>
        const exemplos = {
            tad: {
                titulo: "TAD - Tipo Abstrato de Dados",
                codigo: `// Exemplo de TAD em C#
public class Pilha
{
    private int[] elementos;
    private int topo;

    public Pilha(int tamanho)
    {
        elementos = new int[tamanho];
        topo = -1;
    }

    public void Empilhar(int valor)
    {
        topo++;
        elementos[topo] = valor;
    }

    public int Desempilhar()
    {
        int valor = elementos[topo];
        topo--;

        return valor;
    }
}`,
                explicacao: "O TAD mostra o que a estrutura faz, sem obrigar o usuário a saber como ela funciona por dentro.",
                dica: "Exemplo: quem usa Empilhar e Desempilhar não precisa saber como os dados estão guardados internamente."
            },

            simples: {
                titulo: "Lista Simplesmente Encadeada",
                codigo: `// Nó da Lista Simplesmente Encadeada
public class No
{
    public int Valor;
    public No Proximo;

    public No(int valor)
    {
        Valor = valor;
        Proximo = null;
    }
}`,
                explicacao: "Na lista simplesmente encadeada, cada nó guarda um valor e uma referência para o próximo nó.",
                dica: "Ideia principal: a lista anda em uma direção só, sempre seguindo o próximo nó."
            },

            dupla: {
                titulo: "Lista Duplamente Encadeada",
                codigo: `// Nó da Lista Duplamente Encadeada
public class No
{
    public int Valor;
    public No Proximo;
    public No Anterior;

    public No(int valor)
    {
        Valor = valor;
        Proximo = null;
        Anterior = null;
    }
}`,
                explicacao: "Na lista duplamente encadeada, cada nó aponta para o próximo nó e também para o nó anterior.",
                dica: "Ideia principal: dá para navegar para frente e para trás dentro da lista."
            }
        };

        const links = document.querySelectorAll(".menu-lateral a");
        const titulo = document.getElementById("titulo-codigo");
        const codigo = document.getElementById("codigo-exemplo");
        const textoExplicacao = document.getElementById("texto-explicacao");
        const dicaExplicacao = document.getElementById("dica-explicacao");

        const parametro =
new URLSearchParams(window.location.search);

const estrutura =
parametro.get("estrutura");

if (estrutura && exemplos[estrutura]) {

    titulo.textContent =
    exemplos[estrutura].titulo;

    codigo.textContent =
    exemplos[estrutura].codigo;

    textoExplicacao.textContent =
    exemplos[estrutura].explicacao;

    dicaExplicacao.textContent =
    exemplos[estrutura].dica;

    links.forEach(item=>{

        item.classList.remove("ativo");

        if(
            item.dataset.exemplo
            === estrutura
        ){
            item.classList.add("ativo");
        }

    });

}

        links.forEach(link => {
            link.addEventListener("click", function(event) {
                event.preventDefault();

                links.forEach(item => item.classList.remove("ativo"));
                this.classList.add("ativo");

                const tipo = this.getAttribute("data-exemplo");

                titulo.textContent = exemplos[tipo].titulo;
                codigo.textContent = exemplos[tipo].codigo;
                textoExplicacao.textContent = exemplos[tipo].explicacao;
                dicaExplicacao.textContent = exemplos[tipo].dica;
            });
        });
    </script>
</body>
</html>