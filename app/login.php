<?php
<<<<<<< HEAD
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

>>>>>>> teste
require_once __DIR__ . '/init.php';

if (isset($_SESSION['id_voluntario'])) {
    header("Location: painel-voluntario.php");
    exit;
} elseif (isset($_SESSION['id_instituicao'])) {
    header("Location: painel-instituicao.php");
    exit;
}

$erro = '';
$sucesso = '';
$email = '';
$tipo = 'voluntario';

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $erro = "Faça login para acessar esta página.";
}

if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastro') {
    $sucesso = "Cadastro realizado com sucesso! Faça login para continuar.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
    $email = appText($_POST, 'email');
    $senha = appText($_POST, 'senha', false);
=======
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
>>>>>>> teste
    $tipo  = $_POST['tipo'] ?? 'voluntario';

    $tabelasPermitidas = [
        'voluntario'  => 'voluntarios',
        'instituicao' => 'instituicoes',
    ];

<<<<<<< HEAD
    if ($email === '' || $senha === '' || strlen($senha) > 72 || str_contains($senha, "\0")) {
=======
    if ($email === '' || $senha === '') {
>>>>>>> teste
        $erro = "Preencha o e-mail e a senha.";
    } elseif (!isset($tabelasPermitidas[$tipo])) {
        $erro = "Tipo de acesso inválido.";
    } else {
        $tabela = $tabelasPermitidas[$tipo];
        $conta = read($pdo, $tabela, 'email = ?', [$email]);

        $senhaBanco = $conta['senha'] ?? null;
<<<<<<< HEAD
        $senhaValida = false;
        $legacy = false;
        if (is_string($senhaBanco) && $senhaBanco !== '' && strlen($senha) <= 1024) {
            $info = password_get_info($senhaBanco);
            $legacy = ($info['algoName'] ?? 'unknown') === 'unknown';
            $senhaValida = $legacy ? hash_equals($senhaBanco, $senha) : password_verify($senha, $senhaBanco);
        }

        if ($senhaValida) {
            if ($legacy || password_needs_rehash($senhaBanco, PASSWORD_DEFAULT)) {
                update($pdo, $tabela, ['senha' => password_hash($senha, PASSWORD_DEFAULT)], 'id = ?', [(int)$conta['id']]);
            }
            session_regenerate_id(true);
            unset($_SESSION['id_voluntario'], $_SESSION['id_instituicao'], $_SESSION['nome_voluntario'], $_SESSION['nome_instituicao'], $_SESSION['csrf_token']);
=======
        $senhaValida = $conta
            && $senhaBanco !== null
            && $senhaBanco !== ''
            && ($senha === $senhaBanco || password_verify($senha, $senhaBanco));

        if ($senhaValida) {
            session_regenerate_id(true);
>>>>>>> teste
            $_SESSION['tipo_conta'] = $tipo;

            if ($tipo === 'voluntario') {
                $_SESSION['id_voluntario']   = $conta['id'];
                $_SESSION['nome_voluntario'] = $conta['nome'];
                header("Location: painel-voluntario.php");
            } else {
                $_SESSION['id_instituicao']   = $conta['id'];
                $_SESSION['nome_instituicao'] = $conta['nome'];
                header("Location: painel-instituicao.php");
            }
            exit;
        } else {
            $erro = "E-mail ou senha inválidos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | VolunteerConnect</title>
    <link rel="stylesheet" href="css/login.css">
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

    <main class="login-page">
        <section class="login-card">
            <span class="eyebrow">BEM-VINDO DE VOLTA</span>
            <h1>Acesse seu painel</h1>
            <p class="intro">Entre para acompanhar suas atividades e oportunidades.</p>

            <?php if ($erro !== ''): ?>
<<<<<<< HEAD
                <p class="form-error" role="alert"><?= indexEsc($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($sucesso !== ''): ?>
                <p class="form-success" role="status"><?= indexEsc($sucesso, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="post" action="">
                    <?= csrfField() ?>
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="<?= indexEsc($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="seu.email@gmail.com" autocomplete="email" required>
=======
                <p class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($sucesso !== ''): ?>
                <p class="form-success" role="status"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="seu.email@gmail.com" autocomplete="email" required>
>>>>>>> teste

                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" placeholder="Digite sua senha" autocomplete="current-password" required>

                <label for="tipo">Quero acessar como:</label>
                <select id="tipo" name="tipo">
                    <option value="voluntario" <?= $tipo === 'voluntario' ? 'selected' : '' ?>>Voluntário</option>
                    <option value="instituicao" <?= $tipo === 'instituicao' ? 'selected' : '' ?>>Instituição</option>
                </select>

                <button class="login-button" type="submit">Entrar no painel <span>→</span></button>
            </form>

            <p class="signup-link">Ainda não tem cadastro? <a href="cadastro.php">Criar uma conta</a></p>
        </section>

        <aside class="login-aside">
            <span class="aside-mark">✳</span>
            <p>Pequenas atitudes podem transformar uma comunidade inteira.</p>
            <a href="oportunidades.php">Conheça as oportunidades <span>↗</span></a>
        </aside>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>