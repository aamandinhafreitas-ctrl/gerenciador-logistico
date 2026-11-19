
<?php
require_once "conexao.php";

$mensagem = "";

if (isset($_GET["sucesso"])) {
    $mensagem = "Produto cadastrado com sucesso!";
}

if (isset($_GET["excluido"])) {
    $mensagem = "Produto excluído com sucesso!";
}

if (isset($_GET["erro_exclusao"])) {
    $mensagem = "Não foi possível excluir. O produto pode estar vinculado a um frete.";
}

// CADASTRAR OU EXCLUIR PRODUTO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // EXCLUIR PRODUTO
    if (isset($_POST["excluir_id"])) {

        $id = (int) $_POST["excluir_id"];

        try {
            $stmt = $conexao->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: produtos.php?excluido=1");
            exit;

        } catch (mysqli_sql_exception $e) {
            header("Location: produtos.php?erro_exclusao=1");
            exit;
        }

    } else {

        // CADASTRAR PRODUTO
        $codigo = $_POST["codigo"];
        $nome = $_POST["nome"];
        $descricao = $_POST["descricao"];
        $quantidade = (int) $_POST["quantidade"];
        $peso = (float) $_POST["peso"];

        $sql = "INSERT INTO produtos (codigo, nome, descricao, quantidade, peso)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssid", $codigo, $nome, $descricao, $quantidade, $peso);

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

// BUSCAR PRODUTOS
$resultado = $conexao->query("SELECT * FROM produtos ORDER BY id DESC");

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

        <div class="col-md-9 p-4" style="background-color: #f4f6f5; min-height: 100vh;">

            <h1 style="color: #263238;">Cadastro de Produtos</h1>
            <p class="text-muted">Cadastre os produtos do sistema.</p>

            <hr>

            <?php if ($mensagem != "") { ?>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php } ?>

            <div class="card shadow-sm p-4 mb-4">

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Código do produto</label>
                        <input type="text" name="codigo" class="form-control" placeholder="Digite o código" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nome do produto</label>
                        <input type="text" name="nome" class="form-control" placeholder="Digite o nome" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição do produto</label>
                        <textarea name="descricao" class="form-control" rows="3" placeholder="Descreva o produto"></textarea>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantidade em estoque</label>
                            <input type="number" name="quantidade" class="form-control" placeholder="Quantidade" min="0" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" name="peso" step="0.01" min="0" class="form-control" placeholder="Peso" required>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-success">Salvar produto</button>
                    <button type="reset" class="btn btn-secondary">Cancelar</button>

                </form>

            </div>

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
                                    <td><?php echo htmlspecialchars($produto["codigo"]); ?></td>
                                    <td><?php echo htmlspecialchars($produto["nome"]); ?></td>
                                    <td><?php echo htmlspecialchars($produto["descricao"] ?? ""); ?></td>
                                    <td><?php echo $produto["quantidade"]; ?></td>
                                    <td><?php echo $produto["peso"]; ?></td>

                                    <td>
                                        <form method="POST" onsubmit="return confirm('Deseja realmente excluir este produto?');">
                                            <input type="hidden" name="excluir_id" value="<?php echo $produto["id"]; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                        </form>
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
