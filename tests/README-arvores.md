# Módulo de Árvores — primeira entrega

As aulas usam o escopo informado das aulas 08, 09 e 10 do professor Bruno Santos de Lima. Os exemplos são autorais. Nenhum código ou recurso do CADILAG foi utilizado.

## Convenções locais a confirmar

O simulador binário **não é uma árvore binária de busca**. Não foram encontradas regras de árvores documentadas anteriormente no projeto.

- Inserção: aluno escolhe o pai e um lado livre; primeiro nó vira raiz.
- Valores: inteiros únicos entre -999999 e 999999. Limite visual de 31 nós.
- Busca: visita raiz, esquerda e direita até encontrar o valor; não elimina ramos por comparação numérica.
- Remoção: encontra o alvo; identifica a última folha em largura (esquerda antes de direita), copia seu valor para o alvo e retira a folha original. Se o alvo já for essa folha, apenas a retira. No estado intermediário da cópia, o valor aparece temporariamente em duas posições, com explicação explícita.
- Altura e níveis: contagem em arestas; raiz no nível 0, folha com altura 0, árvore vazia com altura -1.
- Notação aninhada: `valor(esquerda, direita)`; `∅` representa ausência de nó.
- O modelo calcula a operação e produz cópias independentes dos estados. A interface mostra esses estados por passos; alterações ficam bloqueadas enquanto o aluno revê um estado intermediário. Limpar cancela a sequência e o estado final calculado.

Essas convenções são escolhas desta implementação, **não regras confirmadas do professor**. Trie, Patricia e AVL têm teoria e telas de preparação, sem operações habilitadas. Política de caracteres, marcação de palavras, compactação, balanceamento e convenções dessas estruturas precisam ser confirmadas nas próximas etapas.

## Verificação automatizada

Na raiz do projeto:

```powershell
node tests/arvore_binaria.cjs
node tests/arvores_navegador.cjs
```

O primeiro teste usa apenas Node. O segundo exige Node com WebSocket global (22+), PHP CLI e Chrome. Usa servidor e sessão temporários e não grava dados no banco. A navegação compartilhada pode consultar o saldo, seguindo seu comportamento existente. Variáveis opcionais: `MINDNODES_PHP` e `MINDNODES_CHROME`. Perfis e sessões de diagnóstico ficam na pasta temporária indicada na saída.

O teste de navegador verifica autenticação, URLs inválidas, quatro aulas, operações e navegação por passos, reinício durante uma operação, entradas inválidas, telas futuras desabilitadas, ausência de exceções JavaScript e preservação da inserção no simulador de listas. Valida larguras de 320, 375, 768 e 1280 px, incluindo árvore inclinada, limites do SVG e ausência de sobreposição entre caixas dos nós.

## Verificação manual

1. Faça login e abra Conteúdos. Confira os quatro cartões de árvores e o menu compartilhado.
2. Abra Árvore Binária e acesse o simulador. Leia as convenções.
3. Carregue o exemplo: raiz 10, esquerdo 20 e direito 5. A altura é 1 e existem duas folhas.
4. Busque 5, avance os passos e observe as visitas 10, 20 e 5. Revise usando Passo anterior.
5. Conclua, informe 30, selecione o pai 20 e o lado esquerdo. Insira e conclua. A altura passa a 2.
6. Remova 10 e observe a busca, a identificação da última folha em largura, a cópia de 30 para a raiz e a retirada da folha original.
7. Teste lado ocupado, valor repetido, valor ausente, decimal e campo vazio. Limpe durante uma operação e recomece.
8. Abra Trie, Patricia e AVL. Confirme a indicação “em preparação” e os controles indisponíveis.

Não foram criados exemplos C# executáveis, questões de Quiz, persistência ou novas tabelas para árvores nesta entrega.
