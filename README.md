# VolunteerConnect — revisão corretiva

Projeto PHP/Apache/MySQL com correções de integração, autenticação, navegação e formulários. Comece por este arquivo; a lista de mudanças está em `CORRECOES.md`.

**Não é uma versão totalmente homologada.** Foram feitas verificações estáticas. PHP, MySQL, Docker e navegador não foram executados durante esta revisão. Antes de publicar, faça os testes de `tests/TESTES_MANUAIS.md`.

## Computador novo, sem banco: instalação nova com Docker

Requisitos: Docker Desktop (ou Docker Engine) com Docker Compose. O container usa PHP 8.4 e MySQL 8.4.

1. Extraia o ZIP em uma pasta nova. Não substitua seu único backup.
2. Copie `.env.example` para `.env` (pode usar o Explorador do Windows).
3. Edite `.env`: defina `DB_PASSWORD` e `DB_ROOT_PASSWORD` com senhas fortes e diferentes. Não mantenha os textos de exemplo.
4. No terminal, dentro da pasta que contém `compose.yaml`, execute:

   ```sh
   docker compose up -d --build
   docker compose ps
   ```

5. Abra `http://localhost:8080`. Se a porta estiver ocupada, altere `APP_PORT` no `.env` e recrie os containers sem apagar volumes.
6. Crie suas próprias contas de voluntário e instituição pela interface. Os registros ilustrativos do SQL **não possuem senha utilizável**.

O esquema é importado automaticamente **somente ao criar um volume de banco vazio**. A aplicação completa continua dependendo de PHP e MySQL: não abra os `.php` com duplo clique.

### Sem Docker

Use PHP 8.1 ou superior com PDO e `pdo_mysql` habilitados, e MySQL 8.x com tabelas InnoDB. O ambiente de referência fornecido é PHP 8.4/MySQL 8.4. Aponte o DocumentRoot do servidor para `app/`.

Crie um banco vazio `volunteerconnect` com UTF-8 e importe `database/schema.sql` **dentro desse banco selecionado**. Configure as variáveis `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no ambiente do servidor PHP. Fora do Docker, o arquivo `.env` não é lido automaticamente. Não coloque senhas no Git.

## Se você já tem dados: não reimporte schema.sql

1. Faça backup do banco e do projeto. Preserve seu `.env` e o volume existente.
2. **Não altere automaticamente DB_DATABASE, DB_USERNAME ou senhas para os novos exemplos.** O nome real pode ser `integrador`, `volunteerconnect` ou outro. A aplicação deve apontar para o banco que contém seus dados; nenhum script renomeia bancos.
3. A partir de um cliente MySQL, selecione explicitamente esse banco e revise/aplique `database/migrate_existing.sql` com uma conta autorizada a alterar colunas, criar procedures e views.
4. A migração cria apenas as colunas de datas/tipo se estiverem ausentes e recria as views usadas pelos painéis. Não troca senhas, não apaga registros e não carrega exemplos. O script pressupõe as quatro tabelas do projeto; não repara uma importação interrompida que tenha deixado tabelas faltando.
5. Se o tipo de uma campanha estava ausente, classifique-o pela tela de edição da própria instituição. Valores de tipos e estados incompatíveis com o esquema exigem revisão manual antes de publicar.
6. MySQL faz commit implícito de DDL: **backup é obrigatório**, não conte com rollback para desfazer uma migração.
7. Copie os arquivos corrigidos e reinicie/reconstrua a aplicação. Faça novo login e recarregue os formulários; os POSTs agora exigem token CSRF.

**Não use `docker compose down -v` para atualizar este projeto:** isso elimina o volume do banco. Alterar a senha no `.env` também não altera a senha já armazenada em um volume existente.

Se a importação anterior falhou, primeiro faça backup e examine quais tabelas existem. Não tente consertar isso apagando o volume automaticamente. Em um computador realmente sem dados, use uma instalação nova independente.

## Regras agora consistentes

- `cadastro.php` é o cadastro canônico. URLs antigas de cadastro encaminham para ele.
- Campanhas só podem ser criadas/editadas por sua instituição autenticada.
- Inscrições usam o ID do voluntário na sessão; informar o e-mail de outra pessoa não permite inscrevê-la.
- `vagas` representa vagas disponíveis; `vagasTotais` representa capacidade. Uma inscrição reserva uma vaga em transação. Reservas manuais anteriores são preservadas na edição do total.
- Contagens de pessoas e horas vêm das inscrições, não da quantidade de vagas livres.
- Inscrições canceladas podem ser reativadas se houver vaga; não geram uma segunda linha para o mesmo par campanha/voluntário.
- A lista de voluntários exige login: instituição vê participantes de suas próprias campanhas; voluntário vê apenas sua conta. CPF e endereço não aparecem nessa listagem.
- Senhas antigas em texto puro são convertidas para hash após login válido. Isso é compatibilidade temporária: contas com senhas fracas devem ser revisadas antes de uso público. Senhas com espaços são preservadas no cadastro e login novos.
- Todas as 18 páginas visuais usam o mesmo componente de navegação; aliases reutilizam essas páginas.
- Sem banco, páginas dependentes dele retornam erro controlado; a página inicial apresenta o aviso de indisponibilidade, não dados demonstrativos autenticados.

## Verificações

- Relatório da revisão: `tests/static_results.json`.
- Checagem estática reproduzível: `python tests/static_review.py` (Node.js opcional para `node --check`; PyYAML opcional para examinar Compose).
- Teste isolado do menu novo: `node tests/navigation_test.js`.
- Roteiro de validação real: `tests/TESTES_MANUAIS.md`.

A checagem estrutural de PHP não substitui `php -l`. Dentro do ambiente configurado, antes de publicar:

```sh
docker compose exec app sh -c 'find /var/www/html -name "*.php" -exec php -l {} \;'
docker compose logs --tail=100 app db
```

Não compartilhe logs que exponham dados pessoais, senhas ou informações de conexão.
