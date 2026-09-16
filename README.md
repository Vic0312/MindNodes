# MindNodes

O MindNodes é uma aplicação web educacional para estudar Estruturas de Dados. Reúne conteúdos e diagramas, exemplos de código C#, Quiz com questões teóricas e de código e um sistema de moedas usado na Loja e na personalização do Avatar.

## Conteúdos

- TAD — Tipo Abstrato de Dados;
- Lista Simplesmente Encadeada;
- Lista Duplamente Encadeada;
- Fila Encadeada FIFO;
- Fila de Prioridades Encadeada FIFO;
- Pilha Encadeada.

## Recursos

Cadastro, login, recuperação de senha e perfil; aulas e exemplos em C#; Quiz com Dica, Eliminar alternativa e Resumo rápido quando os itens correspondentes estão equipados; moedas, Loja, inventário, Avatar, desempenho e histórico de tentativas.

Os exemplos C# são exibidos para estudo. O site é executado em PHP; **.NET não é necessário para utilizá-lo**.

## Tecnologias e organização

PHP 8 com `mysqli`, MySQL/MariaDB, HTML, CSS e JavaScript. O projeto foi testado localmente com PHP 8.2.0 e MariaDB. Não há instalação de pacotes com Composer ou npm para executar a aplicação.

```text
MindNodes/
├── config/          conexão com o banco
├── controller/      coordenação das requisições
├── model/           dados e regras
├── view/            páginas e componentes visuais
├── processamento/   ações de formulários e logout
├── css/             estilos
├── js/              scripts do navegador
├── img/             imagens e camadas do Avatar
├── uploads/         fotos de perfil enviadas pelo usuário
├── tests/           testes para desenvolvimento
├── index.php        entrada que redireciona para a Home
└── mindnode.sql     estrutura e dados iniciais do banco
```

Na organização MVC, os Models tratam dados e regras, os Controllers coordenam as operações e as Views apresentam a interface. `config/Conexao.php` centraliza a conexão MySQL.

## Instalação rápida

1. Tenha Apache, PHP 8 ou superior com `mysqli`/`mysqlnd` e MySQL ou MariaDB em funcionamento. XAMPP é uma opção compatível.
2. Coloque o projeto em `C:\xampp\htdocs\MindNodes` no XAMPP, ou no diretório servido pelo seu Apache.
3. No phpMyAdmin, crie o banco **`mindnode`** e selecione-o. Importe [mindnode.sql](mindnode.sql) nele. O arquivo não cria nem seleciona o banco automaticamente.
4. Confira host, usuário, senha e nome do banco em [config/Conexao.php](config/Conexao.php). Os valores atuais atendem a uma instalação XAMPP local com MySQL/MariaDB em `localhost`, usuário `root`, senha vazia e banco `mindnode`.
5. Com Apache e MySQL/MariaDB ativos, abra **http://localhost/MindNodes/** e crie sua própria conta pelo link **Cadastro**.

Se a pasta tiver outro nome ou o Apache usar outra porta, ajuste o endereço no navegador. A aplicação usa caminhos relativos; não exige `mod_rewrite`.

**Leia o [Manual de Instalação](MANUAL_INSTALACAO.md)** para o procedimento completo, primeiro acesso, testes e solução de problemas.

## Desenvolvimento

O teste de importação limpa usa um banco temporário e não altera o banco `mindnode`:

```powershell
php tests/instalacao.php
```

Ele exige PHP CLI com `mysqli` e permissão para criar e remover um banco de teste. Outros testes estão em `tests/` e podem exigir Chrome, Node.js ou .NET apenas para sua própria execução; essas ferramentas não são requisitos para usar o site.
