# Manual de Instalação do MindNodes

Este manual descreve a instalação local do MindNodes. O projeto usa PHP e MySQL/MariaDB; os exemplos C# são conteúdo didático exibido no navegador.

## 1. Requisitos

- Servidor web Apache com PHP **8.0 ou superior**. O código usa `str_contains` e `str_starts_with`, disponíveis a partir do PHP 8.0; o ambiente local testado usa PHP 8.2.0.
- Extensão PHP `mysqli` com `mysqlnd` habilitada. O sistema usa `mysqli_stmt_get_result`; não usa PDO para a conexão da aplicação.
- MySQL ou MariaDB. O dump foi gerado no MariaDB 10.4.27 e a importação limpa foi validada em um banco temporário no ambiente local.
- Sessões PHP habilitadas e um navegador atual.
- Para enviar foto de perfil, o processo do servidor precisa conseguir escrever em `uploads/usuarios/`. O sistema cria essa subpasta quando necessário.

O XAMPP reúne Apache, PHP e MariaDB e é adequado para o ambiente Windows deste projeto. Composer, npm, Node.js e .NET **não são necessários para executar o site**. Alguns testes de desenvolvimento usam Node.js, Chrome ou .NET para verificar navegador e exemplos C#.

## 2. Obter e posicionar os arquivos

No Windows com XAMPP, coloque a pasta completa em:

```text
C:\xampp\htdocs\MindNodes
```

É possível clonar o endereço configurado como `origin` no projeto a partir de `C:\xampp\htdocs` (é necessário ter acesso ao repositório, caso ele seja privado):

```powershell
git clone https://github.com/Vic0312/MindNodes.git
```

Também é possível baixar o ZIP do repositório no GitHub, extrair seu conteúdo e nomear a pasta resultante `MindNodes` dentro de `C:\xampp\htdocs`. Confira que `index.php`, `mindnode.sql`, `config/`, `view/`, `css/`, `js/` e `img/` estejam diretamente dentro de `MindNodes`, sem uma pasta extra intermediária.

Se usar outro diretório servido pelo Apache ou outro nome de pasta, o endereço de acesso muda conforme a configuração do servidor. As páginas usam caminhos relativos; não há dependência de `mod_rewrite` ou arquivo `.htaccess`.

## 3. Criar e importar o banco

1. Inicie **Apache** e **MySQL** no painel do XAMPP.
2. Abra o phpMyAdmin do XAMPP (normalmente **http://localhost/phpmyadmin/** quando o Apache usa a porta padrão).
3. Crie um banco de dados chamado **`mindnode`**, preferencialmente com conjunto de caracteres `utf8mb4`.
4. **Selecione `mindnode`** na barra lateral do phpMyAdmin.
5. Na aba **Importar**, escolha `C:\xampp\htdocs\MindNodes\mindnode.sql` e execute a importação.

O arquivo `mindnode.sql` **não contém `CREATE DATABASE` nem `USE`**. Por isso, criar e selecionar `mindnode` antes da importação é obrigatório. Não importe o dump por cima de um banco já preenchido: ele contém `CREATE TABLE` e dados iniciais, e foi validado para instalação limpa.

A importação cria dez tabelas: `usuario`, `item`, `usuario_item`, `avatar_usuario`, `transacao_moeda`, `quiz_assunto`, `quiz_pergunta`, `quiz_alternativa`, `quiz_tentativa` e `quiz_resposta`. O dump já inclui seis assuntos, perguntas e alternativas, seis itens do catálogo e alguns registros históricos de exemplo. **Crie uma conta nova para testar o primeiro acesso**; não dependa das contas de exemplo presentes no dump.

## 4. Configurar a conexão

O arquivo [config/Conexao.php](config/Conexao.php) centraliza a conexão. Os valores atuais são:

| Configuração | Valor no projeto |
| --- | --- |
| Host | `localhost` |
| Usuário MySQL | `root` |
| Senha MySQL | vazia |
| Banco | `mindnode` |

Esses valores correspondem à configuração local padrão usada no projeto. Se seu MySQL/MariaDB usar credenciais diferentes, ajuste as constantes `HOST`, `USUARIO`, `SENHA` e `BANCO` nesse arquivo para o seu ambiente. Não publique suas credenciais pessoais no repositório.

Há uma variável `MINDNODES_DB` usada pelos testes para apontar a conexão para bancos temporários. Na instalação normal, não é preciso defini-la. Se estiver definida no ambiente do servidor, ela substitui o nome `mindnode` configurado no arquivo.

## 5. Abrir o sistema e criar uma conta

Com Apache e MySQL/MariaDB ativos, acesse **http://localhost/MindNodes/**. O [index.php](index.php) redireciona para `view/home.php`, a Home real da aplicação. Se o Apache não usar a porta padrão, inclua sua porta no endereço; se a pasta tiver outro nome, use esse nome na URL.

Na Home, abra **Cadastro**, preencha seus próprios dados e crie uma senha. Depois, faça login. Não use CPF, e-mail ou senha pessoais em um ambiente de demonstração compartilhado. O formulário pode receber foto de perfil, mas ela não é necessária para começar.

Depois do login, o fluxo de apresentação é: abrir um conteúdo, consultar um exemplo C#, fazer um Quiz, receber moedas, visitar a Loja, comprar um item, equipá-lo no Avatar e consultar o Desempenho.

## 6. Verificação rápida

Confirme no navegador:

1. A Home abre em `http://localhost/MindNodes/`.
2. Cadastro cria uma conta nova, login funciona e o perfil abre.
3. Conteúdos e exemplos C# carregam.
4. O Quiz apresenta perguntas e registra uma tentativa finalizada.
5. Loja, Avatar e Desempenho abrem após o login.
6. No phpMyAdmin, a nova conta aparece na tabela `usuario` do banco `mindnode`.

O cadastro concede os itens iniciais e cria o Avatar padrão pelo fluxo da aplicação. Não é necessário editar tabelas manualmente para isso.

## 7. Teste automatizado opcional para desenvolvimento

Na raiz do projeto, com PHP CLI e MySQL/MariaDB local disponíveis:

```powershell
php tests/instalacao.php
```

Esse script cria um banco temporário, importa `mindnode.sql`, verifica tabelas, dados iniciais, chaves únicas e estrangeiras e remove o banco temporário ao terminar. **Não executa alterações no banco `mindnode`**, mas o usuário MySQL empregado no teste precisa de permissão para criar e remover bancos. O teste usa `127.0.0.1:3306`, usuário `root` e senha vazia por padrão; as variáveis `MINDNODES_TEST_USER`, `MINDNODES_TEST_PASSWORD` e `MINDNODES_TEST_PORT` permitem ajustar apenas a conexão do teste.

Os demais scripts em `tests/` são testes de desenvolvimento, não parte da instalação. Alguns exigem Node.js, Chrome ou .NET; consulte cada script antes de executá-lo.

## 8. Problemas comuns

| Sintoma | Verificação |
| --- | --- |
| Apache não inicia | Verifique no painel do XAMPP se a porta configurada para o Apache já está em uso e consulte o log do Apache. |
| MySQL/MariaDB não inicia | Verifique a porta em uso e o log do MySQL no XAMPP. Não apague a pasta de dados para tentar corrigir a falha. |
| “Banco de dados não existe” ou falha de conexão | Confirme que MySQL/MariaDB está ativo, que `mindnode` foi criado e selecionado na importação e que `config/Conexao.php` contém host, usuário, senha e banco corretos. Confira também se `MINDNODES_DB` foi definido no ambiente por engano. |
| Erro ao importar tabelas já existentes | Use um banco `mindnode` vazio para a instalação limpa. Não repita a importação sobre dados que deseja preservar. |
| Erro 404 | Confirme que `index.php` está diretamente em `C:\xampp\htdocs\MindNodes` e que a URL usa o nome real da pasta e a porta do Apache. |
| CSS, JavaScript ou imagens não carregam | Confira se as pastas `css/`, `js/` e `img/` foram copiadas integralmente e se a URL da pasta está correta. |
| Foto de perfil não é salva | Verifique se o servidor pode escrever em `uploads/usuarios/` e se o formato enviado é aceito pelo formulário. |
| Página indica falta de função `mysqli` | Habilite `mysqli` e `mysqlnd` no PHP usado pelo Apache; o PHP de linha de comando pode ter configuração diferente. |

## 9. Observações de entrega

O dump inclui usuários de exemplo legados com senhas antigas. Eles não são contas oficiais de demonstração; para apresentar cadastro e armazenamento seguro de senha, use uma conta nova. Revise esses usuários antes de expor a instalação fora de um ambiente local de ensino. O conteúdo didático e os diagramas funcionam sem .NET. Fontes de ícones e vídeos complementares, quando presentes, dependem da disponibilidade de serviços externos; o fluxo principal do site não depende deles.
