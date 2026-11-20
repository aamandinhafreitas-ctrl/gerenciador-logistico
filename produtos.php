
<?php
require_once "conexao.php";

$mensagem = "";
$produto_edicao = null;

// MENSAGENS
if (isset($_GET["sucesso"])) {
    $mensagem = "Produto cadastrado com sucesso!";
}

if (isset($_GET["editado"])) {
    $mensagem = "Produto atualizado com sucesso!";
}

if (isset($_GET["excluido"])) {
    $mensagem = "Produto excluído com sucesso!";
}

if (isset($_GET["erro_exclusao"])) {
    $mensagem = "Não foi possível excluir. O produto pode estar vinculado a um frete.";
}

// PROCESSAR FORMULÁRIOS
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // EXCLUIR PRODUTO
    if (isset($_POST["excluir_id"])) {

        $id = (int) $_POST["excluir_id"];

        try {
            $stmt = $conexao->prepare(
                "DELETE FROM produtos WHERE id = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: produtos.php?excluido=1");
            exit;

        } catch (mysqli_sql_exception $e) {
            header("Location: produtos.php?erro_exclusao=1");
            exit;
        }
    }

    // ATUALIZAR PRODUTO
    elseif (isset($_POST["atualizar_id"])) {

        $id = (int) $_POST["atualizar_id"];
        $codigo = $_POST["codigo"];
        $nome = $_POST["nome"];
        $descricao = $_POST["descricao"];
        $quantidade = (int) $_POST["quantidade"];
        $peso = (float) $_POST["peso"];

        $sql = "UPDATE produtos
                SET codigo = ?, nome = ?, descricao = ?,
                    quantidade = ?, peso = ?
                WHERE id = ?";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "sssidi",
            $codigo,
            $nome,
            $descricao,
            $quantidade,
            $peso,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: produtos.php?editado=1");
            exit;
        } else {
            $mensagem = "Erro ao atualizar: " . $stmt->error;
            $stmt->close();
        }
    }

    // CADASTRAR PRODUTO
    else {

        $codigo = $_POST["codigo"];
        $nome = $_POST["nome"];
        $descricao = $_POST["descricao"];
        $quantidade = (int) $_POST["quantidade"];
        $peso = (float) $_POST["peso"];

        $sql = "INSERT INTO produtos
                (codigo, nome, descricao, quantidade, peso)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "sssid",
            $codigo,
            $nome,
            $descricao,
            $quantidade,
            $peso
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: produtos.php?sucesso=1");
            exit;
        } else {
            $mensagem = "Erro ao cadastrar: " . $stmt->error;
            $stmt->close();
        }
    }
}

// BUSCAR PRODUTO PARA EDIÇÃO
if (isset($_GET["editar_id"])) {

    $id_editar = (int) $_GET["editar_id"];

    $stmt = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $stmt->bind_param("i", $id_editar);
    $stmt->execute();

    $produto_edicao = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$produto_edicao) {
        $mensagem = "Produto não encontrado.";
    }
}

// BUSCAR TODOS OS PRODUTOS
$resultado = $conexao->query(
    "SELECT * FROM produtos ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Produtos</title>

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

            <h1 style="color: #263238;">Cadastro de Produtos</h1>

            <p class="text-muted">
                Cadastre e gerencie os produtos do sistema.
            </p>

            <hr>

            <?php if ($mensagem != "") { ?>

                <div class="alert alert-info">
                    <?= htmlspecialchars($mensagem) ?>
                </div>

            <?php } ?>

            <!-- FORMULÁRIO -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">
                    <?= $produto_edicao ? "Editar produto" : "Novo produto" ?>
                </h4>

                <form method="POST">

                    <?php if ($produto_edicao) { ?>

                        <input
                            type="hidden"
                            name="atualizar_id"
                            value="<?= $produto_edicao["id"] ?>"
                        >

                    <?php } ?>

                    <div class="mb-3">

                        <label class="form-label">Código do produto</label>

                        <input
                            type="text"
                            name="codigo"
                            class="form-control"
                            placeholder="Digite o código"
                            value="<?= htmlspecialchars($produto_edicao["codigo"] ?? "") ?>"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Nome do produto</label>

                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            placeholder="Digite o nome"
                            value="<?= htmlspecialchars($produto_edicao["nome"] ?? "") ?>"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Descrição do produto</label>

                        <textarea
                            name="descricao"
                            class="form-control"
                            rows="3"
                            placeholder="Descreva o produto"
                        ><?= htmlspecialchars($produto_edicao["descricao"] ?? "") ?></textarea>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Quantidade em estoque</label>

                            <input
                                type="number"
                                name="quantidade"
                                class="form-control"
                                min="0"
                                value="<?= htmlspecialchars((string)($produto_edicao["quantidade"] ?? "")) ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Peso (kg)</label>

                            <input
                                type="number"
                                name="peso"
                                step="0.01"
                                min="0"
                                class="form-control"
                                placeholder="Peso"
                                value="<?= htmlspecialchars((string)($produto_edicao["peso"] ?? "")) ?>"
                                required
                            >

                        </div>

                    </div>

                    <?php if ($produto_edicao) { ?>

                        <button type="submit" class="btn btn-primary">
                            Atualizar produto
                        </button>

                        <a href="produtos.php" class="btn btn-secondary">
                            Cancelar edição
                        </a>

                    <?php } else { ?>

                        <button type="submit" class="btn btn-success">
                            Salvar produto
                        </button>

                        <button type="reset" class="btn btn-secondary">
                            Limpar
                        </button>

                    <?php } ?>

                </form>

            </div>

            <!-- LISTAGEM DE PRODUTOS -->
            <div class="card shadow-sm p-4">

                <h4 class="mb-3">Produtos cadastrados</h4>

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Código</th>
                                <th>Produto</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                                <th>Peso (kg)</th>
                                <th>Ação</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($produto = $resultado->fetch_assoc()) { ?>

                                <tr>

                                    <td><?= htmlspecialchars($produto["codigo"]) ?></td>

                                    <td><?= htmlspecialchars($produto["nome"]) ?></td>

                                    <td><?= htmlspecialchars($produto["descricao"] ?? "") ?></td>

                                    <td><?= $produto["quantidade"] ?></td>

                                    <td><?= $produto["peso"] ?></td>

                                    <td>

                                        <div class="d-flex gap-2">

                                            <a
                                                href="produtos.php?editar_id=<?= $produto["id"] ?>"
                                                class="btn btn-primary btn-sm"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                onsubmit="return confirm('Deseja realmente excluir este produto?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="excluir_id"
                                                    value="<?= $produto["id"] ?>"
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

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>
