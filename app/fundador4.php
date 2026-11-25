<?php
// Dados do fundador armazenados em variáveis
$nomeFundador   = "Nycolas Campos";
$cargoResumo    = "Desenvolvedor do Sistema.";
$fotoFundador   = "imagens/nycolas.webp";
$tituloSecao    = "Cargo:";
$descricaoBio   = "Como Desenvolvedor do Sistema da VolunteerConnect, Nycolas trabalha diretamente na implementação de soluções, na organização das interfaces e no aprimoramento contínuo da experiência de navegação da plataforma. Sua chegada à equipe aconteceu pela paixão por desenvolvimento web e pelo interesse em participar de um projeto com impacto positivo real na sociedade. Junto ao time técnico, contribuiu desde o início da construção do sistema, transformando requisitos e ideias em páginas funcionais que aproximam instituições e voluntários no dia a dia.";
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