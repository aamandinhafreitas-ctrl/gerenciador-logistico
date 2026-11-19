
# Gerenciador Logístico

## Sobre o projeto

O Gerenciador Logístico é um sistema web desenvolvido para auxiliar no controle e gerenciamento de operações logísticas, permitindo organizar produtos, endereços, transportadores e fretes em um único ambiente.

## Funcionalidades

- Cadastro, consulta e exclusão de produtos.
- Cadastro, consulta e exclusão de endereços.
- Cadastro, consulta e exclusão de transportadores.
- Cadastro, consulta e exclusão de fretes.
- Painel inicial com indicadores atualizados automaticamente.
- Relatórios de fretes.
- Exportação de relatórios em formato CSV.
- Impressão de relatórios.

## Tecnologias utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- Bootstrap 5
- XAMPP
- Visual Studio Code

## Banco de dados

O sistema utiliza o banco de dados `gerenciador_logistico`, composto pelas seguintes tabelas:

- produtos
- enderecos
- transportadores
- fretes

As tabelas estão relacionadas por meio de chaves estrangeiras.

## Como executar o projeto

1. Instale o XAMPP.
2. Inicie os serviços Apache e MySQL.
3. Copie a pasta do projeto para `C:\xampp\htdocs`.
4. Acesse o phpMyAdmin pelo navegador.
5. Crie o banco de dados `gerenciador_logistico`.
6. Execute os comandos SQL de criação das tabelas.
7. Confira as configurações de conexão no arquivo `conexao.php`.
8. Acesse pelo navegador:

   http://localhost/gerenciador_logistico/

## Estrutura do projeto

- `index.php` – Painel inicial.
- `produtos.php` – Gerenciamento de produtos.
- `enderecos.php` – Gerenciamento de endereços.
- `transportadores.php` – Gerenciamento de transportadores.
- `fretes.php` – Gerenciamento de fretes.
- `relatorios.php` – Relatórios do sistema.
- `exportar_fretes.php` – Exportação de fretes em CSV.
- `conexao.php` – Conexão com o banco de dados.

---

Projeto desenvolvido como atividade acadêmica.
