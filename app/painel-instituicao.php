<?php

require_once __DIR__ . "/init.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueia qualquer tipo de cache no navegador (inclusive cache do botão Voltar)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// VERIFICAÇÃO: Só permite entrar se estiver logado como voluntário
if (($_SESSION['tipo_conta'] ?? '') !== 'instituicao' || empty($_SESSION['id_instituicao'])) {
    header("Location: login.php?erro=acesso_negado");
    exit;
}

$idInstituicao = (int) $_SESSION['id_instituicao'];
$instituicao   = read($pdo, 'voluntarios', 'id = ?', [$idInstituicao]);

// Se o ID da sessão não existir mais no banco, encerra a sessão
if (!$instituicao) {
    header("Location: index.php");
    exit;
}

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
            <a href="oportunidades.php">Encontre Oportunidades</a>
            <a href="<?= $linkMeuPainel ?>">Meu Painel</a>
            <a href="sobre.php">Sobre Nós</a>
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

            <span class="rotulo-lateral">MENU PRINCIPAL</span>
            <a class="item-menu ativo" href="painel-instituicao.php">Visão Geral</a>
            <a class="item-menu" href="#campanhas-ativas">Atuais Campanhas</a>
            <a class="item-menu" href="#campanhas-finalizadas">Campanhas Encerradas</a>
            <a class="item-menu" href="#participantes">Participantes</a>
            <a class="item-menu" id="botao-sair" href="logout.php">Sair</a>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="suporte@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Olá, <?= $instituicao['nome'] ?>!</h1>
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
                                <td><a class="link-texto" href="editar-campanha.php?id=<?= $c['id_campanha'] ?>">Veja Mais</a></td>
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
                                    <th>Vagas Preenchidas</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campanhas_finalizadas as $cf): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($cf['nome_campanha']) ?></strong><small><?= htmlspecialchars($cf['area']) ?></small></td>
                                        <td><?= date('d/m/Y', strtotime($cf['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($cf['data_termino'])) ?></td>
                                        <td><?= $cf['vagas'] ?>/<?= $cf['vagasTotais'] ?></td>
                                        <td>
                                            <?php if ($cf['status'] === 'Encerrada'): ?>
                                                <span class="status status-encerrado">Encerrada</span>
                                            <?php elseif ($cf['status'] === 'Cancelada' || $cf['status'] === 'Cancelado'): ?>
                                                <span class="status status-cancelado">Cancelada</span>
                                            <?php else: ?>
                                                <span class="status status-encerrado"><?= htmlspecialchars($cf['status']) ?></span>
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
    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>