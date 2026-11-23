<?php
require_once __DIR__ . '/init.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$areas = [
    'meio ambiente' => 'Meio ambiente',
    'educacao' => 'Educação',
    'assistencia social' => 'Assistência social',
    'saude' => 'Saúde',
    'protecao animal' => 'Proteção animal',
    'cultura e esportes' => 'Cultura e esportes'
];

$disponibilidades = [
    'manha' => 'Manhã',
    'tarde' => 'Tarde',
    'noite' => 'Noite',
    'fim de semana' => 'Fim de semana',
    'qualquer horario' => 'Qualquer horário'
];

$statusCampanha = [
    'planejada' => 'Planejada',
    'ativa' => 'Ativa',
    'encerrada' => 'Encerrada',
    'cancelada' => 'Cancelada'
];

$tiposCadastro = ['voluntario', 'instituicao', 'campanha'];
$tipo = $_POST['tipo_cadastro'] ?? $_GET['tipo'] ?? 'voluntario';

if (!in_array($tipo, $tiposCadastro, true)) {
    $tipo = 'voluntario';
}

$erros = [];
$sucesso = '';

$mensagens = [
    'voluntario' => 'Voluntário cadastrado com sucesso!',
    'instituicao' => 'Instituição cadastrada com sucesso!',
    'campanha' => 'Campanha cadastrada com sucesso!'
];

if (isset($_GET['sucesso']) && isset($mensagens[$_GET['sucesso']])) {
    $sucesso = $mensagens[$_GET['sucesso']];
}

// Busca as instituições para mostrar no cadastro de campanhas.
$instituicoes = $pdo->query("SELECT id, nome FROM instituicoes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $disponibilidade = $_POST['disponibilidade'] ?? '';

    if ($nome === '') {
        $erros[] = 'Informe o nome.';
    }

    if ($endereco === '') {
        $erros[] = 'Informe o endereço.';
    }

    if (!isset($disponibilidades[$disponibilidade])) {
        $erros[] = 'Escolha uma disponibilidade válida.';
    }

    try {
        if ($tipo === 'voluntario' || $tipo === 'instituicao') {
            $email = trim($_POST['email'] ?? '');
            $area = $_POST['area'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
                $erros[] = 'Informe um e-mail válido.';
            }

            if (!isset($areas[$area])) {
                $erros[] = 'Escolha uma área válida.';
            }

            if (strlen($senha) < 6) {
                $erros[] = 'A senha precisa ter pelo menos 6 caracteres.';
            }

            if ($senha !== $confirmarSenha) {
                $erros[] = 'As senhas não são iguais.';
            }

            if (!isset($_POST['termos'])) {
                $erros[] = 'Aceite os termos de uso para continuar.';
            }

            // Verifica se o e-mail já está cadastrado.
            if ($erros === []) {
                $consulta = $pdo->prepare('SELECT id FROM voluntarios WHERE email = ?');
                $consulta->execute([$email]);
                $emailExiste = $consulta->fetch();

                if (!$emailExiste) {
                    $consulta = $pdo->prepare('SELECT id FROM instituicoes WHERE email = ?');
                    $consulta->execute([$email]);
                    $emailExiste = $consulta->fetch();
                }

                if ($emailExiste) {
                    $erros[] = 'Este e-mail já está cadastrado.';
                }
            }

            if ($tipo === 'voluntario') {
                $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');

                if ($cpf !== '' && strlen($cpf) !== 11) {
                    $erros[] = 'O CPF deve ter 11 números.';
                }

                if ($erros === []) {
                    $sql = "INSERT INTO voluntarios
                            (nome, endereco, cpf, email, area_atuacao, disponibilidade, senha)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->execute([
                        $nome,
                        $endereco,
                        $cpf !== '' ? $cpf : null,
                        $email,
                        $area,
                        $disponibilidade,
                        password_hash($senha, PASSWORD_DEFAULT)
                    ]);
                }
            } else {
                if ($erros === []) {
                    $sql = "INSERT INTO instituicoes
                            (nome, area, endereco, email, disponibilidade, senha)
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->execute([
                        $nome,
                        $area,
                        $endereco,
                        $email,
                        $disponibilidade,
                        password_hash($senha, PASSWORD_DEFAULT)
                    ]);
                }
            }
        }

        if ($tipo === 'campanha') {
            $idInstituicao = (int) ($_POST['id_instituicao'] ?? 0);
            $descricao = trim($_POST['descricao'] ?? '');
            $horario = $_POST['horario'] ?? '';
            $status = $_POST['status'] ?? '';
            $vagas = filter_var($_POST['vagas'] ?? '', FILTER_VALIDATE_INT);
            $vagasTotais = filter_var($_POST['vagasTotais'] ?? '', FILTER_VALIDATE_INT);

            $consulta = $pdo->prepare('SELECT id FROM instituicoes WHERE id = ?');
            $consulta->execute([$idInstituicao]);

            if (!$consulta->fetch()) {
                $erros[] = 'Selecione uma instituição cadastrada.';
            }

            if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $horario)) {
                $erros[] = 'Informe um horário válido.';
            }

            if (!isset($statusCampanha[$status])) {
                $erros[] = 'Escolha um status válido.';
            }

            if ($vagas === false || $vagas < 0) {
                $erros[] = 'Informe a quantidade de vagas disponíveis.';
            }

            if ($vagasTotais === false || $vagasTotais < 1) {
                $erros[] = 'Informe o total de vagas.';
            }

            if ($vagas !== false && $vagasTotais !== false && $vagas > $vagasTotais) {
                $erros[] = 'As vagas disponíveis não podem passar do total de vagas.';
            }

            if ($erros === []) {
                $sql = "INSERT INTO campanhas
                        (id_instituicao, nome, descricao, endereco, horario,
                         disponibilidade, status, vagas, vagasTotais)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([
                    $idInstituicao,
                    $nome,
                    $descricao,
                    $endereco,
                    $horario,
                    $disponibilidade,
                    $status,
                    $vagas,
                    $vagasTotais
                ]);
            }
        }

        if ($erros === []) {
            header('Location: cadastro.php?sucesso=' . $tipo);
            exit;
        }
    } catch (PDOException $erro) {
        error_log($erro->getMessage());
        $erros[] = 'Não foi possível salvar. Confira se as colunas das tabelas estão iguais às usadas neste cadastro.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | VolunteerConnect</title>
    <link rel="stylesheet" href="css/cadastro.css">
</head>

<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="VolunteerConnect, início">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav">
            <a href="index.php">Início</a>
            <a href="oportunidades.php">Oportunidades</a>
            <a href="login.php">Painel</a>
            <a href="index.php#como-funciona">Como funciona</a>
        </nav>

        <div class="header-actions">
            <a class="login-link" href="login.php">Já tenho conta</a>
        </div>
    </header>

    <main class="signup-shell">
        <div class="signup-heading">
            <span class="eyebrow">UM CADASTRO DO SEU JEITO</span>
            <h1>Faça parte de uma rede que<br><em>transforma boas intenções em ações.</em></h1>
            <p>Escolha o tipo de cadastro que deseja fazer.</p>
        </div>

        <div class="forms-grid">
            <section class="form-card account-card">
                <div class="form-card-heading">
                    <span class="eyebrow">CADASTRO</span>
                    <h2>O que você quer cadastrar?</h2>
                    <p>Selecione uma opção para abrir o formulário.</p>
                </div>

                <?php if ($sucesso !== ''): ?>
                    <div class="aviso aviso-sucesso" role="status"><?= e($sucesso) ?></div>
                <?php endif; ?>

                <?php if ($erros): ?>
                    <div class="aviso aviso-erro" role="alert">
                        <ul>
                            <?php foreach ($erros as $mensagem): ?>
                                <li><?= e($mensagem) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="role-switch">
                    <a class="role-option <?= $tipo === 'voluntario' ? 'is-active' : '' ?>" href="cadastro.php?tipo=voluntario">
                        <span class="role-option-icon role-volunteer-icon">♡</span>
                        <span><strong>Voluntário</strong><small>Quero participar de ações</small></span>
                        <span class="role-check">✓</span>
                    </a>

                    <a class="role-option <?= $tipo === 'instituicao' ? 'is-active' : '' ?>" href="cadastro.php?tipo=instituicao">
                        <span class="role-option-icon role-institution-icon">⌂</span>
                        <span><strong>Instituição</strong><small>Quero divulgar campanhas</small></span>
                        <span class="role-check">✓</span>
                    </a>

                    <a class="role-option <?= $tipo === 'campanha' ? 'is-active' : '' ?>" href="cadastro.php?tipo=campanha">
                        <span class="role-option-icon role-campaign-icon">✳</span>
                        <span><strong>Campanha</strong><small>Quero criar uma ação</small></span>
                        <span class="role-check">✓</span>
                    </a>
                </div>

                <form method="post" action="cadastro.php?tipo=<?= e($tipo) ?>">
                    <input type="hidden" name="tipo_cadastro" value="<?= e($tipo) ?>">

                    <fieldset class="role-fields">
                        <legend><?= $tipo === 'voluntario' ? 'Dados do voluntário' : ($tipo === 'instituicao' ? 'Dados da instituição' : 'Dados da campanha') ?></legend>

                        <label for="nome">
                            <?= $tipo === 'voluntario' ? 'Nome completo' : ($tipo === 'instituicao' ? 'Nome da instituição' : 'Nome da campanha') ?>
                        </label>
                        <input type="text" id="nome" name="nome" maxlength="100" required value="<?= e($_POST['nome'] ?? '') ?>">

                        <label for="endereco">Endereço</label>
                        <input type="text" id="endereco" name="endereco" maxlength="200" required value="<?= e($_POST['endereco'] ?? '') ?>">

                        <label for="disponibilidade">Disponibilidade</label>
                        <select id="disponibilidade" name="disponibilidade" required>
                            <option value="">Selecione</option>
                            <?php foreach ($disponibilidades as $valor => $texto): ?>
                                <option value="<?= e($valor) ?>" <?= ($_POST['disponibilidade'] ?? '') === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($tipo === 'voluntario' || $tipo === 'instituicao'): ?>
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" maxlength="150" required value="<?= e($_POST['email'] ?? '') ?>">

                            <?php if ($tipo === 'voluntario'): ?>
                                <label for="cpf">CPF (opcional)</label>
                                <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= e($_POST['cpf'] ?? '') ?>">
                                <label for="area">Área em que deseja ajudar</label>
                            <?php else: ?>
                                <label for="area">Área de atuação</label>
                            <?php endif; ?>

                            <select id="area" name="area" required>
                                <option value="">Selecione</option>
                                <?php foreach ($areas as $valor => $texto): ?>
                                    <option value="<?= e($valor) ?>" <?= ($_POST['area'] ?? '') === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label for="senha">Senha (mínimo de 6 caracteres)</label>
                            <input type="password" id="senha" name="senha" minlength="6" required autocomplete="new-password">

                            <label for="confirmar_senha">Confirmar senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="6" required autocomplete="new-password">

                            <label class="terms">
                                <input type="checkbox" name="termos" value="1" required <?= isset($_POST['termos']) ? 'checked' : '' ?>>
                                Li e concordo com os termos de uso e a política de privacidade.
                            </label>
                        <?php else: ?>
                            <label for="id_instituicao">Instituição responsável</label>
                            <select id="id_instituicao" name="id_instituicao" required>
                                <option value="">Selecione</option>
                                <?php foreach ($instituicoes as $instituicao): ?>
                                    <option value="<?= (int) $instituicao['id'] ?>" <?= (int) ($_POST['id_instituicao'] ?? 0) === (int) $instituicao['id'] ? 'selected' : '' ?>><?= e($instituicao['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <?php if (!$instituicoes): ?>
                                <p class="aviso aviso-erro">Cadastre uma instituição antes de criar uma campanha.</p>
                            <?php endif; ?>

                            <label for="descricao">Descrição (opcional)</label>
                            <textarea id="descricao" name="descricao" rows="3"><?= e($_POST['descricao'] ?? '') ?></textarea>

                            <label for="horario">Horário</label>
                            <input type="time" id="horario" name="horario" required value="<?= e($_POST['horario'] ?? '') ?>">

                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="">Selecione</option>
                                <?php foreach ($statusCampanha as $valor => $texto): ?>
                                    <option value="<?= e($valor) ?>" <?= ($_POST['status'] ?? 'planejada') === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div class="two-fields">
                                <div>
                                    <label for="vagas">Vagas disponíveis</label>
                                    <input type="number" id="vagas" name="vagas" min="0" required value="<?= e($_POST['vagas'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="vagasTotais">Total de vagas</label>
                                    <input type="number" id="vagasTotais" name="vagasTotais" min="1" required value="<?= e($_POST['vagasTotais'] ?? '') ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                    </fieldset>

                    <button class="button form-submit" type="submit">
                        Cadastrar <?= $tipo === 'voluntario' ? 'voluntário' : ($tipo === 'instituicao' ? 'instituição' : 'campanha') ?>
                    </button>
                </form>

                <p class="form-footnote">Já tem uma conta? <a href="login.php">Faça login</a>.</p>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <a class="brand brand-footer" href="index.php">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>
        <p>Conectando pessoas a causas que importam.</p>
        <span>© 2026 VolunteerConnect</span>
    </footer>

    <script>
        var botaoMenu = document.querySelector('.menu-toggle');
        var menu = document.querySelector('.main-nav');

        if (botaoMenu && menu) {
            botaoMenu.addEventListener('click', function() {
                var aberto = menu.classList.toggle('is-open');
                botaoMenu.setAttribute('aria-expanded', aberto);
            });
        }
    </script>
</body>

</html>