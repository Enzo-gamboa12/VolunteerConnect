<?php

require_once __DIR__ . '/init.php';

$instituicao = read($pdo, 'instituicoes', 'id = ?', [$_SESSION['id_instituicao']]);
$campanha = read($pdo, 'campanhas', 'id = ? AND id_instituicao = ?', [$_GET['id'], $_SESSION['id_instituicao']]);

if (!$campanha) {
    header("Location: painel-instituicao.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome'            => $_POST['nome_campanha'],
        'tipo'            => $_POST['tipo'],
        'descricao'       => $_POST['descricao'],
        'endereco'        => $_POST['endereco'],
        'data_inicio'     => $_POST['data_inicio'],
        'data_termino'    => $_POST['data_termino'],
        'horario'         => $_POST['horario'],
        'disponibilidade' => $_POST['disponibilidade'],
        'vagasTotais'     => $_POST['vagasTotais'],
        'status'          => $_POST['status']
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
                <a href="mailto:suporte@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Editar Campanha</h1>
                    <p><?= htmlspecialchars($campanha['nome'] ?? '') ?></p>
                </div>
                <a class="botao-secundario" href="painel-instituicao.php">← Voltar</a>
            </header>

            <section class="cartao-formulario">
                <form method="POST" action="">
                    <div class="grade-form">
                        <div class="grupo-campo">
                            <label for="nome_campanha">Nome da Campanha</label>
                            <input type="text" id="nome_campanha" name="nome_campanha" value="<?= htmlspecialchars($campanha['nome'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="tipo">Área</label>
                            <select id="tipo" name="tipo" required>
                                <option value="Educação" <?= strcasecmp(trim($campanha['tipo'] ?? ''), 'Educação') === 0 ? 'selected' : '' ?>>Educação</option>
                                <option value="Saúde" <?= strcasecmp(trim($campanha['tipo'] ?? ''), 'Saúde') === 0 ? 'selected' : '' ?>>Saúde</option>
                                <option value="Meio Ambiente" <?= strcasecmp(trim($campanha['tipo'] ?? ''), 'Meio Ambiente') === 0 ? 'selected' : '' ?>>Meio Ambiente</option>
                                <option value="Assistência Social" <?= strcasecmp(trim($campanha['tipo'] ?? ''), 'Assistência Social') === 0 ? 'selected' : '' ?>>Assistência Social</option>
                                <option value="Causa Animal" <?= strcasecmp(trim($campanha['tipo'] ?? ''), 'Causa Animal') === 0 ? 'selected' : '' ?>>Causa Animal</option>
                                <option value="Cultura e Esporte" <?= strcasecmp(trim($campanha['tipo'] ?? ''), 'Cultura e Esporte') === 0 ? 'selected' : '' ?>>Cultura e Esporte</option>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <label for="endereco">Local</label>
                            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($campanha['endereco'] ?? '') ?>" required>
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
                            <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($campanha['data_inicio'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="data_termino">Data de Término</label>
                            <input type="date" id="data_termino" name="data_termino" value="<?= htmlspecialchars($campanha['data_termino'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="horario">Horário</label>
                            <input type="text" id="horario" name="horario" value="<?= htmlspecialchars($campanha['horario'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="disponibilidade">Disponibilidade</label>
                            <input type="text" id="disponibilidade" name="disponibilidade" value="<?= htmlspecialchars($campanha['disponibilidade'] ?? '') ?>">
                        </div>

                        <div class="grupo-campo">
                            <label for="vagasTotais">Total de Vagas</label>
                            <input type="number" id="vagasTotais" name="vagasTotais" value="<?= htmlspecialchars($campanha['vagasTotais'] ?? '') ?>" required>
                        </div>

                        <div class="grupo-campo campo-full">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($campanha['descricao'] ?? '') ?></textarea>
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