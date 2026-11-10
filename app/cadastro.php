<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro | VolunteerConnect</title>
  <link rel="stylesheet" href="assets/css/cadastro.css">
  <script src="assets/js/app.js" defer></script>
</head>
<body>
<header class="site-header">
  <a class="brand" href="index.php" aria-label="VolunteerConnect, início">
    <img class="brand-logo" src="assets/img/logo-volunteerconnect.png" alt="">
    <span>Volunteer<span>Connect</span></span>
  </a>
  <button class="menu-toggle" aria-label="Abrir menu" aria-expanded="false"><span></span><span></span><span></span></button>
  <nav class="main-nav">
    <a href="index.php">Início</a>
    <a href="oportunidades.php">Oportunidades</a>
    <a href="login.php">Painel</a>
    <a href="index.php#como-funciona">Como funciona</a>
  </nav>
  <div class="header-actions"><a class="login-link" href="login.php">Já tenho conta</a></div>
</header>

<main class="signup-shell">
  <div class="signup-heading">
    <span class="eyebrow"><span class="eyebrow-dot"></span> UM CADASTRO DO SEU JEITO</span>
    <h1>Faça parte de uma rede que<br><em>transforma boas intenções em ações.</em></h1>
    <p>Escolha como você quer participar. Cada perfil tem um cadastro diferente.</p>
  </div>

  <div class="forms-grid">
    <section class="form-card account-card" aria-labelledby="account-title">
      <div class="form-card-heading">
        <div>
          <span class="form-icon">♡</span>
          <span class="eyebrow">CRIE SEU ACESSO</span>
          <h2 id="account-title">Quem está se cadastrando?</h2>
          <p>Selecione seu perfil para ver os campos certos.</p>
        </div>
        <span class="form-step">01</span>
      </div>

      <div class="role-switch" role="group" aria-label="Tipo de cadastro">
        <button class="role-option is-active" type="button" data-register-role="voluntario" aria-pressed="true">
          <span class="role-option-icon role-volunteer-icon">♡</span>
          <span><strong>Sou voluntário</strong><small>Quero participar de ações</small></span>
          <span class="role-check">✓</span>
        </button>
        <button class="role-option" type="button" data-register-role="instituicao" aria-pressed="false">
          <span class="role-option-icon role-institution-icon">⌂</span>
          <span><strong>Sou instituição</strong><small>Quero divulgar campanhas</small></span>
          <span class="role-check">✓</span>
        </button>
      </div>

      <form data-demo-form data-account-form data-success-voluntario="Cadastro de voluntário concluído nesta demonstração!" data-success-instituicao="Cadastro de instituição concluído nesta demonstração!">
        <fieldset class="role-fields" data-role-panel="voluntario">
          <legend>Dados do voluntário</legend>
          <label for="vol-name">Nome completo</label>
          <input id="vol-name" name="nome_voluntario" autocomplete="name" placeholder="Seu nome e sobrenome" required>
          <div class="two-fields">
            <div><label for="vol-email">E-mail</label><input id="vol-email" name="email_voluntario" type="email" autocomplete="email" placeholder="voce@email.com" required></div>
            <div><label for="vol-phone">Telefone</label><input id="vol-phone" name="telefone_voluntario" type="tel" autocomplete="tel" placeholder="(00) 00000-0000" required></div>
          </div>
          <div class="two-fields">
            <div><label for="vol-city">Cidade</label><input id="vol-city" name="cidade_voluntario" autocomplete="address-level2" placeholder="Sua cidade" required></div>
            <div><label for="vol-birth">Data de nascimento</label><input id="vol-birth" name="nascimento" type="date" required></div>
          </div>
          <label for="vol-area">Área em que deseja ajudar</label>
          <select id="vol-area" name="area_interesse" required>
            <option value="">Selecione uma área</option><option>Educação</option><option>Meio ambiente</option><option>Assistência social</option><option>Saúde</option><option>Proteção animal</option><option>Cultura e esporte</option>
          </select>
          <label for="vol-availability">Disponibilidade</label>
          <select id="vol-availability" name="disponibilidade_voluntario" required>
            <option value="">Selecione sua disponibilidade</option><option>Durante a semana</option><option>Finais de semana</option><option>Manhãs</option><option>Tardes</option><option>Noites</option><option>Horários flexíveis</option>
          </select>
          <div class="two-fields">
            <div><label for="vol-password">Senha</label><input id="vol-password" name="senha_voluntario" type="password" minlength="6" autocomplete="new-password" placeholder="Mínimo de 6 caracteres" required></div>
            <div><label for="vol-confirm">Confirmar senha</label><input id="vol-confirm" name="confirmar_senha_voluntario" type="password" minlength="6" autocomplete="new-password" placeholder="Digite novamente" required></div>
          </div>
        </fieldset>

        <fieldset class="role-fields" data-role-panel="instituicao" hidden disabled>
          <legend>Dados da instituição</legend>
          <label for="org-name">Nome da instituição</label>
          <input id="org-name" name="nome_instituicao" placeholder="Nome pelo qual é conhecida" required>
          <div class="two-fields">
            <div><label for="org-legal">Razão social</label><input id="org-legal" name="razao_social" placeholder="Nome registrado"></div>
            <div><label for="org-cnpj">CNPJ</label><input id="org-cnpj" name="cnpj" inputmode="numeric" placeholder="00.000.000/0000-00"></div>
          </div>
          <label for="org-responsible">Pessoa responsável</label>
          <input id="org-responsible" name="responsavel" placeholder="Nome do responsável pelo cadastro" required>
          <div class="two-fields">
            <div><label for="org-email">E-mail institucional</label><input id="org-email" name="email_instituicao" type="email" placeholder="contato@instituicao.org" required></div>
            <div><label for="org-phone">Telefone de contato</label><input id="org-phone" name="telefone_instituicao" type="tel" placeholder="(00) 0000-0000" required></div>
          </div>
          <div class="two-fields">
            <div><label for="org-city">Cidade</label><input id="org-city" name="cidade_instituicao" placeholder="Cidade onde atua" required></div>
            <div><label for="org-type">Tipo de instituição</label><select id="org-type" name="tipo_instituicao" required><option value="">Selecione</option><option>ONG</option><option>Associação</option><option>Projeto social</option><option>Instituição de ensino</option><option>Instituição pública</option><option>Outro</option></select></div>
          </div>
          <label for="org-site">Site ou rede social <span class="optional-label">(opcional)</span></label>
          <input id="org-site" name="site_instituicao" type="url" placeholder="https://">
          <div class="two-fields">
            <div><label for="org-password">Senha</label><input id="org-password" name="senha_instituicao" type="password" minlength="6" autocomplete="new-password" placeholder="Mínimo de 6 caracteres" required></div>
            <div><label for="org-confirm">Confirmar senha</label><input id="org-confirm" name="confirmar_senha_instituicao" type="password" minlength="6" autocomplete="new-password" placeholder="Digite novamente" required></div>
          </div>
        </fieldset>

        <label class="terms"><input type="checkbox" required> Li e concordo com os termos de uso e a política de privacidade.</label>
        <button class="button form-submit" type="submit">Criar meu cadastro <span>→</span></button>
      </form>
      <p class="form-footnote">Já tem uma conta? <a href="login.php">Faça login</a>.</p>
    </section>

    <section class="form-card campaign-card" id="nova-campanha" aria-labelledby="campaign-title-heading">
      <div class="form-card-heading">
        <div>
          <span class="form-icon campaign-icon">✳</span>
          <span class="eyebrow eyebrow-coral">PARA INSTITUIÇÕES</span>
          <h2 id="campaign-title-heading">Divulgue uma campanha</h2>
          <p>As instituições podem cadastrar uma ação e encontrar voluntários.</p>
        </div>
        <span class="form-step">02</span>
      </div>
      <div class="campaign-tip"><span>✦</span><p>Já tem uma conta institucional? Entre pelo painel para gerenciar suas campanhas. Este formulário é apenas uma demonstração.</p></div>
      <form data-demo-form data-success="Campanha registrada nesta demonstração! Confira o painel da instituição.">
        <label for="campaign-title">Título da campanha</label>
        <input id="campaign-title" name="titulo" placeholder="Ex.: Aulas de reforço escolar" required>
        <label for="campaign-description">Descrição</label>
        <textarea id="campaign-description" name="descricao" rows="3" placeholder="Explique o objetivo e como os voluntários podem ajudar..." required></textarea>
        <div class="two-fields">
          <div><label for="campaign-area">Área de atuação</label><select id="campaign-area" name="area" required><option value="">Selecione</option><option>Educação</option><option>Meio ambiente</option><option>Assistência social</option><option>Saúde</option><option>Animais</option><option>Cultura e esporte</option></select></div>
          <div><label for="campaign-format">Modalidade</label><select id="campaign-format" name="modalidade"><option>Presencial</option><option>Online</option><option>Híbrido</option></select></div>
        </div>
        <div class="two-fields">
          <div><label for="campaign-location">Local</label><input id="campaign-location" name="local" placeholder="Cidade / bairro"></div>
          <div><label for="campaign-vacancies">Número de vagas</label><input id="campaign-vacancies" name="vagas" type="number" min="1" placeholder="Ex.: 10"></div>
        </div>
        <div class="two-fields">
          <div><label for="campaign-date">Data de início</label><input id="campaign-date" name="data" type="date"></div>
          <div><label for="campaign-time">Disponibilidade</label><select id="campaign-time" name="disponibilidade"><option>Durante a semana</option><option>Fim de semana</option><option>Flexível</option></select></div>
        </div>
        <label for="campaign-contact">E-mail de contato</label>
        <input id="campaign-contact" name="contato" type="email" placeholder="contato@instituicao.org" required>
        <div class="campaign-actions"><button class="button button-outline" type="reset">Limpar campos</button><button class="button button-coral" type="submit">Publicar campanha <span>↗</span></button></div>
        <p class="form-disclaimer">Demonstração acadêmica: os dados não são enviados para um servidor.</p>
      </form>
    </section>
  </div>
</main>

<footer class="site-footer">
  <a class="brand brand-footer" href="index.php"><img class="brand-logo" src="assets/img/logo-volunteerconnect.png" alt=""><span>Volunteer<span>Connect</span></span></a>
  <p>Conectando pessoas a causas que importam.</p><span>© 2026 VolunteerConnect</span>
</footer>
</body>
</html>
