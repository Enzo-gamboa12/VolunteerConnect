<?php
// Dados do fundador armazenados em variáveis
$nomeFundador   = "Antonio Gabriel";
$cargoResumo    = "Desenvolvedor do Sistema.";
$fotoFundador   = "imagens/antonio-fundador.jpg";
$tituloSecao    = "Cargo:";
$descricaoBio   = "Como Desenvolvedor do Sistema da VolunteerConnect, Antonio atua na construção da estrutura técnica, na lógica de funcionamento e na integração das funcionalidades que tornam a plataforma rápida, segura e intuitiva para usuários e instituições. Ele chegou ao projeto motivado pelo desafio de usar a programação como ferramenta de transformação social, vendo na tecnologia o meio ideal para escalar ações solidárias. Desde os primeiros protótipos, dedicou-se a tirar a ideia do papel em forma de código, garantindo que cada recurso desenvolvido facilitasse o encontro entre voluntários e causas sociais.";
$anoAtual       = date("Y");
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeFundador; ?> - Fundadores</title>
    <link rel="stylesheet" href="css/fundador.css">
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

        <div class="menu-usuario">
            <span class="avatar-usuario">IC</span>
            <span>Instituto Caminhos</span>
        </div>
    </header>

    <section class="card-secao">
        <div class="perfil-topo">
            <img class="imagem-card" src="<?php echo $fotoFundador; ?>" alt="<?php echo $nomeFundador; ?>">
            <div class="nomeie">
                <h1><?php echo $nomeFundador; ?></h1>
                <small><?php echo $cargoResumo; ?></small>
            </div>
        </div>

        <div class="perfil-baixo">
            <h2><?php echo $tituloSecao; ?></h2>
            <p><?php echo $descricaoBio; ?></p>
        </div>
    </section>

    <footer class="rodape-simples">
        <div class="rodape-conteudo">
            <p>&copy; <?php echo $anoAtual; ?> VolunteerConnect. Todos os direitos reservados.</p>
            <ul class="rodape-links">
                <li><a href="sobre.php">Sobre Nós</a></li>
                <li><a href="#">Termos de Uso</a></li>
                <li><a href="#">Política de Privacidade</a></li>
            </ul>
        </div>
    </footer>
</body>

</html>