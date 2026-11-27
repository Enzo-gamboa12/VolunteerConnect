# Mudanças aplicadas

Base: `VolunteerConnect-teste.zip`. O arquivo original foi mantido intacto. Veja `ALTERACOES.json` para os nomes e hashes dos arquivos modificados.

## Integração e banco
- Nome padrão do banco unificado em PHP, Compose e exemplo de configuração.
- SQL inicial deixa de forçar um banco diferente do configurado no Docker.
- Datas criadas antes das views; removido o ALTER repetido de `data_termino`.
- Exemplos de campanhas passam a informar `tipo` obrigatório.
- Campos `categoria`, `id_instituicao`, datas, horário e endereço disponíveis na view de inscrições.
- Removidas as atualizações que substituíam todas as senhas por `12345`.
- Tabelas novas explicitamente InnoDB/utf8mb4; migração separada e limitada para banco existente.
- Driver PDO habilitado por um único mecanismo; healthcheck verifica uma consulta autenticada e trata a senha como argumento entre aspas.
- Removida a dependência de `mb_strlen`, que não era instalada no Docker original.

## Cadastros, login e permissões
- Um único controlador de cadastro; formulários antigos deixam de enviar campos incompatíveis a outro controlador.
- Valores de área/disponibilidade alinhados aos ENUMs, limites de comprimento, confirmação de senha e tratamento de duplicidade.
- Nova campanha usa exclusivamente a instituição autenticada; editor verifica propriedade antes de alterar.
- Senha informada deixa de ser comparada diretamente com um hash armazenado. Senhas legadas em texto puro são migradas após verificação.
- Sessão renovada no login, limpeza de dados de outra conta e validação de contas removidas.
- Proteção CSRF em todos os formulários POST. Logout muda de GET para POST.
- Entradas que deveriam ser texto rejeitam arrays; o filtro `area[]` continua aceito.
- Erros internos detalhados ficam no log, não na tela.
- Listagens antigas deixam de expor a base completa de voluntários publicamente.

## Campanhas e inscrições
- Filtro de oportunidades usa o tipo real e não é sobrescrito por outra consulta.
- Estado `Ativa` é reconhecido independentemente da caixa utilizada na comparação do PHP.
- Cards deixam de classificar todas as campanhas como Saúde.
- IDs inexistentes de oportunidade retornam 404, sem trocar silenciosamente para campanha 1.
- Inscrição exige conta de voluntário, confirmação e campanha ativa com vaga.
- Reserva de vagas e inscrição ocorrem na mesma transação com bloqueio da campanha.
- Repetições e reinscrição após cancelamento são tratadas sem duplicar o vínculo.
- Editor valida tipos, datas, disponibilidade, capacidade e mantém coerência das vagas ao alterar o total.
- Painel da instituição consulta por ID, não por nome; contagens deixam de somar vagas livres como pessoas.
- Tabela de campanhas com fechamento correto fora do foreach; campanhas planejadas ficam acessíveis para edição.
- Campos ausentes nos painéis corrigidos; datas nulas exibem “A definir”.
- Data fixa de participação removida. A data exibida passa a ser a data de início da campanha.
- Certificados não implementados aparecem como indisponíveis, em vez de um link que não gerava documento.

## Interface e rotas
- Navegação compartilhada em `app/partials/navigation.php`, `app/css/navigation.css` e `app/js/navigation.js`.
- Menu móvel com teclado e funcionamento básico sem JavaScript.
- Links para instituições/voluntários corrigidos; removida referência a `app.js` inexistente; contatos usam `mailto:`.
- Seção “Como funciona” passa a existir na página inicial.
- Nomes provenientes do banco são escapados onde eram exibidos diretamente.
- Estilos das áreas de conteúdo e arquivos de imagem originais preservados. Imagens não foram renderizadas nem homologadas nesta revisão.

## Limites e pontos ainda pendentes

Esta revisão não garante ausência de todos os bugs ou vulnerabilidades. Não houve execução PHP, MySQL, Docker nem navegador. As verificações de delimitadores PHP e inspeção textual SQL não equivalem a parsers completos. A migração precisa ser testada em cópia do banco antes de ser aplicada ao banco de uso.

Recursos novos como emissão de certificados, redefinição de senha, verificação de e-mail, administração de inscrições/horas, política de termos/privacidade e limitação distribuída de tentativas de login não foram implementados. A migração não converte automaticamente engines/collations de bancos antigos nem corrige todos os dados inconsistentes. Para uso público, faça auditoria de segurança, revisão dos dados exemplificativos, TLS e plano de backups.

O e-mail é único dentro de cada tabela. A verificação de duplicidade entre as duas tabelas é feita pela aplicação e não garante exclusividade cruzada em cadastros simultâneos; o login distingue os tipos de conta. Não foi feita uma reestruturação global de identidade.
