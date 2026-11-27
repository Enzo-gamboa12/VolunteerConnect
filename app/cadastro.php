<?php
require_once __DIR__ . '/init.php';
<<<<<<< HEAD
function e(mixed $value): string { return indexEsc($value); }
$areas = array_combine(['Meio Ambiente','Educação','Assistência Social','Saúde','Proteção Animal','Cultura e Esportes'], ['Meio Ambiente','Educação','Assistência Social','Saúde','Proteção Animal','Cultura e Esportes']);
$disponibilidades = array_combine(['Manhã','Tarde','Noite','Fim de Semana','Qualquer Horário'], ['Manhã','Tarde','Noite','Fim de Semana','Qualquer Horário']);
$statusCampanha = ['Planejada'=>'Planejada','Ativa'=>'Ativa','Encerrada'=>'Encerrada','Cancelada'=>'Cancelada'];
$tipo = appText($_POST, 'tipo_cadastro') ?: (appText($_GET, 'tipo') ?: 'voluntario');
if ($tipo === 'campanha') { header('Location: campanha.php', true, 303); exit; }
if (!in_array($tipo, ['voluntario','instituicao'], true)) $tipo = 'voluntario';
$erros = []; $sucesso = ''; $instituicoes = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $nome = appText($_POST, 'nome'); $endereco = appText($_POST, 'endereco');
    $email = appText($_POST, 'email'); $area = appText($_POST, 'area');
    $disponibilidade = appText($_POST, 'disponibilidade');
    $senha = appText($_POST, 'senha', false); $confirmarSenha = appText($_POST, 'confirmar_senha', false);
    $cpf = preg_replace('/\D/', '', appText($_POST, 'cpf'));
    if ($nome === '' || appLength($nome) > 100) $erros[] = 'Informe um nome com até 100 caracteres.';
    if ($endereco === '' || appLength($endereco) > 200) $erros[] = 'Informe um endereço com até 200 caracteres.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $erros[] = 'Informe um e-mail válido.';
    if (!isset($areas[$area])) $erros[] = 'Escolha uma área válida.';
    if (!isset($disponibilidades[$disponibilidade])) $erros[] = 'Escolha uma disponibilidade válida.';
    if (strlen($senha) < 6 || strlen($senha) > 72 || str_contains($senha, "\0")) $erros[] = 'A senha deve ter entre 6 e 72 bytes, sem caractere nulo.';
    if ($senha !== $confirmarSenha) $erros[] = 'As senhas não são iguais.';
    if (appText($_POST, 'termos') !== '1') $erros[] = 'Aceite os termos para continuar.';
    if ($tipo === 'voluntario' && $cpf !== '' && strlen($cpf) !== 11) $erros[] = 'O CPF deve ter 11 números.';
    if (!$erros) {
        try {
            foreach (['voluntarios','instituicoes'] as $table) {
                if (read($pdo, $table, 'email = ?', [$email])) { $erros[] = 'Este e-mail já está cadastrado.'; break; }
            }
            if (!$erros) {
                $data = ['nome'=>$nome, 'endereco'=>$endereco, 'email'=>$email, 'disponibilidade'=>$disponibilidade, 'senha'=>password_hash($senha, PASSWORD_DEFAULT)];
                $data[$tipo === 'voluntario' ? 'area_atuacao' : 'area'] = $area;
                if ($tipo === 'voluntario') $data['cpf'] = $cpf !== '' ? $cpf : null;
                create($pdo, $tipo === 'voluntario' ? 'voluntarios' : 'instituicoes', $data);
                header('Location: login.php?sucesso=cadastro', true, 303); exit;
            }
        } catch (PDOException $error) {
            error_log((string)$error);
            $erros[] = (string)$error->getCode() === '23000' ? 'E-mail já cadastrado ou dados incompatíveis.' : 'Não foi possível salvar. Tente novamente em instantes.';
        }
=======

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

$tiposCadastro = ['voluntario', 'instituicao'];
$tipo = $_POST['tipo_cadastro'] ?? $_GET['tipo'] ?? 'voluntario';

if (!in_array($tipo, $tiposCadastro, true)) {
    $tipo = 'voluntario';
}

$erros = [];
$sucesso = '';

$mensagens = [
    'voluntario' => 'Voluntário cadastrado com sucesso!',
    'instituicao' => 'Instituição cadastrada com sucesso!'
];

if (isset($_GET['sucesso']) && isset($mensagens[$_GET['sucesso']])) {
    $sucesso = $mensagens[$_GET['sucesso']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $disponibilidade = $_POST['disponibilidade'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $area = $_POST['area'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($nome === '') {
        $erros[] = 'Informe o nome.';
    }

    if ($endereco === '') {
        $erros[] = 'Informe o endereço.';
    }

    if (!isset($disponibilidades[$disponibilidade])) {
        $erros[] = 'Escolha uma disponibilidade válida.';
    }

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

    try {
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

        if ($erros === []) {
            header('Location: cadastro.php?sucesso=' . $tipo);
            exit;
        }
    } catch (PDOException $erro) {
        error_log($erro->getMessage());
        $erros[] = 'Não foi possível salvar. Confira se as colunas das tabelas estão iguais às usadas neste cadastro.';
>>>>>>> teste
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
<<<<<<< HEAD
    <link rel="stylesheet" href="css/navigation.css">
    <script src="js/navigation.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/navigation.php'; ?>
=======
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

        <div class="acoes-autenticacao" aria-label="Acesso à conta">
            <a class="botao-autenticacao" href="login.php">Entrar</a>
            <a class="botao-autenticacao botao-autenticacao-cadastro" href="cadastro.php">Cadastrar</a>
        </div>
    </header>
>>>>>>> teste

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
<<<<<<< HEAD

                    <a class="role-option <?= $tipo === 'campanha' ? 'is-active' : '' ?>" href="campanha.php">
                        <span class="role-option-icon role-campaign-icon">✳</span>
                        <span><strong>Campanha</strong><small>Quero criar uma ação</small></span>
                        <span class="role-check">✓</span>
                    </a>
                </div>

                <form method="post" action="cadastro.php?tipo=<?= e($tipo) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="tipo_cadastro" value="<?= e($tipo) ?>">

                    <fieldset class="role-fields">
                        <legend><?= $tipo === 'voluntario' ? 'Dados do voluntário' : ($tipo === 'instituicao' ? 'Dados da instituição' : 'Dados da campanha') ?></legend>

                        <label for="nome">
                            <?= $tipo === 'voluntario' ? 'Nome completo' : ($tipo === 'instituicao' ? 'Nome da instituição' : 'Nome da campanha') ?>
=======
                </div>

                <form method="post" action="cadastro.php?tipo=<?= e($tipo) ?>">
                    <input type="hidden" name="tipo_cadastro" value="<?= e($tipo) ?>">

                    <fieldset class="role-fields">
                        <legend><?= $tipo === 'voluntario' ? 'Dados do voluntário' : 'Dados da instituição' ?></legend>

                        <label for="nome">
                            <?= $tipo === 'voluntario' ? 'Nome completo' : 'Nome da instituição' ?>
>>>>>>> teste
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

<<<<<<< HEAD
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
=======
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
                    </fieldset>

                    <button class="button form-submit" type="submit">
                        Cadastrar <?= $tipo === 'voluntario' ? 'voluntário' : 'instituição' ?>
>>>>>>> teste
                    </button>
                </form>

                <p class="form-footnote">Já tem uma conta? <a href="login.php">Faça login</a>.</p>
            </section>
        </div>
    </main>

<<<<<<< HEAD
    <footer class="site-footer">
        <a class="brand brand-footer" href="index.php">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>
        <p>Conectando pessoas a causas que importam.</p>
        <span>© 2026 VolunteerConnect</span>
=======
    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
>>>>>>> teste
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