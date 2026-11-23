    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

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
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $tipo  = $_POST['tipo'] ?? 'voluntario';

        $tabelasPermitidas = [
            'voluntario'  => 'voluntarios',
            'instituicao' => 'instituicoes',
        ];

        if ($email === '' || $senha === '') {
            $erro = "Preencha o e-mail e a senha.";
        } elseif (!isset($tabelasPermitidas[$tipo])) {
            $erro = "Tipo de acesso inválido.";
        } else {
            $tabela = $tabelasPermitidas[$tipo];
            $usuario = read($pdo, $tabela, 'email = ?', [$email]);

            $senhaBanco = $usuario['senha'] ?? null;
            $senhaValida = $usuario
                && $senhaBanco !== null
                && $senhaBanco !== ''
                && ($senha === $senhaBanco || password_verify($senha, $senhaBanco));

            if ($senhaValida) {
                session_regenerate_id(true);
                $_SESSION['tipo_usuario'] = $tipo;

                if ($tipo === 'voluntario') {
                    $_SESSION['id_voluntario']   = $usuario['id'];
                    $_SESSION['nome_voluntario'] = $usuario['nome'];
                    header("Location: painel-voluntario.php");
                } else {
                    $_SESSION['id_instituicao']   = $usuario['id'];
                    $_SESSION['nome_instituicao'] = $usuario['nome'];
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
    </header>
    
    <main class="login-page">
    <section class="login-card">
        <span class="eyebrow">BEM-VINDO DE VOLTA</span>
        <h1>Acesse seu painel</h1>
        <p class="intro">Entre para acompanhar suas atividades e oportunidades.</p>

        <?php if ($erro !== ''): ?>
        <p class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($sucesso !== ''): ?>
        <p class="form-success" role="status"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="seu.email@gmail.com" autocomplete="email" required>

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
    <footer class="login-footer"><span>© 2026 VolunteerConnect</span><span>Conectando pessoas a causas que importam.</span></footer>
    </body>
    </html>