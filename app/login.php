<?php
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $tipo = $_POST['tipo'] ?? 'voluntario';

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $senha !== '' && in_array($tipo, ['voluntario', 'instituicao'], true)) {
        // Acesso demonstrativo: não há autenticação real nem banco de dados neste projeto.
        $destino = $tipo === 'instituicao' ? 'painel-instituicao.php' : 'painel-voluntario.php';
        header('Location: ' . $destino);
        exit;
    }
    $erro = 'Informe um e-mail válido e uma senha para continuar.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar | VolunteerConnect</title>
  <link rel="stylesheet" href="assets/css/login.css">
  <script src="assets/js/app.js" defer></script>
</head>
<body>
<header class="login-header">
  <a class="brand" href="index.php" aria-label="VolunteerConnect, página inicial">
    <img class="brand-logo" src="assets/img/logo-volunteerconnect.png" alt="">
    <span>Volunteer<span>Connect</span></span>
  </a>
  <a class="back-link" href="index.php">Voltar ao início <span>↗</span></a>
</header>
<main class="login-page">
  <section class="login-card">
    <div class="login-symbol">♡</div>
    <span class="eyebrow">BEM-VINDO DE VOLTA</span>
    <h1>Acesse seu painel</h1>
    <p class="intro">Entre para acompanhar suas atividades e oportunidades.</p>
    <div class="demo-note"><strong>Acesso para demonstração</strong><span>Como este projeto não possui banco de dados, use qualquer e-mail válido e qualquer senha.</span></div>
    <?php if ($erro !== ''): ?>
      <p class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" placeholder="voce@email.com" autocomplete="email" required>
      <label for="senha">Senha</label>
      <input id="senha" name="senha" type="password" placeholder="Digite sua senha" autocomplete="current-password" required>
      <label for="tipo">Quero acessar como</label>
      <select id="tipo" name="tipo">
        <option value="voluntario">Voluntário</option>
        <option value="instituicao">Instituição</option>
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
