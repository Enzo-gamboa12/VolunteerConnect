<?php

require_once __DIR__ . '/init.php';

$instituicao = read($pdo, 'instituicoes', 'id = ?', [$_SESSION['id_instituicao']]);
$proximas_participacoes = readAll($pdo, 'campanhas_por_instituicao',  'id_instituicao = ? AND status = ?', [$_SESSION['id_instituicao'], 'Planejada']);
$campanhas_ativas = readAll($pdo, 'campanhas_por_instituicao',  'id_instituicao = ? AND status = ?', [$_SESSION['id_instituicao'], 'Ativa']);
$campanhas_finalizadas = readAll($pdo, 'campanhas_por_instituicao',  'id_instituicao = ? AND status = ?', [$_SESSION['id_instituicao'], 'Encerrada']);
$participantes_recentes = readAll($pdo, 'inscricoes_por_voluntario', 'nome_instituicao = ?', [$_SESSION['nome_instituicao']]);
$voluntarios_inscritos = array_sum(array_column($campanhas_ativas, 'vagas'));
$horas_doadas = array_sum(array_merge(array_column($campanhas_finalizadas, 'horas'), array_column($campanhas_ativas, 'horas')));
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Instituição - VolunteerConnect</title>
    <link rel="stylesheet" href="css/painel-instituicao.css">
</head>

<body>
    <header class="cabecalho">
        <a class="link-logo" href="index.php">
            <img class="logo" src="imagens/logo-volunteerconnect.png" alt="Logo VolunteerConnect">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <nav class="menu">
            <a href="inicio.php">Início</a>
            <a href="sobre.php">Sobre Nós</a>
            <a href="index.php#como-funciona">Como funciona</a>
        </nav>

        <a href="painel-instituicao.php">
            <div class="menu-usuario">
                <span class="avatar-usuario"><?= indexIniciais($instituicao['nome']) ?></span>
                <span><?= $instituicao['nome']; ?></span>
            </div>
        </a>
    </header>

    <main class="painel-principal">
        <aside class="menu-lateral">
            <div class="bloco-perfil">
                <span class="avatar-perfil"><?= indexIniciais($instituicao['nome']) ?></span>
                <strong><?= $instituicao['nome'] ?></strong>
            </div>

            <span class="rotulo-lateral">GESTÃO</span>
            <a class="item-menu ativo" href="painel-instituicao.php">Visão Geral</a>
            <a class="item-menu" href="#campanhas-ativas">Atuais Campanhas</a>
            <a class="item-menu" href="#campanhas-finalizadas">Campanhas Encerradas</a>
            <a class="item-menu" href="#participantes">Participantes</a>

            <span class="rotulo-lateral">CONTA</span>
            <a class="item-menu" href="cadastro.php">Dados da Instituição</a>
            <a class="item-menu" href="logout.php">Sair</a>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="mailto:ajuda@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Olá, <?= $instituicao['nome'] ?>!</h1>
                    <p>Boas-vindas ao seu painel.</p>
                </div>
                <a class="botao-principal" href="cadastro.php#nova-campanha">+ Nova campanha</a>
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

            <section class="campanhas-ativas" id="campanhas-ativas">
                <header class="cabecalho-secao">
                    <div>
                        <h2>Campanhas Ativas</h2>
                    </div>
                </header>

                <div class="container-tabela">
                    <?php if (empty($campanhas_ativas)): ?>
                        <p>Não há nenhuma campanha ativa no momento.</p><br>
                        <a class='botao-principal' href='cadastro.php#nova-campanha'>+ Nova campanha</a>
                    <?php else: ?>
                        <table>
                        <thead>
                        <tr>
                        <th>Campanha</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Vagas</th>
                        <th>Ações</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($campanhas_ativas as $c): ?>
                            <tr>
                                <td><strong><?= $c['nome_campanha']?></strong><small><?= $c['area']?></small></td>
                                <td><?= date('d/m/Y', strtotime($c['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($c['data_termino'])) ?></td>
                                <td><?= $c['horario']?></td>
                                <td><?= $c['vagas']?>/<?= $c['vagasTotais']?></td>
                                <td><a href="detalhes.php?id=<?= $c['id_campanha']?>">Ver mais</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endforeach;
                    endif; ?>
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
                            <th>Vagas</th>
                            <th>Situação</th>
                            <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>"

                            <?php foreach ($campanhas_finalizadas as $cf): ?>
                                <tr>;
                                <td><strong><?= $cf['nome_campanha'] ?></strong><small><?= $cf['area'] ?></small></td>;
                                <td><?= $cf['data_inicio'] ?> a <?= $cf['data_fim'] ?></td>;
                                <td><?= $cf['vagas_ocupadas']?>/<?= $cf['vagas_totais'] ?></td>;
                                <td><?php if (['status'] == 'Encerrado'): ?><span class="status status-encerrado">Encerrada</span>
                                    <?php elseif (['status'] == 'Cancelado'): ?><span class="status status-cancelado">Cancelado</span></td>;
                                    <?php endif ?>
                                <td><a href="#campanhas">Relatório Final</a></td>
                                </tr>;
                            </tbody>;
                        </table>;
                        <?php endforeach;
                        endif; ?>
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
                        <span class="avatar-pessoa"><?= indexIniciais($pr['nome_voluntario']) ?></span>
                        <div>
                            <strong><?= $pr['nome_voluntario'] ?></strong>
                            <small><?= $pr['nome_campanha'] ?></small>
                        </div>
                        <time datetime="2026-11-07">07/11/2026</time>
                        <span class="status status-andamento"><?= $pr['status'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul> 
                   <?php endif ?>
            </section>
        </div>
    </main>
</body>

</html>