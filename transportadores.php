
<?php
require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// MENSAGENS
$mensagens = [
    "sucesso" => "Transportador cadastrado com sucesso!",
    "editado" => "Transportador atualizado com sucesso!",
    "excluido" => "Transportador excluído com sucesso!",
    "erro_exclusao" => "Este transportador está vinculado a um frete e não pode ser excluído.",
    "erro" => "Ocorreu um erro ao processar a operação."
];

// ATUALIZAR TRANSPORTADOR
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar_id"])) {

    $id = (int) $_POST["atualizar_id"];
    $nome = trim($_POST["nome"]);
    $telefone = trim($_POST["telefone"]);
    $email = trim($_POST["email"]);

    try {
        $sql = "UPDATE transportadores
                SET nome = ?, telefone = ?, email = ?
                WHERE id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssi", $nome, $telefone, $email, $id);
        $stmt->execute();

        header("Location: transportadores.php?editado=1");
        exit;

    } catch (mysqli_sql_exception $e) {
        header("Location: transportadores.php?erro=1");
        exit;
    }
}

// CADASTRAR TRANSPORTADOR
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["salvar"])) {

    $nome = trim($_POST["nome"]);
    $telefone = trim($_POST["telefone"]);
    $email = trim($_POST["email"]);

    try {
        $sql = "INSERT INTO transportadores (nome, telefone, email)
                VALUES (?, ?, ?)";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sss", $nome, $telefone, $email);
        $stmt->execute();

        header("Location: transportadores.php?sucesso=1");
        exit;

    } catch (mysqli_sql_exception $e) {
        header("Location: transportadores.php?erro=1");
        exit;
    }
}

// EXCLUIR TRANSPORTADOR
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir_id"])) {

    $id = (int) $_POST["excluir_id"];

    try {
        // Verifica se o transportador está vinculado a algum frete
        $sql = "SELECT COUNT(*) AS total
                FROM fretes
                WHERE transportador_id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $vinculo = $stmt->get_result()->fetch_assoc();

        if ($vinculo["total"] > 0) {
            header("Location: transportadores.php?erro_exclusao=1");
            exit;
        }

        $sql = "DELETE FROM transportadores WHERE id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: transportadores.php?excluido=1");
        exit;

    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1451) {
            header("Location: transportadores.php?erro_exclusao=1");
        } else {
            header("Location: transportadores.php?erro=1");
        }

        exit;
    }
}

// BUSCAR TRANSPORTADOR PARA EDIÇÃO
$transportador_edicao = null;

if (isset($_GET["editar_id"])) {

    $id = (int) $_GET["editar_id"];

    $sql = "SELECT * FROM transportadores WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $transportador_edicao = $stmt->get_result()->fetch_assoc();
}

// LISTAR TRANSPORTADORES
$sql = "SELECT * FROM transportadores ORDER BY id DESC";
$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Transportadores</title>

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

            <h1 style="color: #263238;">Cadastro de Transportadores</h1>
            <p class="text-muted">Cadastre as empresas ou profissionais responsáveis pelo transporte.</p>
            <hr>

            <!-- MENSAGENS -->
            <?php foreach ($mensagens as $chave => $mensagem): ?>
                <?php if (isset($_GET[$chave])): ?>
                    <div class="alert <?= in_array($chave, ["erro", "erro_exclusao"]) ? "alert-danger" : "alert-success" ?>">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- FORMULÁRIO -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">
                    <?= $transportador_edicao ? "Editar transportador" : "Novo transportador" ?>
                </h4>

                <form method="POST" action="transportadores.php">

                    <?php if ($transportador_edicao): ?>
                        <input
                            type="hidden"
                            name="atualizar_id"
                            value="<?= $transportador_edicao["id"] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nome do transportador</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            placeholder="Digite o nome"
                            value="<?= htmlspecialchars($transportador_edicao["nome"] ?? "") ?>"
                            required>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input
                                type="text"
                                name="telefone"
                                class="form-control"
                                placeholder="(00) 00000-0000"
                                value="<?= htmlspecialchars($transportador_edicao["telefone"] ?? "") ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-mail</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="exemplo@email.com"
                                value="<?= htmlspecialchars($transportador_edicao["email"] ?? "") ?>">
                        </div>

                    </div>

                    <?php if ($transportador_edicao): ?>

                        <button type="submit" class="btn btn-primary">
                            Atualizar transportador
                        </button>

                        <a href="transportadores.php" class="btn btn-secondary">
                            Cancelar edição
                        </a>

                    <?php else: ?>

                        <button type="submit" name="salvar" class="btn btn-success">
                            Salvar transportador
                        </button>

                        <button type="reset" class="btn btn-secondary">
                            Limpar
                        </button>

                    <?php endif; ?>

                </form>

            </div>

            <!-- LISTAGEM -->
            <div class="card shadow-sm p-4">

                <h4 class="mb-3">Transportadores cadastrados</h4>

                <div class="table-responsive">

                    <table class="table table-striped table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>E-mail</th>
                                <th>Ação</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($resultado->num_rows > 0): ?>

                                <?php while ($transportador = $resultado->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <?= $transportador["id"] ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($transportador["nome"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($transportador["telefone"] ?? "") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($transportador["email"] ?? "") ?>
                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <a
                                                    href="transportadores.php?editar_id=<?= $transportador["id"] ?>"
                                                    class="btn btn-primary btn-sm">
                                                    Editar
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="transportadores.php"
                                                    onsubmit="return confirm('Deseja realmente excluir este transportador?');">

                                                    <input
                                                        type="hidden"
                                                        name="excluir_id"
                                                        value="<?= $transportador["id"] ?>">

                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        Excluir
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Nenhum transportador cadastrado.
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
