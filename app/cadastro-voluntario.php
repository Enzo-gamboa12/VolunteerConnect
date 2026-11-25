<?php

require_once __DIR__ . '/init.php';

// Valores exatamente como definidos nos ENUMs de database/schema.sql.
$areas = [
    'Meio Ambiente',
    'Educação',
    'Assistência Social',
    'Saúde',
    'Proteção Animal',
    'Cultura e Esportes',
];

$disponibilidades = [
    'Manhã',
    'Tarde',
    'Noite',
    'Fim de Semana',
    'Qualquer Horário',
];

/** Lê um campo de texto do POST (ignora arrays) já sem espaços nas pontas. */
function cadastroCampo(string $nome): string
{
    $valor = $_POST[$nome] ?? '';

    return is_string($valor) ? trim($valor) : '';
}

/** Monta as <option> de um select, marcando a opção escolhida. */
function cadastroOpcoes(array $lista, string $selecionado): string
{
    $html = '<option value="">Selecione</option>';

    foreach ($lista as $valor) {
        $marcada = $valor === $selecionado ? ' selected' : '';
        $html .= '<option value="' . indexEsc($valor) . '"' . $marcada . '>' . indexEsc($valor) . '</option>';
    }

    return $html;
}

/** O e-mail não pode existir em voluntários nem em instituições. */
function cadastroEmailExiste($pdo, string $email): bool
{
    foreach (['voluntarios', 'instituicoes'] as $tabela) {
        if (read($pdo, $tabela, 'email = ?', [$email]) !== false) {
            return true;
        }
    }

    return false;
}

$erros = ['voluntario' => [], 'instituicao' => []];
$dados = ['voluntario' => [], 'instituicao' => []];
$formEnviado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formEnviado = cadastroCampo('acao');

    if (!isset($erros[$formEnviado])) {
        $formEnviado = '';
    }
}

if ($formEnviado !== '') {
    $f = $formEnviado;
    $ehVoluntario = $f === 'voluntario';

    $dados[$f] = [
        'nome'            => cadastroCampo('nome'),
        'endereco'        => cadastroCampo('endereco'),
        'cpf'             => $ehVoluntario ? cadastroCampo('cpf') : '',
        'email'           => cadastroCampo('email'),
        'area'            => cadastroCampo('area'),
        'disponibilidade' => cadastroCampo('disponibilidade'),
        'termos'          => cadastroCampo('termos') === '1',
    ];

    $d = $dados[$f];

    // O login também aplica trim() na senha digitada, então a senha é gravada do mesmo jeito.
    $senha = cadastroCampo('senha');
    $confirmarSenha = cadastroCampo('confirmar_senha');
    $e = [];

    // Nome: VARCHAR(100) NOT NULL
    if ($d['nome'] === '') {
        $e[] = $ehVoluntario ? 'Informe o nome completo.' : 'Informe o nome da instituição.';
    } elseif (strlen($d['nome']) > 100) {
        $e[] = 'O nome deve ter no máximo 100 caracteres.';
    }

    // Endereço: VARCHAR(200) NOT NULL
    if ($d['endereco'] === '') {
        $e[] = 'Informe o endereço.';
    } elseif (strlen($d['endereco']) > 200) {
        $e[] = 'O endereço deve ter no máximo 200 caracteres.';
    }

    // E-mail: VARCHAR(150) NOT NULL UNIQUE
    if ($d['email'] === '' || !filter_var($d['email'], FILTER_VALIDATE_EMAIL) || strlen($d['email']) > 150) {
        $e[] = 'Informe um e-mail válido (até 150 caracteres).';
    }

    // CPF (somente voluntário): VARCHAR(100), opcional. Gravado no formato 000.000.000-00, como no banco.
    $cpf = null;
    if ($ehVoluntario && $d['cpf'] !== '') {
        $digitos = preg_replace('/\D/', '', $d['cpf']);

        if (strlen($digitos) !== 11) {
            $e[] = 'O CPF deve ter 11 números.';
        } else {
            $cpf = substr($digitos, 0, 3) . '.' . substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-' . substr($digitos, 9, 2);
        }
    }

    // Área: ENUM NOT NULL (area_atuacao em voluntarios, area em instituicoes)
    if (!in_array($d['area'], $areas, true)) {
        $e[] = $ehVoluntario ? 'Escolha uma área em que deseja ajudar.' : 'Escolha uma área de atuação.';
    }

    // Disponibilidade: ENUM NOT NULL
    if (!in_array($d['disponibilidade'], $disponibilidades, true)) {
        $e[] = 'Escolha uma disponibilidade válida.';
    }

    // Senha: o banco guarda o hash em VARCHAR(255); o bcrypt considera no máximo 72 bytes.
    if (strlen($senha) < 6) {
        $e[] = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif (strlen($senha) > 72) {
        $e[] = 'A senha deve ter no máximo 72 caracteres.';
    }

    if ($senha !== $confirmarSenha) {
        $e[] = 'As senhas não são iguais.';
    }

    if (!$d['termos']) {
        $e[] = 'Aceite os termos de uso para continuar.';
    }

    // Só consulta o banco se o restante estiver válido.
    if (!$e && cadastroEmailExiste($pdo, $d['email'])) {
        $e[] = 'Este e-mail já está cadastrado.';
    }

    if (!$e) {
        try {
            if ($ehVoluntario) {
                $id = create($pdo, 'voluntarios', [
                    'nome'            => $d['nome'],
                    'endereco'        => $d['endereco'],
                    'cpf'             => $cpf,
                    'email'           => $d['email'],
                    'area_atuacao'    => $d['area'],
                    'disponibilidade' => $d['disponibilidade'],
                    'senha'           => password_hash($senha, PASSWORD_DEFAULT),
                ]);
            } else {
                $id = create($pdo, 'instituicoes', [
                    'nome'            => $d['nome'],
                    'area'            => $d['area'],
                    'endereco'        => $d['endereco'],
                    'email'           => $d['email'],
                    'disponibilidade' => $d['disponibilidade'],
                    'senha'           => password_hash($senha, PASSWORD_DEFAULT),
                ]);
            }

            // Só considera salvo se o CRUD devolveu o ID do novo registro.
            if ($id !== '' && $id !== '0') {
                header('Location: login.php?sucesso=cadastro');
                exit;
            }

            $e[] = 'Não foi possível confirmar o cadastro. Tente novamente.';
        } catch (Exception $erro) {
            error_log($erro->getMessage());

            // 23000 = violação de chave única (e-mail cadastrado por outra requisição ao mesmo tempo).
            if ((string) $erro->getCode() === '23000') {
                $e[] = 'Este e-mail já está cadastrado.';
            } else {
                $e[] = 'Não foi possível salvar o cadastro. Tente novamente em instantes.';
            }
        }
    }

    $erros[$f] = $e;
}

$v = $dados['voluntario'];
$i = $dados['instituicao'];
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
            <span class="eyebrow"><span class="eyebrow-dot"></span> UM CADASTRO DO SEU JEITO</span>
            <h1>Faça parte de uma rede que<br><em>transforma boas intenções em ações.</em></h1>
            <p>Escolha como você quer participar. Cada perfil tem um cadastro diferente.</p>
        </div>

        <div class="forms-grid">
            <!-- Cadastro de voluntário -->
            <section class="form-card account-card" id="cadastro-voluntario" aria-labelledby="titulo-voluntario">
                <div class="form-card-heading">
                    <div>
                        <span class="form-icon">♡</span>
                        <span class="eyebrow">CRIE SEU ACESSO</span>
                        <h2 id="titulo-voluntario">Sou voluntário</h2>
                        <p>Quero participar de ações</p>
                    </div>
                    <span class="form-step">01</span>
                </div>

                <?php if ($erros['voluntario']): ?>
                    <div class="form-error" role="alert">
                        <ul>
                            <?php foreach ($erros['voluntario'] as $mensagem): ?>
                                <li><?= indexEsc($mensagem) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="cadastro.php#cadastro-voluntario">
                    <input type="hidden" name="acao" value="voluntario">

                    <fieldset class="role-fields" data-role-panel="voluntario">
                        <legend>Dados do voluntário</legend>

                        <label for="vol-nome">Nome completo</label>
                        <input id="vol-nome" name="nome" type="text" maxlength="100" autocomplete="name" placeholder="Seu nome e sobrenome" required value="<?= indexEsc($v['nome'] ?? '') ?>">

                        <div class="two-fields">
                            <div>
                                <label for="vol-email">E-mail</label>
                                <input id="vol-email" name="email" type="email" maxlength="150" autocomplete="email" placeholder="voce@email.com" required value="<?= indexEsc($v['email'] ?? '') ?>">
                            </div>
                            <div>
                                <label for="vol-cpf">CPF <span class="optional-label">(opcional)</span></label>
                                <input id="vol-cpf" name="cpf" type="text" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" value="<?= indexEsc($v['cpf'] ?? '') ?>">
                            </div>
                        </div>

                        <label for="vol-endereco">Endereço</label>
                        <input id="vol-endereco" name="endereco" type="text" maxlength="200" autocomplete="street-address" required value="<?= indexEsc($v['endereco'] ?? '') ?>">

                        <label for="vol-area">Área em que deseja ajudar</label>
                        <select id="vol-area" name="area" required>
                            <?= cadastroOpcoes($areas, $v['area'] ?? '') ?>
                        </select>

                        <label for="vol-disponibilidade">Disponibilidade</label>
                        <select id="vol-disponibilidade" name="disponibilidade" required>
                            <?= cadastroOpcoes($disponibilidades, $v['disponibilidade'] ?? '') ?>
                        </select>

                        <div class="two-fields">
                            <div>
                                <label for="vol-senha">Senha</label>
                                <input id="vol-senha" name="senha" type="password" minlength="6" maxlength="72" autocomplete="new-password" placeholder="Mínimo de 6 caracteres" required>
                            </div>
                            <div>
                                <label for="vol-confirmar">Confirmar senha</label>
                                <input id="vol-confirmar" name="confirmar_senha" type="password" minlength="6" maxlength="72" autocomplete="new-password" placeholder="Digite novamente" required>
                            </div>
                        </div>
                    </fieldset>

                    <label class="terms">
                        <input type="checkbox" name="termos" value="1" required <?= !empty($v['termos']) ? 'checked' : '' ?>>
                        Li e concordo com os termos de uso e a política de privacidade.
                    </label>

                    <button class="button form-submit" type="submit">Criar meu cadastro <span>→</span></button>
                </form>
            </section>

            <!-- Cadastro de instituição -->
            <section class="form-card campaign-card" id="cadastro-instituicao" aria-labelledby="titulo-instituicao">
                <div class="form-card-heading">
                    <div>
                        <span class="form-icon campaign-icon">⌂</span>
                        <span class="eyebrow eyebrow-coral">CRIE SEU ACESSO</span>
                        <h2 id="titulo-instituicao">Sou instituição</h2>
                        <p>Quero divulgar campanhas</p>
                    </div>
                    <span class="form-step">02</span>
                </div>

                <?php if ($erros['instituicao']): ?>
                    <div class="form-error" role="alert">
                        <ul>
                            <?php foreach ($erros['instituicao'] as $mensagem): ?>
                                <li><?= indexEsc($mensagem) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="cadastro.php#cadastro-instituicao">
                    <input type="hidden" name="acao" value="instituicao">

                    <fieldset class="role-fields" data-role-panel="instituicao">
                        <legend>Dados da instituição</legend>

                        <label for="inst-nome">Nome da instituição</label>
                        <input id="inst-nome" name="nome" type="text" maxlength="100" placeholder="Nome pelo qual é conhecida" required value="<?= indexEsc($i['nome'] ?? '') ?>">

                        <label for="inst-email">E-mail institucional</label>
                        <input id="inst-email" name="email" type="email" maxlength="150" autocomplete="email" placeholder="contato@instituicao.org" required value="<?= indexEsc($i['email'] ?? '') ?>">

                        <label for="inst-endereco">Endereço</label>
                        <input id="inst-endereco" name="endereco" type="text" maxlength="200" autocomplete="street-address" required value="<?= indexEsc($i['endereco'] ?? '') ?>">

                        <label for="inst-area">Área de atuação</label>
                        <select id="inst-area" name="area" required>
                            <?= cadastroOpcoes($areas, $i['area'] ?? '') ?>
                        </select>

                        <label for="inst-disponibilidade">Disponibilidade</label>
                        <select id="inst-disponibilidade" name="disponibilidade" required>
                            <?= cadastroOpcoes($disponibilidades, $i['disponibilidade'] ?? '') ?>
                        </select>

                        <div class="two-fields">
                            <div>
                                <label for="inst-senha">Senha</label>
                                <input id="inst-senha" name="senha" type="password" minlength="6" maxlength="72" autocomplete="new-password" placeholder="Mínimo de 6 caracteres" required>
                            </div>
                            <div>
                                <label for="inst-confirmar">Confirmar senha</label>
                                <input id="inst-confirmar" name="confirmar_senha" type="password" minlength="6" maxlength="72" autocomplete="new-password" placeholder="Digite novamente" required>
                            </div>
                        </div>
                    </fieldset>

                    <label class="terms">
                        <input type="checkbox" name="termos" value="1" required <?= !empty($i['termos']) ? 'checked' : '' ?>>
                        Li e concordo com os termos de uso e a política de privacidade.
                    </label>

                    <button class="button form-submit button-coral" type="submit">Criar meu cadastro <span>→</span></button>
                </form>
            </section>
        </div>

        <p class="form-footnote">Já tem uma conta? <a href="login.php">Faça login</a>.</p>
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