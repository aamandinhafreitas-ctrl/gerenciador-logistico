
<?php
require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mensagem = "";
$endereco_edicao = null;

// MENSAGENS
if (isset($_GET["sucesso"])) {
    $mensagem = "Endereço cadastrado com sucesso!";
}

if (isset($_GET["editado"])) {
    $mensagem = "Endereço atualizado com sucesso!";
}

if (isset($_GET["excluido"])) {
    $mensagem = "Endereço excluído com sucesso!";
}

if (isset($_GET["erro_codigo"])) {
    $mensagem = "Este código já está cadastrado. Informe outro código.";
}

if (isset($_GET["erro_exclusao"])) {
    $mensagem = "Este endereço está vinculado a um frete e não pode ser excluído.";
}

if (isset($_GET["erro"])) {
    $mensagem = "Ocorreu um erro ao processar a operação.";
}

// PROCESSAR FORMULÁRIOS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // EXCLUIR ENDEREÇO
    if (isset($_POST["excluir_id"])) {

        $id = (int) $_POST["excluir_id"];

        try {
            $sql = "DELETE FROM enderecos WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: enderecos.php?excluido=1");
            exit;

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1451) {
                header("Location: enderecos.php?erro_exclusao=1");
            } else {
                header("Location: enderecos.php?erro=1");
            }

            exit;
        }
    }

    // ATUALIZAR ENDEREÇO
    elseif (isset($_POST["atualizar_id"])) {

        $id = (int) $_POST["atualizar_id"];

        $codigo = trim($_POST["codigo"]);
        $identificacao = trim($_POST["identificacao"]);
        $cep = trim($_POST["cep"]);
        $rua = trim($_POST["rua"]);
        $numero = trim($_POST["numero"]);
        $bairro = trim($_POST["bairro"]);
        $cidade = trim($_POST["cidade"]);
        $estado = strtoupper(trim($_POST["estado"]));

        try {

            $sql = "UPDATE enderecos
                    SET codigo = ?,
                        identificacao = ?,
                        cep = ?,
                        rua = ?,
                        numero = ?,
                        bairro = ?,
                        cidade = ?,
                        estado = ?
                    WHERE id = ?";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "ssssssssi",
                $codigo,
                $identificacao,
                $cep,
                $rua,
                $numero,
                $bairro,
                $cidade,
                $estado,
                $id
            );

            $stmt->execute();
            $stmt->close();

            header("Location: enderecos.php?editado=1");
            exit;

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1062) {
                header("Location: enderecos.php?erro_codigo=1");
            } else {
                header("Location: enderecos.php?erro=1");
            }

            exit;
        }
    }

    // CADASTRAR ENDEREÇO
    elseif (isset($_POST["salvar"])) {

        $codigo = trim($_POST["codigo"]);
        $identificacao = trim($_POST["identificacao"]);
        $cep = trim($_POST["cep"]);
        $rua = trim($_POST["rua"]);
        $numero = trim($_POST["numero"]);
        $bairro = trim($_POST["bairro"]);
        $cidade = trim($_POST["cidade"]);
        $estado = strtoupper(trim($_POST["estado"]));

        try {

            $sql = "INSERT INTO enderecos
                    (codigo, identificacao, cep, rua, numero, bairro, cidade, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "ssssssss",
                $codigo,
                $identificacao,
                $cep,
                $rua,
                $numero,
                $bairro,
                $cidade,
                $estado
            );

            $stmt->execute();
            $stmt->close();

            header("Location: enderecos.php?sucesso=1");
            exit;

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1062) {
                header("Location: enderecos.php?erro_codigo=1");
            } else {
                header("Location: enderecos.php?erro=1");
            }

            exit;
        }
    }
}

// BUSCAR ENDEREÇO PARA EDIÇÃO
if (isset($_GET["editar_id"])) {

    $id_editar = (int) $_GET["editar_id"];

    $stmt = $conexao->prepare(
        "SELECT * FROM enderecos WHERE id = ?"
    );

    $stmt->bind_param("i", $id_editar);
    $stmt->execute();

    $endereco_edicao = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$endereco_edicao) {
        $mensagem = "Endereço não encontrado.";
    }
}

// BUSCAR TODOS OS ENDEREÇOS
$sql = "SELECT * FROM enderecos ORDER BY id DESC";
$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Endereços</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- MENU LATERAL -->
        <div class="col-md-3" style="background-color: #243B35; color: white; min-height: 100vh; padding: 25px;">

            <h3>LOGÍSTICA</h3>
            <hr>

            <p><a href="index.php" class="text-white text-decoration-none">Início</a></p>
            <p><a href="produtos.php" class="text-white text-decoration-none">Produtos</a></p>
            <p><a href="enderecos.php" class="text-white text-decoration-none">Endereços</a></p>
            <p><a href="transportadores.php" class="text-white text-decoration-none">Transportadores</a></p>
            <p><a href="fretes.php" class="text-white text-decoration-none">Fretes</a></p>
            <p><a href="relatorios.php" class="text-white text-decoration-none">Relatórios</a></p>

        </div>

        <!-- CONTEÚDO -->
        <div class="col-md-9 p-4" style="background-color: #f4f6f5; min-height: 100vh;">

            <h1 style="color: #263238;">Cadastro de Endereços</h1>

            <p class="text-muted">
                Cadastre os locais de origem e destino.
            </p>

            <hr>

            <!-- MENSAGENS -->
            <?php if ($mensagem != ""): ?>

                <div class="alert alert-info">
                    <?= htmlspecialchars($mensagem) ?>
                </div>

            <?php endif; ?>

            <!-- FORMULÁRIO -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">
                    <?= $endereco_edicao ? "Editar endereço" : "Novo endereço" ?>
                </h4>

                <form method="POST" action="enderecos.php">

                    <?php if ($endereco_edicao): ?>

                        <input
                            type="hidden"
                            name="atualizar_id"
                            value="<?= $endereco_edicao["id"] ?>"
                        >

                    <?php endif; ?>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">Código do endereço</label>

                            <input
                                type="text"
                                name="codigo"
                                class="form-control"
                                placeholder="Ex.: END001"
                                value="<?= htmlspecialchars($endereco_edicao["codigo"] ?? "") ?>"
                                required
                            >

                        </div>

                        <div class="col-md-8 mb-3">

                            <label class="form-label">Identificação do local</label>

                            <input
                                type="text"
                                name="identificacao"
                                class="form-control"
                                placeholder="Ex.: Matriz, Filial ou Depósito"
                                value="<?= htmlspecialchars($endereco_edicao["identificacao"] ?? "") ?>"
                                required
                            >

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">CEP</label>

                            <input
                                type="text"
                                name="cep"
                                class="form-control"
                                placeholder="Digite o CEP"
                                value="<?= htmlspecialchars($endereco_edicao["cep"] ?? "") ?>"
                            >

                        </div>

                        <div class="col-md-8 mb-3">

                            <label class="form-label">Rua</label>

                            <input
                                type="text"
                                name="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($endereco_edicao["rua"] ?? "") ?>"
                            >

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">Número</label>

                            <input
                                type="text"
                                name="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($endereco_edicao["numero"] ?? "") ?>"
                            >

                        </div>

                        <div class="col-md-8 mb-3">

                            <label class="form-label">Bairro</label>

                            <input
                                type="text"
                                name="bairro"
                                class="form-control"
                                value="<?= htmlspecialchars($endereco_edicao["bairro"] ?? "") ?>"
                            >

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-8 mb-3">

                            <label class="form-label">Cidade</label>

                            <input
                                type="text"
                                name="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($endereco_edicao["cidade"] ?? "") ?>"
                            >

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">Estado (UF)</label>

                            <input
                                type="text"
                                name="estado"
                                class="form-control"
                                maxlength="2"
                                value="<?= htmlspecialchars($endereco_edicao["estado"] ?? "") ?>"
                            >

                        </div>

                    </div>

                    <?php if ($endereco_edicao): ?>

                        <button type="submit" class="btn btn-primary">
                            Atualizar endereço
                        </button>

                        <a href="enderecos.php" class="btn btn-secondary">
                            Cancelar edição
                        </a>

                    <?php else: ?>

                        <button type="submit" name="salvar" class="btn btn-success">
                            Salvar endereço
                        </button>

                        <button type="reset" class="btn btn-secondary">
                            Limpar
                        </button>

                    <?php endif; ?>

                </form>

            </div>

            <!-- LISTA DE ENDEREÇOS -->
            <div class="card shadow-sm p-4">

                <h4 class="mb-3">Endereços cadastrados</h4>

                <div class="table-responsive">

                    <table class="table table-striped table-hover align-middle">

                        <thead class="table-dark">

                            <tr>
                                <th>Código</th>
                                <th>Identificação</th>
                                <th>CEP</th>
                                <th>Endereço</th>
                                <th>Cidade/UF</th>
                                <th>Ação</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if ($resultado->num_rows > 0): ?>

                                <?php while ($endereco = $resultado->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($endereco["codigo"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($endereco["identificacao"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($endereco["cep"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($endereco["rua"]) ?>,
                                            <?= htmlspecialchars($endereco["numero"]) ?>

                                            <br>

                                            <small class="text-muted">
                                                <?= htmlspecialchars($endereco["bairro"]) ?>
                                            </small>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($endereco["cidade"]) ?> /
                                            <?= htmlspecialchars($endereco["estado"]) ?>
                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <a
                                                    href="enderecos.php?editar_id=<?= $endereco["id"] ?>"
                                                    class="btn btn-primary btn-sm"
                                                >
                                                    Editar
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="enderecos.php"
                                                    onsubmit="return confirm('Deseja realmente excluir este endereço?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="excluir_id"
                                                        value="<?= $endereco["id"] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm"
                                                    >
                                                        Excluir
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Nenhum endereço cadastrado.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>
