<?php
require_once __DIR__ . '/init.php';
$instituicao = requireAccount($pdo, 'instituicao');
$idInstituicao = (int)$instituicao['id'];
$proximas_participacoes = readAll($pdo, 'campanhas_por_instituicao', 'id_instituicao = ? AND status = ?', [$idInstituicao,'Planejada']);
$campanhas_ativas = readAll($pdo, 'campanhas_por_instituicao', 'id_instituicao = ? AND status = ?', [$idInstituicao,'Ativa']);
$campanhas_finalizadas = readAll($pdo, 'campanhas_por_instituicao', "id_instituicao = ? AND status IN ('Encerrada','Cancelada')", [$idInstituicao]);
$participantes_recentes = readAll($pdo, 'inscricoes_por_voluntario', "id_instituicao = ? AND status <> 'Cancelado' ORDER BY id_inscricao DESC LIMIT 20", [$idInstituicao]);
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT ins.id_voluntario) AS pessoas, COALESCE(SUM(ins.horas),0) AS horas FROM inscricoes ins INNER JOIN campanhas c ON c.id=ins.id_campanha WHERE c.id_instituicao=? AND ins.status <> 'Cancelado'");
$stmt->execute([$idInstituicao]); $totais = $stmt->fetch();
$voluntarios_inscritos = (int)$totais['pessoas']; $horas_doadas = (int)$totais['horas'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Instituição - VolunteerConnect</title>
    <link rel="stylesheet" href="css/painel-instituicao.css">
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
            <a class="item-menu ativo" href="painel-instituicao.php">Visão Geral</a>
            <a class="item-menu" href="#campanhas-ativas">Atuais Campanhas</a>
            <a class="item-menu" href="#campanhas-finalizadas">Campanhas Encerradas</a>
            <a class="item-menu" href="#participantes">Participantes</a>
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
                    <h1>Olá, <?= indexEsc($instituicao['nome']) ?>!</h1>
                    <p>Boas-vindas ao seu painel.</p>
                </div>
                <a class="botao-principal" href="campanha.php">+ Nova campanha</a>
            </header>

            <section class="grade-estatisticas">
                <article class="cartao-estatistica">
                    <small>Campanhas ativas</small>
                    <strong><?= count($campanhas_ativas) ?></strong>
                </article>
                <article class="cartao-estatistica">
                    <small>Voluntários inscritos</small>
                    <strong><?= $voluntarios_inscritos ?></strong>
                </article>
                <article class="cartao-estatistica">
                    <small>Horas doadas</small>
                    <strong><?= $horas_doadas ?> <small>h</small></strong>
                </article>
            </section>

            <section id="campanhas-planejadas"><h2>Campanhas planejadas</h2>
                <?php if (!$proximas_participacoes): ?><p>Nenhuma campanha planejada.</p><?php endif; ?>
                <?php foreach ($proximas_participacoes as $planejada): ?><p><a class="link-texto" href="editar-campanha.php?id=<?= (int)$planejada['id_campanha'] ?>"><?= indexEsc($planejada['nome_campanha']) ?> — Editar</a></p><?php endforeach; ?>
            </section>
            <section class="campanhas-ativas" id="campanhas-ativas">
                <header class="cabecalho-secao">
                    <div>
                        <h2>Campanhas Ativas</h2>
                    </div>
                </header>

                <div class="container-tabela">
                    <?php if (empty($campanhas_ativas)): ?>
                        <p>Não há nenhuma campanha ativa no momento.</p><br>
                        <a class='botao-principal' href='campanha.php'>+ Nova campanha</a>
                    <?php else: ?>
                        <table>
                        <thead>
                        <tr>
                        <th>Campanha</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Vagas disponíveis / total</th>
                        <th>Ações</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($campanhas_ativas as $c): ?>
                            <tr>
                                <td><strong><?= indexEsc($c['nome_campanha']) ?></strong><small><?= indexEsc($c['area']) ?></small></td>
                                <td><?= appDate($c['data_inicio']) ?> a <?= appDate($c['data_termino']) ?></td>
                                <td><?= indexEsc($c['horario']) ?></td>
                                <td><?= indexEsc($c['vagas']) ?>/<?= indexEsc($c['vagasTotais']) ?></td>
                                <td><a class="link-texto" href="editar-campanha.php?id=<?= indexEsc($c['id_campanha']) ?>">Veja Mais</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="campanhas-finalizadas" id="campanhas-finalizadas">
                <header class="cabecalho-secao">
                    <div>
                        <h2>Campanhas Finalizadas</h2>
                    </div>
                </header>
                
                <div class="container-tabela">
                    <?php if (empty($campanhas_finalizadas)): ?>
                        <p>Nenhuma campanha foi encerrada até o momento.</p><br>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Campanha</th>
                                    <th>Período</th>
                                    <th>Vagas disponíveis / total</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campanhas_finalizadas as $cf): ?>
                                    <tr>
                                        <td><strong><?= indexEsc($cf['nome_campanha']) ?></strong><small><?= indexEsc($cf['area']) ?></small></td>
                                        <td><?= appDate($cf['data_inicio']) ?> a <?= appDate($cf['data_termino']) ?></td>
                                        <td><?= indexEsc($cf['vagas']) ?>/<?= indexEsc($cf['vagasTotais']) ?></td>
                                        <td>
                                            <?php if ($cf['status'] === 'Encerrada'): ?>
                                                <span class="status status-encerrado">Encerrada</span>
                                            <?php elseif ($cf['status'] === 'Cancelada' || $cf['status'] === 'Cancelado'): ?>
                                                <span class="status status-cancelado">Cancelada</span>
                                            <?php else: ?>
                                                <span class="status status-encerrado"><?= indexEsc($cf['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>

            <section class="secao-participantes" id="participantes">
                <header class="cabecalho-secao">
                    <div>
                        <h2>Participantes Recentes</h2>
                    </div>
                </header>
                <?php if (empty($participantes_recentes)): ?>
                            <p>Nenhum participante recente em suas campanhas.</p><br>
                <?php else: ?>
                <ul class="lista-participantes">
                    <?php foreach ($participantes_recentes as $pr): ?>
                    <li class="item-participante">
                        <span class="avatar-pessoa"><?= indexEsc(indexIniciais($pr['nome_voluntario'])) ?></span>
                        <div>
                            <strong><?= indexEsc($pr['nome_voluntario']) ?></strong>
                            <small><?= indexEsc($pr['nome_campanha']) ?></small>
                        </div>
                        <span><?= indexEsc(appDate($pr['data_inicio'])) ?></span>
                        <span class="status status-andamento"><?= indexEsc($pr['status']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul> 
                   <?php endif ?>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>