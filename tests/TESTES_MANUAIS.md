# Validação obrigatória no ambiente real

Execute em uma cópia local de testes, nunca usando dados pessoais reais. Este roteiro não foi executado nesta revisão.

1. **Lint:** rode `php -l` em todos os arquivos. Nenhum erro de sintaxe.
2. **Banco novo:** crie um ambiente/volume separado e vazio; confirme as quatro tabelas e as quatro views. Inspecione as colunas de datas e tipo. Não reutilize/limpe o único volume que contém seus dados.
3. **Migração:** em uma cópia do banco existente, selecione o banco correto e aplique `database/migrate_existing.sql` duas vezes. Deve terminar sem duplicar colunas. Compare quantidade de registros e hashes/senhas antes/depois (sem expô-los). Valide InnoDB nas tabelas antes de testar concorrência.
4. **Configuração:** confira que PHP e MySQL usam o mesmo banco. Teste senhas de banco contendo caracteres especiais. Interromper o banco deve produzir erro controlado, sem expor credenciais/stack trace.
5. **Cadastro:** voluntário e instituição com áreas acentuadas; senha com espaços nas pontas; confirmação diferente; e-mail repetido; campos maiores que os limites; arrays enviados onde se espera texto. Sucessos persistem e erros não criam registros.
6. **Login:** credenciais válidas/erradas; trocar tipo de conta; sessão de conta removida; login legado em cópia de teste. Após login legado correto, a senha fica em hash. O próprio hash não deve funcionar como senha.
7. **CSRF:** POST sem token ou com token alterado deve retornar 403 e não gravar nada. Reabrir o formulário permite tentar novamente. Logout por GET não encerra sessão; botão Sair funciona via POST.
8. **Permissões:** sem login não se cria/edita campanha nem se consulta lista de voluntários. Instituição A não edita campanha de B, inclusive alterando o ID da URL ou campos enviados.
9. **Campanhas:** criar e editar cada tipo permitido; datas vazias/válidas/invertidas; horário inválido; total abaixo das vagas ocupadas; capacidade aumentada. Reabrir a edição confirma os dados salvos. Campanhas planejadas aparecem no painel.
10. **Oportunidades:** filtrar Educação, Saúde e Tecnologia individualmente, múltiplas áreas, texto e nenhum resultado. Inativas não aparecem na busca. Detalhe inválido retorna 404.
11. **Inscrições:** conta voluntária válida; instituição tentando se inscrever; tentativa sem vaga/inativa; clique duplo; reenvio do mesmo POST; par cancelado. Não há duplicatas e a disponibilidade não fica negativa.
12. **Concorrência real:** em campanha de teste com uma vaga, dois voluntários enviam inscrições quase simultaneamente. Apenas um consegue; confira a tabela e a disponibilidade. Teste também edição da capacidade enquanto outra sessão se inscreve. A verificação estática não substitui este teste.
13. **Painéis:** instituição vê apenas seus participantes; voluntário vê apenas suas inscrições. Compare pessoas e horas com consultas no banco. Teste múltiplas campanhas na tabela e datas nulas.
14. **Interface:** telas com larguras de 320, 768 e 1366 px, menu mobile e teclado/Escape, login/logout, imagens, links e impressão. Confirme se os arquivos de imagem originais funcionam nos navegadores de destino.
15. **Produção:** rever senhas fracas/legadas, exemplos do SQL, TLS/cookies seguros no proxy, termos de uso/privacidade, backup e restauração. Certificados estão explicitamente indisponíveis.
