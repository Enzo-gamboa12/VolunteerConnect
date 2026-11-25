<?php
// Dados do fundador armazenados em variáveis
$nomeFundador   = "Enzo Gamboa";
$cargoResumo    = "Gerente, Diretor, Coordenador.";
$fotoFundador   = "imagens/enzo.webp";
$tituloSecao    = "Cargo:";
$descricaoBio   = "Como Gerente, Diretor e Coordenador da VolunteerConnect, Enzo é responsável por liderar o planejamento estratégico, organizar as operações da equipe e garantir que cada projeto saia do papel com eficiência e propósito. Sua trajetória na empresa começou ao perceber a dificuldade que muitas instituições sociais enfrentavam para organizar demandas e alcançar pessoas dispostas a ajudar. Unindo visão de liderança e foco em gestão, assumiu a coordenação geral do projeto para transformar uma ideia solidária em uma plataforma estruturada, capaz de conectar voluntários e instituições de forma prática e transparente.";
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