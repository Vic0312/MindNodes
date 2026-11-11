<?php
// Escopo informado pelo usuário: aulas 08, 09 e 10, professor Bruno Santos de Lima.
// Exemplos e convenções de interface são autorais; não transcrevem os materiais.
$arvores = [
    'binaria' => [
        'nome' => 'Árvore Binária',
        'resumo' => 'Hierarquia de nós com até dois filhos, identificados como esquerdo e direito.',
        'conceito' => 'Uma árvore organiza informações em uma hierarquia. A raiz é o nó inicial; cada ligação conecta um pai a um filho. Na árvore binária, cada nó pode ter uma subárvore esquerda e uma subárvore direita. Ter dois lados não significa que os valores estejam ordenados.',
        'caracteristicas' => ['Pai e filho indicam uma relação direta. Irmãos compartilham o mesmo pai.', 'Folhas são nós sem filhos. Cada filho pode ser a raiz de uma subárvore.', 'Níveis indicam a posição na hierarquia; altura descreve a extensão do caminho até as folhas. A contagem precisa de uma convenção explícita.', 'A representação aninhada descreve recursivamente o nó e suas duas subárvores.'],
        'operacoes' => ['Inserção: cria um nó e estabelece sua ligação na hierarquia. A posição depende da convenção utilizada; não decorre automaticamente do valor.', 'Remoção: retira um valor e precisa definir como preservar ou reorganizar as ligações restantes.', 'Visualização: permite reconhecer a raiz, as folhas, os níveis e as subárvores após cada operação. A busca foi acrescentada como recurso solicitado para o simulador.'],
        'exemplo' => 'No exemplo autoral, 10 é a raiz, 20 é seu filho esquerdo e 5 é seu filho direito. 20 e 5 são irmãos e folhas. A posição de 20 mostra que esta não é necessariamente uma árvore binária de busca.',
        'diagrama' => "10 · raiz\n├─ esquerda: 20 · folha\n└─ direita: 5 · folha",
        'revisao' => 'Uma árvore binária exige que o filho esquerdo seja menor? Não. Essa ordenação pertence a uma convenção adicional de árvore de busca.',
    ],
    'trie' => [
        'nome' => 'Árvore Trie',
        'resumo' => 'Palavras representadas por caminhos de caracteres que compartilham prefixos.',
        'conceito' => 'Uma Trie organiza palavras por seus caracteres. Ao partir da raiz e seguir as ligações de um caminho, reconstruímos uma sequência. Palavras com o mesmo início compartilham o trecho correspondente ao prefixo.',
        'caracteristicas' => ['Os caminhos representam sequências de caracteres.', 'Um prefixo compartilhado evita repetir o mesmo caminho inicial.', 'É preciso distinguir uma palavra completa de um simples prefixo, inclusive quando uma palavra é prefixo de outra.'],
        'operacoes' => ['Inserção: acompanha o prefixo já existente e acrescenta o trecho necessário para representar a palavra.', 'Busca: acompanha os caracteres da palavra; um caminho de prefixo sozinho não comprova que a palavra inteira foi armazenada.', 'Remoção: retira a palavra preservando os caminhos ainda necessários às outras palavras. Os detalhes de poda serão confirmados antes da implementação.', 'Visualização: destaca o caminho correspondente aos caracteres percorridos.'],
        'exemplo' => 'No exemplo autoral, CASA e CASO compartilham C → A → S e seguem por A e O. O símbolo * indica fim de palavra apenas neste desenho.',
        'diagrama' => "raiz\n└─ C → A → S\n             ├─ A* (CASA)\n             └─ O* (CASO)",
        'revisao' => 'Encontrar um prefixo é o mesmo que encontrar uma palavra? Não: o caminho precisa representar também o término da palavra procurada.',
    ],
    'patricia' => [
        'nome' => 'Árvore Patricia',
        'resumo' => 'Trie compactada que reúne segmentos de palavras em caminhos.',
        'conceito' => 'A Patricia estudada neste módulo é uma Trie compactada. Em vez de mostrar cada caractere em uma etapa separada, reúne segmentos de palavras em caminhos, preservando os prefixos compartilhados e as ramificações necessárias.',
        'caracteristicas' => ['As ligações podem representar segmentos com vários caracteres.', 'Prefixos comuns continuam compartilhados.', 'A compactação reduz caminhos intermediários sem eliminar as distinções entre as palavras armazenadas.'],
        'operacoes' => ['Inserção: compara os segmentos e o prefixo comum; uma divergência pode exigir dividir um segmento para criar uma ramificação.', 'Busca: acompanha segmentos compatíveis com a palavra e verifica seu término.', 'Remoção: retira a palavra e verifica se caminhos podem voltar a ser compactados. Critérios exatos de fusão e marcação de término ainda precisam de confirmação.', 'Visualização: mostra os segmentos e a estrutura resultante das alterações.'],
        'exemplo' => 'CASA e CASO podem compartilhar CAS, com ramificações A e O. Este é um esquema autoral de prefixos, não uma confirmação da representação gráfica usada pelo professor.',
        'diagrama' => "raiz\n└─ CAS\n   ├─ A* (CASA)\n   └─ O* (CASO)",
        'revisao' => 'O que a compactação preserva? As palavras, seus prefixos compartilhados e as ramificações que distinguem seus caminhos.',
    ],
    'avl' => [
        'nome' => 'Árvore AVL',
        'resumo' => 'Árvore binária balanceada com verificação do equilíbrio e rotações.',
        'conceito' => 'A AVL mantém o equilíbrio de uma estrutura binária por meio da verificação do balanceamento e de rotações. Após uma inserção, é necessário observar os caminhos afetados e, quando aplicável, reorganizar as ligações para recuperar o equilíbrio.',
        'caracteristicas' => ['O balanceamento é verificado após a inserção.', 'Rotações alteram ligações entre nós e podem mudar a raiz de uma subárvore.', 'A comparação visual antes e depois ajuda a explicar a reorganização.'],
        'operacoes' => ['Inserção: acrescenta o valor seguindo as regras que serão confirmadas para este simulador.', 'Verificação do balanceamento: analisa o equilíbrio das subárvores afetadas. A convenção de altura e o sinal do fator não foram informados.', 'Rotações: reorganizam a estrutura para manter o equilíbrio. A nomenclatura e os critérios dos casos serão confirmados antes de programar as operações.', 'Visualização: compara a árvore antes e depois das rotações. Remoção não faz parte do escopo AVL informado nesta entrega.'],
        'exemplo' => 'O esquema autoral mostra uma mudança de ligações: B ocupa o lugar de A, A passa a ser filho esquerdo de B e C permanece à direita. Ele ilustra uma rotação, sem estabelecer os critérios de inserção ou de balanceamento da disciplina.',
        'diagrama' => "Antes                 Depois\nA                       B\n └─ direita: B         /   \\\n     └─ direita: C    A     C",
        'revisao' => 'Uma rotação apenas muda a aparência? Não. Ela muda referências entre nós; o desenho precisa refletir a estrutura resultante.',
    ],
];
