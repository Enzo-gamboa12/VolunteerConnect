<?php

require_once __DIR__ . '/init.php';

$instituicao = read($pdo, 'instituicoes', 'id = ?', [$_SESSION['id_instituicao']]);
$campanha = read($pdo, 'campanhas_por_instituicao', 'id_campanha = ? AND id_instituicao = ?', [$_GET['id'], $_SESSION['id_instituicao']]);

if (!$campanha) {
    header("Location: painel-instituicao.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome_campanha' => $_POST['nome_campanha'],
        'area'          => $_POST['area'],
        'descricao'     => $_POST['descricao'],
        'local'         => $_POST['local'],
        'data_inicio'   => $_POST['data_inicio'],
        'data_termino'  => $_POST['data_termino'],
        'horario'       => $_POST['horario'],
        'vagasTotais'   => $_POST['vagasTotais'],
        'horas'         => $_POST['horas'],
        'status'        => $_POST['status']
    ];

    update($pdo, 'campanhas', $dados, 'id = ? AND id_instituicao = ?', [$_GET['id'], $_SESSION['id_instituicao']]);

    header("Location: painel-instituicao.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Campanha | VolunteerConnect</title>
    <link rel="stylesheet" href="css/editar-campanha.css">
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
                <span><?= $instituicao['nome'] ?></span>
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
            <a class="item-menu" href="painel-instituicao.php">Visão Geral</a>
            <a class="item-menu ativo" href="#">Editar Campanha</a>
            <a class="item-menu" id="botao-sair" href="logout.php">Sair</a>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="suporte@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Editar Campanha</h1>
                    <p><?= $campanha['nome_campanha'] ?></p>
                </div>
                <a class="botao-secundario" href="painel-instituicao.php">← Voltar</a>
            </header>

            <section class="cartao-formulario">
                <form method="POST" action="">
                    <div class="grade-form">
                        <div class="grupo-campo">
                            <label for="nome_campanha">Nome da Campanha</label>
                            <input type="text" id="nome_campanha" name="nome_campanha" value="<?= $campanha['nome_campanha'] ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="area">Área</label>
                            <select id="area" name="area" required>
                                <option value="Educação" <?= $campanha['area'] == 'Educação' ? 'selected' : '' ?>>Educação</option>
                                <option value="Saúde" <?= $campanha['area'] == 'Saúde' ? 'selected' : '' ?>>Saúde</option>
                                <option value="Meio Ambiente" <?= $campanha['area'] == 'Meio Ambiente' ? 'selected' : '' ?>>Meio Ambiente</option>
                                <option value="Assistência Social" <?= $campanha['area'] == 'Assistência Social' ? 'selected' : '' ?>>Assistência Social</option>
                                <option value="Causa Animal" <?= $campanha['area'] == 'Causa Animal' ? 'selected' : '' ?>>Causa Animal</option>
                                <option value="Cultura e Esporte" <?= $campanha['area'] == 'Cultura e Esporte' ? 'selected' : '' ?>>Cultura e Esporte</option>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <label for="endereco">Local</label>
                            <input type="text" id="endereco" name="endereco" value="<?= $campanha['endereco'] ?? '' ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="status">Situação</label>
                            <select id="status" name="status">
                                <option value="Planejada" <?= $campanha['status'] == 'Planejada' ? 'selected' : '' ?>>Planejada</option>
                                <option value="Ativa" <?= $campanha['status'] == 'Ativa' ? 'selected' : '' ?>>Ativa</option>
                                <option value="Encerrada" <?= $campanha['status'] == 'Encerrada' ? 'selected' : '' ?>>Encerrada</option>
                                <option value="Cancelada" <?= $campanha['status'] == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <label for="data_inicio">Data de Início</label>
                            <input type="date" id="data_inicio" name="data_inicio" value="<?= $campanha['data_inicio'] ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="data_termino">Data de Término</label>
                            <input type="date" id="data_termino" name="data_termino" value="<?= $campanha['data_termino'] ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="horario">Horário</label>
                            <input type="text" id="horario" name="horario" value="<?= $campanha['horario'] ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="vagasTotais">Total de Vagas</label>
                            <input type="number" id="vagasTotais" name="vagasTotais" value="<?= $campanha['vagasTotais'] ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="horas">Horas Doadas</label>
                            <input type="number" id="horas" name="horas" value="<?= $campanha['horas'] ?>" >
                        </div>

                        <div class="grupo-campo campo-full">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="5" required><?= $campanha['descricao'] ?? '' ?></textarea>
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
</body>

</html>