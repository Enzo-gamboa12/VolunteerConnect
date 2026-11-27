<?php
require_once __DIR__ . '/init.php';
$instituicao = requireAccount($pdo, 'instituicao');
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) appError(404, 'Campanha não encontrada.');
$campanha = read($pdo, 'campanhas', 'id = ? AND id_instituicao = ?', [$id, (int)$instituicao['id']]);
if (!$campanha) appError(404, 'Campanha não encontrada ou não pertence à sua instituição.');
$erros = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $dados = ['nome'=>appText($_POST, 'nome_campanha')];
    foreach (['tipo','descricao','endereco','data_inicio','data_termino','horario','disponibilidade','status'] as $key) $dados[$key] = appText($_POST, $key);
    $total = filter_var($_POST['vagasTotais'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>2147483647]]);
    if ($dados['nome'] === '' || appLength($dados['nome']) > 100) $erros[] = 'Informe um nome com até 100 caracteres.';
    if ($dados['endereco'] === '' || appLength($dados['endereco']) > 200) $erros[] = 'Informe um endereço com até 200 caracteres.';
    if (strlen($dados['descricao']) > 65535) $erros[] = 'Descrição longa demais.';
    if (!in_array($dados['tipo'], ['Saúde','Tecnologia','Educação'], true)) $erros[] = 'Tipo de campanha inválido.';
    if (!in_array($dados['status'], ['Planejada','Ativa','Encerrada','Cancelada'], true)) $erros[] = 'Situação inválida.';
    if (!in_array($dados['disponibilidade'], ['Manhã','Tarde','Noite','Fim de Semana','Qualquer Horário'], true)) $erros[] = 'Disponibilidade inválida.';
    foreach (['data_inicio','data_termino'] as $key) {
        if ($dados[$key] !== '' && !appDateValid($dados[$key])) $erros[] = 'Data inválida.';
        if ($dados[$key] === '') $dados[$key] = null;
    }
    if ($dados['data_inicio'] && $dados['data_termino'] && $dados['data_termino'] < $dados['data_inicio']) $erros[] = 'O término não pode anteceder o início.';
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/D', $dados['horario'])) $erros[] = 'Horário inválido.';
    if ($total === false) $erros[] = 'Informe um total de vagas válido.';
    if (!$erros) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT * FROM campanhas WHERE id = ? AND id_instituicao = ? FOR UPDATE');
            $stmt->execute([$id, (int)$instituicao['id']]); $current = $stmt->fetch();
            if (!$current) throw new RuntimeException('Campanha não encontrada.');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_campanha = ? AND status <> 'Cancelado'");
            $stmt->execute([$id]);
            $used = max((int)$stmt->fetchColumn(), (int)$current['vagasTotais'] - (int)$current['vagas']);
            if ($total < $used) {
                $pdo->rollBack(); $erros[] = 'O total não pode ser menor que as vagas já ocupadas ou reservadas ('.$used.').';
            } else {
                $dados['vagasTotais'] = $total; $dados['vagas'] = $total - $used;
                update($pdo, 'campanhas', $dados, 'id = ? AND id_instituicao = ?', [$id, (int)$instituicao['id']]);
                $pdo->commit(); header('Location: painel-instituicao.php', true, 303); exit;
            }
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log((string)$error); $erros[] = 'Não foi possível salvar a campanha. Tente novamente.';
        }
    }
    $campanha = array_replace($campanha, $dados, ['vagasTotais'=>$_POST['vagasTotais'] ?? '']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Campanha | VolunteerConnect</title>
    <link rel="stylesheet" href="css/editar-campanha.css">
    <link rel="stylesheet" href="css/navigation.css">
    <script src="js/navigation.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/navigation.php'; ?>

    <main class="painel-principal">
        <aside class="menu-lateral">
            <div class="bloco-perfil">
                <span class="avatar-perfil"><?= indexEsc(indexIniciais($instituicao['nome'])) ?></span>
                <strong><?= indexEsc($instituicao['nome']) ?></strong>
            </div>

            <span class="rotulo-lateral">MENU PRINCIPAL</span>
            <a class="item-menu" href="painel-instituicao.php">Visão Geral</a>
            <a class="item-menu ativo" href="#">Editar Campanha</a>
            <form method="post" action="logout.php">
                    <?= csrfField() ?><button type="submit" class="item-menu">Sair</button></form>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="mailto:suporte@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Editar Campanha</h1>
                    <p><?= indexEsc($campanha['nome'] ?? '') ?></p>
                </div>
                <a class="botao-voltar" href="painel-instituicao.php">← Voltar</a>
            </header>

            <section class="cartao-formulario">
                <?php if ($erros): ?><div role="alert"><ul><?php foreach ($erros as $erro): ?><li><?= indexEsc($erro) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="grade-form">
                        <div class="grupo-campo">
                            <label for="nome_campanha">Nome da Campanha</label>
                            <input type="text" id="nome_campanha" name="nome_campanha" value="<?= indexEsc($campanha['nome'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="tipo">Área</label>
                            <select id="tipo" name="tipo" required>
                                <?php foreach (['Saúde','Tecnologia','Educação'] as $opcao): ?>
                                <option value="<?= indexEsc($opcao) ?>" <?= ($campanha['tipo'] ?? '') === $opcao ? 'selected' : '' ?>><?= indexEsc($opcao) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <label for="endereco">Local</label>
                            <input type="text" id="endereco" name="endereco" value="<?= indexEsc($campanha['endereco'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="status">Situação</label>
                            <select id="status" name="status">
                                <option value="Planejada" <?= strcasecmp(trim($campanha['status'] ?? ''), 'Planejada') === 0 ? 'selected' : '' ?>>Planejada</option>
                                <option value="Ativa" <?= strcasecmp(trim($campanha['status'] ?? ''), 'Ativa') === 0 ? 'selected' : '' ?>>Ativa</option>
                                <option value="Encerrada" <?= strcasecmp(trim($campanha['status'] ?? ''), 'Encerrada') === 0 ? 'selected' : '' ?>>Encerrada</option>
                                <option value="Cancelada" <?= strcasecmp(trim($campanha['status'] ?? ''), 'Cancelada') === 0 ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <label for="data_inicio">Data de Início</label>
                            <input type="date" id="data_inicio" name="data_inicio" value="<?= indexEsc($campanha['data_inicio']) ?>">
                        </div>

                        <div class="grupo-campo">
                            <label for="data_termino">Data de Término</label>
                            <input type="date" id="data_termino" name="data_termino" value="<?= indexEsc($campanha['data_termino']) ?>">
                        </div>

                        <div class="grupo-campo">
                            <label for="horario">Horário</label>
                            <input type="text" id="horario" name="horario" value="<?= indexEsc($campanha['horario'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="disponibilidade">Disponibilidade</label>
                            <input type="text" id="disponibilidade" name="disponibilidade" value="<?= indexEsc($campanha['disponibilidade'] ?? '') ?>">
                        </div>

                        <div class="grupo-campo">
                            <label for="vagasTotais">Total de Vagas</label>
                            <input type="number" id="vagasTotais" name="vagasTotais" min="1" value="<?= indexEsc($campanha['vagasTotais'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo campo-full">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="5" required><?= indexEsc($campanha['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="acoes-form">
                        <button type="submit" class="botao-principal">Salvar Alterações</button>
                        <a class="botao-secundario" href="painel-instituicao.php">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>