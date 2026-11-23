
<?php

// CONEXÃO COM O BANCO
$conexao = new PDO(
    "mysql:host=localhost;dbname=volunteerconnect;charset=utf8",
    "root",
    "Senai@123"
);

$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mensagem = "";

// CADASTROS
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {

        // CADASTRAR VOLUNTÁRIO
        if ($_POST["tipo"] == "voluntario") {

            $sql = "INSERT INTO voluntarios
            (nome, endereco, cpf, email, area_atuacao, disponibilidade)
            VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            $stmt->execute([
                $_POST["nome"],
                $_POST["endereco"],
                $_POST["cpf"],
                $_POST["email"],
                $_POST["area_atuacao"],
                $_POST["disponibilidade"]
            ]);

            $mensagem = "Voluntário cadastrado!";
        }

        // CADASTRAR INSTITUIÇÃO
        if ($_POST["tipo"] == "instituicao") {

            $sql = "INSERT INTO instituicoes
            (nome, area, endereco, email, disponibilidade)
            VALUES (?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            $stmt->execute([
                $_POST["nome"],
                $_POST["area"],
                $_POST["endereco"],
                $_POST["email"],
                $_POST["disponibilidade"]
            ]);

            $mensagem = "Instituição cadastrada!";
        }

        // CADASTRAR CAMPANHA
        if ($_POST["tipo"] == "campanha") {

            $sql = "INSERT INTO campanhas
            (id_instituicao, nome, descricao, endereco, horario,
            disponibilidade, status, vagas, vagasTotais)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            $stmt->execute([
                $_POST["id_instituicao"],
                $_POST["nome"],
                $_POST["descricao"],
                $_POST["endereco"],
                $_POST["horario"],
                $_POST["disponibilidade"],
                $_POST["status"],
                $_POST["vagas"],
                $_POST["vagasTotais"]
            ]);

            $mensagem = "Campanha cadastrada!";
        }

    } catch (PDOException $e) {
        $mensagem = "Erro no cadastro: " . $e->getMessage();
    }
}

// LISTAR INSTITUIÇÕES
$instituicoes = $conexao->query(
    "SELECT id, nome FROM instituicoes ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>VolunteerConnect</title>
</head>

<body>

<h1>VolunteerConnect - Cadastros</h1>

<p><?= htmlspecialchars($mensagem) ?></p>

<hr>

<!-- VOLUNTÁRIO -->

<h2>Cadastro de Voluntário</h2>

<form method="POST">

    <input type="hidden" name="tipo" value="voluntario">

    Nome:
    <input type="text" name="nome" required><br><br>

    Endereço:
    <input type="text" name="endereco" required><br><br>

    CPF:
    <input type="text" name="cpf"><br><br>

    E-mail:
    <input type="email" name="email" required><br><br>

    Área de atuação:
    <select name="area_atuacao" required>
        <option value="meio ambiente">Meio ambiente</option>
        <option value="educacao">Educação</option>
        <option value="assistencia social">Assistência social</option>
        <option value="saude">Saúde</option>
        <option value="protecao animal">Proteção animal</option>
        <option value="cultura e esportes">Cultura e esportes</option>
    </select><br><br>

    Disponibilidade:
    <select name="disponibilidade" required>
        <option value="manha">Manhã</option>
        <option value="tarde">Tarde</option>
        <option value="noite">Noite</option>
        <option value="fim de semana">Fim de semana</option>
        <option value="qualquer horario">Qualquer horário</option>
    </select><br><br>

    <button type="submit">Cadastrar Voluntário</button>

</form>

<hr>

<!-- INSTITUIÇÃO -->

<h2>Cadastro de Instituição</h2>

<form method="POST">

    <input type="hidden" name="tipo" value="instituicao">

    Nome:
    <input type="text" name="nome" required><br><br>

    Área:
    <select name="area" required>
        <option value="meio ambiente">Meio ambiente</option>
        <option value="educacao">Educação</option>
        <option value="assistencia social">Assistência social</option>
        <option value="saude">Saúde</option>
        <option value="protecao animal">Proteção animal</option>
        <option value="cultura e esportes">Cultura e esportes</option>
    </select><br><br>

    Endereço:
    <input type="text" name="endereco" required><br><br>

    E-mail:
    <input type="email" name="email" required><br><br>

    Disponibilidade:
    <select name="disponibilidade" required>
        <option value="manha">Manhã</option>
        <option value="tarde">Tarde</option>
        <option value="noite">Noite</option>
        <option value="fim de semana">Fim de semana</option>
        <option value="qualquer horario">Qualquer horário</option>
    </select><br><br>

    <button type="submit">Cadastrar Instituição</button>

</form>

<hr>

<!-- CAMPANHA -->

<h2>Cadastro de Campanha</h2>

<form method="POST">

    <input type="hidden" name="tipo" value="campanha">

    Instituição responsável:
    <select name="id_instituicao" required>

        <option value="">Selecione</option>

        <?php foreach ($instituicoes as $i) { ?>

            <option value="<?= $i["id"] ?>">
                <?= htmlspecialchars($i["nome"]) ?>
            </option>

        <?php } ?>

    </select><br><br>

    Nome:
    <input type="text" name="nome" required><br><br>

    Descrição:
    <textarea name="descricao"></textarea><br><br>

    Endereço:
    <input type="text" name="endereco" required><br><br>

    Horário:
    <input type="text" name="horario" required><br><br>

    Disponibilidade:
    <select name="disponibilidade" required>
        <option value="manha">Manhã</option>
        <option value="tarde">Tarde</option>
        <option value="noite">Noite</option>
        <option value="fim de semana">Fim de semana</option>
        <option value="qualquer horario">Qualquer horário</option>
    </select><br><br>

    Status:
    <select name="status" required>
        <option value="planejada">Planejada</option>
        <option value="ativa">Ativa</option>
        <option value="encerrada">Encerrada</option>
        <option value="cancelada">Cancelada</option>
    </select><br><br>

    Vagas disponíveis:
    <input type="number" name="vagas" min="0" required><br><br>

    Total de vagas:
    <input type="number" name="vagasTotais" min="1" required><br><br>

    <button type="submit">Cadastrar Campanha</button>

</form>

</body>
</html>
