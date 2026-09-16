<?php
// Roteiros autorais de estados didáticos. Não são um simulador nem executam C#.
$multimidia = [
    'tad' => [
        'titulo' => 'TAD: operações e implementação', 'tipo' => 'tad',
        'objetivo' => 'Compare duas representações que oferecem o mesmo comportamento de pilha. A interface descreve o que fazer; a implementação define como guardar e manipular os dados.',
        'passos' => [
            ['titulo' => '1. Um contrato, duas representações', 'texto' => 'Empilhar, Desempilhar, Topo e EstaVazia são operações do TAD Pilha. Vetor e nós são alternativas internas, não operações diferentes.', 'nos' => [20, 10], 'rotulo' => 'Topo: 20 nas duas representações.'],
            ['titulo' => '2. Empilhar(30)', 'texto' => 'O aluno solicita a mesma operação. No vetor, o índice do topo avança e recebe 30; na cadeia, um novo nó aponta para o topo antigo. Ambas passam a ter 30 no topo.', 'nos' => [30, 20, 10], 'rotulo' => 'Topo: 30 nas duas representações.'],
            ['titulo' => '3. Desempilhar()', 'texto' => 'As duas implementações devolvem 30 e voltam a ter 20 no topo. Quem usa o TAD observa LIFO, sem precisar acessar o vetor ou as referências internas.', 'nos' => [20, 10], 'rotulo' => 'Sai: 30. Novo topo: 20.'],
        ],
        'conclusao' => 'Mesmas operações e regra LIFO; formas internas diferentes. O vetor ilustrado tem espaço disponível para a inserção.',
    ],
    'simples' => [
        'titulo' => 'Lista Simplesmente Encadeada: siga Proximo', 'tipo' => 'simples',
        'objetivo' => 'Veja como inserir no início preserva a cadeia e como remover um nó reconecta os vizinhos. Cada cartão mostra o valor e sua referência Proximo.',
        'passos' => [
            ['titulo' => '1. A cadeia inicial', 'texto' => 'inicio aponta para 10. Cada nó conhece o próximo, até o último apontar para null.', 'nos' => [10, 20, 30], 'rotulo' => 'INÍCIO → 10'],
            ['titulo' => '2. Preparar o nó 5', 'texto' => 'novo.Proximo = inicio; liga o novo nó ao nó 10. inicio ainda aponta para 10: primeiro preservamos a ligação antiga.', 'nos' => [10, 20, 30], 'rotulo' => 'INÍCIO → 10', 'extra' => 'NOVO: [5 | Proximo → 10], ainda fora do acesso por inicio.'],
            ['titulo' => '3. Atualizar inicio', 'texto' => 'inicio = novo; torna 5 o primeiro nó. Percorrer a lista agora visita 5, 10, 20 e 30.', 'nos' => [5, 10, 20, 30], 'rotulo' => 'INÍCIO → 5'],
            ['titulo' => '4. Remover o valor 20', 'texto' => 'Após encontrar o nó anterior (10), sua referência Proximo recebe o próximo de 20, isto é, o nó 30. O nó 20 deixa de pertencer à lista.', 'nos' => [5, 10, 30], 'rotulo' => 'INÍCIO → 5 · removido: 20'],
        ],
        'conclusao' => 'Inserir no início é O(1). Buscar o valor para remover é O(n) no pior caso. null indica ausência de próximo nó.',
        'video' => ['id' => '0BDMqra4D94', 'titulo' => 'Lista Dinâmica Encadeada', 'autor' => 'André Backes · Programação Descomplicada', 'orientacao' => 'Observe a ligação entre um nó e seu sucessor. A aula usa C; os exemplos do MindNodes usam C#.', 'fonte' => 'https://programacaodescomplicada.wordpress.com/2013/06/11/aula-10-lista-dinamica-encadeada-pt-1-definicao/'],
    ],
    'dupla' => [
        'titulo' => 'Lista Duplamente Encadeada: ida e volta', 'tipo' => 'dupla',
        'objetivo' => 'Acompanhe Proximo e Anterior. As setas de ida e volta representam referências diferentes e precisam permanecer consistentes após cada operação.',
        'passos' => [
            ['titulo' => '1. Referências nos dois sentidos', 'texto' => 'inicio aponta para 10 e fim para 30. O Anterior de 10 e o Proximo de 30 são null.', 'nos' => [10, 20, 30], 'rotulo' => 'INÍCIO → 10 · FIM → 30'],
            ['titulo' => '2. InserirFinal(40)', 'texto' => 'novo.Anterior recebe o fim antigo (30); 30.Proximo recebe o novo nó; fim passa a apontar para 40. Abaixo está o estado após essas atualizações.', 'nos' => [10, 20, 30, 40], 'rotulo' => 'INÍCIO → 10 · FIM → 40'],
            ['titulo' => '3. Remover(20)', 'texto' => 'Depois da busca, 10.Proximo recebe 30 e 30.Anterior recebe 10. As duas ligações são ajustadas antes de considerar a remoção concluída.', 'nos' => [10, 30, 40], 'rotulo' => 'INÍCIO → 10 · FIM → 40 · removido: 20'],
            ['titulo' => '4. Percorrer para trás', 'texto' => 'Partindo de fim e seguindo Anterior, a visita é 40, 30, 10, até null. Os nós não mudam de posição: apenas a direção do percurso muda.', 'nos' => [10, 30, 40], 'rotulo' => 'PERCURSO INVERSO: 40 → 30 → 10 → null'],
        ],
        'conclusao' => 'Inserir nas extremidades é O(1) com inicio/fim. Remover por valor inclui uma busca O(n). Cada nó guarda duas referências.',
    ],
    'fila-fifo' => [
        'titulo' => 'Fila Encadeada FIFO: entrada e saída', 'tipo' => 'fila',
        'objetivo' => 'Compare o ponto de entrada com o ponto de saída: entra no fim e sai do início. FIFO significa primeiro a entrar, primeiro a sair.',
        'passos' => [
            ['titulo' => '1. Fila vazia', 'texto' => 'Sem nós, inicio e fim são null.', 'nos' => [], 'rotulo' => 'INÍCIO = null · FIM = null'],
            ['titulo' => '2. Enfileirar(10)', 'texto' => 'O primeiro nó é simultaneamente início e fim. Seu Proximo é null.', 'nos' => [10], 'rotulo' => 'INÍCIO → 10 · FIM → 10'],
            ['titulo' => '3. Enfileirar(20) e Enfileirar(30)', 'texto' => 'Em cada inserção, fim.Proximo recebe novo; depois fim recebe novo. O nó 10 continua na frente.', 'nos' => [10, 20, 30], 'rotulo' => 'SAÍDA: INÍCIO → 10 · ENTRADA: FIM → 30'],
            ['titulo' => '4. Desenfileirar()', 'texto' => 'Guarda 10 e move inicio para inicio.Proximo. Sai o primeiro inserido, sem percorrer a cadeia.', 'nos' => [20, 30], 'rotulo' => 'INÍCIO → 20 · FIM → 30 · sai: 10'],
            ['titulo' => '5. Remover os dois restantes', 'texto' => 'Saem 20 e depois 30. Ao remover 30, inicio torna-se null e fim também recebe null. A próxima inserção começa uma nova cadeia.', 'nos' => [], 'rotulo' => 'INÍCIO = null · FIM = null'],
        ],
        'conclusao' => 'Entrada: 10, 20, 30. Saída: 10, 20, 30. Enfileirar, Desenfileirar e Frente são O(1); o espaço é O(n).',
        'video' => ['id' => 'ragDjmJ7OUM', 'titulo' => 'Estruturas de Dados — Fila (Lista Encadeada) · LIBRAS', 'autor' => 'UNIVESP · Ulisses Martins Dias', 'orientacao' => 'Observe as referências das extremidades e relacione a retirada com FIFO. Esta versão inclui interpretação em Libras; a notação do vídeo pode diferir do C# da aula.', 'fonte' => 'https://www.youtube.com/watch?v=ragDjmJ7OUM'],
    ],
    'fila-prioridade' => [
        'titulo' => 'Fila de Prioridades: a posição e o empate', 'tipo' => 'prioridade',
        'objetivo' => 'Menor número significa maior prioridade. O número grande no cartão é o valor; P1, P2 e P3 identificam a prioridade, não a posição nem o valor.',
        'passos' => [
            ['titulo' => '1. Enfileirar(10, 2)', 'texto' => 'O primeiro nó tem valor 10 e prioridade 2.', 'nos' => [10], 'prioridades' => [2], 'rotulo' => 'INÍCIO → 10 (P2)'],
            ['titulo' => '2. Enfileirar(20, 1)', 'texto' => 'Como 1 < 2, o nó 20 entra na frente. A prioridade determina o atendimento antes da ordem de chegada.', 'nos' => [20, 10], 'prioridades' => [1, 2], 'rotulo' => 'INÍCIO → 20 (P1)'],
            ['titulo' => '3. Enfileirar(30, 2)', 'texto' => 'A condição <= passa pelos nós antigos de mesma prioridade. Assim, 30 entra depois de 10: o empate P2 preserva FIFO.', 'nos' => [20, 10, 30], 'prioridades' => [1, 2, 2], 'rotulo' => 'EMPATE P2: 10 chegou antes de 30'],
            ['titulo' => '4. Enfileirar(40, 3)', 'texto' => 'P3 fica após P1 e P2. A cadeia ordenada define a sequência de atendimento.', 'nos' => [20, 10, 30, 40], 'prioridades' => [1, 2, 2, 3], 'rotulo' => 'ORDEM: 20 P1 → 10 P2 → 30 P2 → 40 P3'],
            ['titulo' => '5. Desenfileirar()', 'texto' => 'Sai 20 (P1). O novo início é 10 (P2), que ainda permanece antes de 30 (P2).', 'nos' => [10, 30, 40], 'prioridades' => [2, 2, 3], 'rotulo' => 'INÍCIO → 10 (P2) · sai: 20 (P1)'],
        ],
        'conclusao' => 'Inserção ordenada: O(n) no pior caso. Remoção do início e consulta à frente: O(1). Espaço: O(n). Menor número tem maior prioridade; FIFO entre iguais.',
    ],
    'pilha-encadeada' => [
        'titulo' => 'Pilha Encadeada: acompanhe o topo', 'tipo' => 'pilha',
        'objetivo' => 'Leia a cadeia de cima para baixo. Topo é o último inserido e o próximo a sair: LIFO, último a entrar, primeiro a sair.',
        'passos' => [
            ['titulo' => '1. Empilhar(10) e Empilhar(20)', 'texto' => '20 é o topo e aponta para 10. O Proximo de 10 é null.', 'nos' => [20, 10], 'rotulo' => 'TOPO → 20'],
            ['titulo' => '2. Ligar o novo nó 30', 'texto' => 'novo.Proximo = topo; liga 30 ao topo antigo (20). Ainda não atualizamos topo.', 'nos' => [20, 10], 'rotulo' => 'TOPO → 20', 'extra' => 'NOVO: [30 | Proximo → 20], preparado para entrar no topo.'],
            ['titulo' => '3. Atualizar topo', 'texto' => 'topo = novo; conclui Empilhar(30). A ordem das atribuições preserva a cadeia antiga.', 'nos' => [30, 20, 10], 'rotulo' => 'TOPO → 30'],
            ['titulo' => '4. Desempilhar()', 'texto' => 'Guarda topo.Valor (30), atualiza topo = topo.Proximo e devolve o valor salvo. O nó 20 passa a ser o topo.', 'nos' => [20, 10], 'rotulo' => 'TOPO → 20 · sai: 30'],
            ['titulo' => '5. Topo()', 'texto' => 'Consulta 20 sem retirar nenhum nó. A cadeia permanece igual à do passo anterior.', 'nos' => [20, 10], 'rotulo' => 'CONSULTA: 20 · sem remoção'],
            ['titulo' => '6. Remover até esvaziar', 'texto' => 'Ao desempilhar novamente, saem 20 e depois 10. O último Proximo é null, então topo também se torna null.', 'nos' => [], 'rotulo' => 'TOPO = null'],
        ],
        'conclusao' => 'Entrada: 10, 20, 30. Saída: 30, 20, 10. Push, Pop e Topo são O(1), sem percorrer a cadeia; o espaço é O(n).',
        'video' => ['id' => 'Xlkh6-10ILw', 'titulo' => 'Estruturas de Dados — Pilha (Lista Encadeada)', 'autor' => 'UNIVESP · Ulisses Martins Dias', 'orientacao' => 'Observe a inserção e a retirada na mesma extremidade. Relacione as referências do vídeo com o campo topo usado nos exemplos C#.', 'fonte' => 'https://www.youtube.com/watch?v=Xlkh6-10ILw'],
    ],
];
