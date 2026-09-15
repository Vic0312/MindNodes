// Compilado somente pelo teste CLI, junto aos seis exemplos renderizados.
using System;
using System.IO;
using System.Reflection;

public static class Testes
{
    static void Exigir(bool condicao)
    {
        if (!condicao) throw new Exception("Comportamento incorreto");
    }

    static void Vazio(Action acao)
    {
        try { acao(); }
        catch (InvalidOperationException) { return; }
        throw new Exception("Operacao vazia aceita");
    }

    static object Campo(object objeto, string nome)
    {
        return objeto.GetType().GetField(nome, BindingFlags.Instance | BindingFlags.NonPublic).GetValue(objeto);
    }

    public static string Capturar(Action acao)
    {
        var anterior = Console.Out;
        var texto = new StringWriter();
        try { Console.SetOut(texto); acao(); }
        finally { Console.SetOut(anterior); }
        return texto.ToString().Replace("\r", "");
    }

    public static void Validar()
    {
        var tad = new Exemplo0.Pilha(2);
        Exigir(tad.EstaVazia());
        Vazio(() => tad.Desempilhar());
        Vazio(() => tad.Topo());
        tad.Empilhar(1); tad.Empilhar(2);
        Vazio(() => tad.Empilhar(3));
        Exigir(tad.Topo() == 2 && tad.Desempilhar() == 2 && tad.Desempilhar() == 1 && tad.EstaVazia());
        try { new Exemplo0.Pilha(0); throw new Exception("Capacidade invalida aceita"); }
        catch (ArgumentOutOfRangeException) { }

        var simples = new Exemplo1.ListaSimples();
        Exigir(!simples.Remover(1) && !simples.Buscar(1));
        simples.InserirFinal(1);
        Exigir(simples.Remover(1) && Campo(simples, "inicio") == null);
        simples.InserirInicio(2); simples.InserirInicio(1);
        simples.InserirFinal(2); simples.InserirFinal(3);
        Exigir(simples.Remover(2));
        Exigir(Capturar(simples.Exibir) == "1 -> 2 -> 3\n");
        Exigir(simples.Buscar(2) && !simples.Buscar(8));
        Exigir(simples.Remover(1) && simples.Remover(3) && !simples.Remover(8));
        Exigir(Capturar(simples.Exibir) == "2\n");
        Exigir(simples.Remover(2) && Campo(simples, "inicio") == null);
        simples.InserirFinal(9);
        Exigir(Capturar(simples.Exibir) == "9\n");

        var dupla = new Exemplo2.ListaDupla();
        Exigir(!dupla.Remover(1));
        dupla.InserirFinal(1);
        Exigir(dupla.Remover(1) && Campo(dupla, "inicio") == null && Campo(dupla, "fim") == null);
        dupla.InserirInicio(2); dupla.InserirInicio(1);
        dupla.InserirFinal(2); dupla.InserirFinal(3);
        Exigir(dupla.Remover(2));
        Exigir(Capturar(dupla.ExibirFrente) == "1 -> 2 -> 3\n");
        Exigir(Capturar(dupla.ExibirTras) == "3 -> 2 -> 1\n");
        Exigir(dupla.Remover(1) && dupla.Remover(3) && !dupla.Remover(8));
        var unico = (Exemplo2.No)Campo(dupla, "inicio");
        Exigir(unico == Campo(dupla, "fim") && unico.Anterior == null && unico.Proximo == null);
        Exigir(dupla.Remover(2) && Campo(dupla, "inicio") == null && Campo(dupla, "fim") == null);
        dupla.InserirInicio(9); dupla.InserirFinal(10);
        Exigir(Capturar(dupla.ExibirTras) == "10 -> 9\n");

        var fila = new Exemplo3.FilaEncadeada();
        for (int ciclo = 0; ciclo < 2; ciclo++)
        {
            Exigir(fila.EstaVazia());
            Vazio(() => fila.Frente()); Vazio(() => fila.Desenfileirar());
            fila.Enfileirar(10); fila.Enfileirar(20); fila.Enfileirar(30);
            Exigir(fila.Frente() == 10 && fila.Frente() == 10);
            Exigir(fila.Desenfileirar() == 10 && fila.Desenfileirar() == 20 && fila.Desenfileirar() == 30);
            Exigir(fila.EstaVazia() && Campo(fila, "inicio") == null && Campo(fila, "fim") == null);
        }

        var prioridade = new Exemplo4.FilaPrioridadeEncadeada();
        Exigir(prioridade.EstaVazia());
        Vazio(() => prioridade.Frente()); Vazio(() => prioridade.Desenfileirar());
        prioridade.Enfileirar(10, 2); prioridade.Enfileirar(20, 1);
        prioridade.Enfileirar(30, 2); prioridade.Enfileirar(40, 3);
        prioridade.Enfileirar(50, 1); prioridade.Enfileirar(60, 0);
        Exigir(prioridade.Frente() == 60 && prioridade.Frente() == 60);
        foreach (int esperado in new int[] { 60, 20, 50, 10, 30, 40 })
            Exigir(prioridade.Desenfileirar() == esperado);
        Exigir(prioridade.EstaVazia());
        prioridade.Enfileirar(70, 1);
        Exigir(prioridade.Desenfileirar() == 70 && prioridade.EstaVazia());

        var pilha = new Exemplo5.PilhaEncadeada();
        for (int ciclo = 0; ciclo < 2; ciclo++)
        {
            Exigir(pilha.EstaVazia());
            Vazio(() => pilha.Topo()); Vazio(() => pilha.Desempilhar());
            pilha.Empilhar(10); pilha.Empilhar(20); pilha.Empilhar(30);
            Exigir(pilha.Topo() == 30 && pilha.Topo() == 30);
            Exigir(pilha.Desempilhar() == 30 && pilha.Topo() == 20);
            Exigir(pilha.Desempilhar() == 20 && pilha.Desempilhar() == 10 && pilha.EstaVazia());
        }
        Console.WriteLine("PASSOU: seis implementacoes C#, limites, referencias, FIFO, prioridades estaveis e LIFO.");
    }
}
