
<?php
require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// CADASTRAR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["salvar"])) {

    $produto_id = (int) $_POST["produto_id"];
    $transportador_id = (int) $_POST["transportador_id"];
    $origem_id = (int) $_POST["origem_id"];
    $destino_id = (int) $_POST["destino_id"];
    $data_saida = $_POST["data_saida"];
    $data_entrega = $_POST["data_entrega"];
    $valor = (float) str_replace(",", ".", $_POST["valor"]);
    $situacao = $_POST["situacao"];

    try {

        $sql = "INSERT INTO fretes
                (produto_id, transportador_id, origem_id, destino_id,
                 data_saida, data_entrega, valor, situacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "iiiissds",
            $produto_id,
            $transportador_id,
            $origem_id,
            $destino_id,
            $data_saida,
            $data_entrega,
            $valor,
            $situacao
        );

        $stmt->execute();

        header("Location: fretes.php?sucesso=1");
        exit;

    } catch (mysqli_sql_exception $e) {

        header("Location: fretes.php?erro=1");
        exit;
    }
}

// EXCLUIR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir_id"])) {

    $id = (int) $_POST["excluir_id"];

    try {

        $sql = "DELETE FROM fretes WHERE id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: fretes.php?excluido=1");
        exit;

    } catch (mysqli_sql_exception $e) {

        header("Location: fretes.php?erro=1");
        exit;
    }
}

// BUSCAR DADOS PARA OS CAMPOS DE SELEÇÃO
$produtos = $conexao->query(
    "SELECT id, codigo, nome FROM produtos ORDER BY nome"
);

$transportadores = $conexao->query(
    "SELECT id, nome FROM transportadores ORDER BY nome"
);

$enderecos = $conexao->query(
    "SELECT id, identificacao, cidade, estado FROM enderecos ORDER BY identificacao"
);

// LISTAR FRETES CADASTRADOS
$sql = "SELECT
            f.id,
            p.nome AS produto,
            t.nome AS transportador,
            o.identificacao AS origem,
            d.identificacao AS destino,
            f.data_saida,
            f.data_entrega,
            f.valor,
            f.situacao
        FROM fretes f
        INNER JOIN produtos p ON f.produto_id = p.id
        INNER JOIN transportadores t ON f.transportador_id = t.id
        INNER JOIN enderecos o ON f.origem_id = o.id
        INNER JOIN enderecos d ON f.destino_id = d.id
        ORDER BY f.id DESC";

$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Fretes</title>

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

            <h1 style="color: #263238;">Cadastro de Fretes</h1>

            <p class="text-muted">
                Registre e acompanhe o transporte de produtos.
            </p>

            <hr>

            <!-- MENSAGENS -->
            <?php if (isset($_GET["sucesso"])): ?>
                <div class="alert alert-success">
                    Frete cadastrado com sucesso!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["excluido"])): ?>
                <div class="alert alert-success">
                    Frete excluído com sucesso!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["erro"])): ?>
                <div class="alert alert-danger">
                    Ocorreu um erro ao processar a operação.
                    Confira se os dados selecionados são válidos.
                </div>
            <?php endif; ?>

            <!-- FORMULÁRIO -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">Novo frete</h4>

                <form method="POST" action="fretes.php">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Produto</label>

                            <select name="produto_id" class="form-select" required>
                                <option value="">Selecione um produto</option>

                                <?php while ($produto = $produtos->fetch_assoc()): ?>
                                    <option value="<?= $produto["id"] ?>">
                                        <?= htmlspecialchars($produto["codigo"] . " - " . $produto["nome"]) ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transportador</label>

                            <select name="transportador_id" class="form-select" required>
                                <option value="">Selecione um transportador</option>

                                <?php while ($transportador = $transportadores->fetch_assoc()): ?>
                                    <option value="<?= $transportador["id"] ?>">
                                        <?= htmlspecialchars($transportador["nome"]) ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Endereço de origem</label>

                            <select name="origem_id" class="form-select" required>
                                <option value="">Selecione a origem</option>

                                <?php while ($endereco = $enderecos->fetch_assoc()): ?>
                                    <option value="<?= $endereco["id"] ?>">
                                        <?= htmlspecialchars($endereco["identificacao"] . " - " . $endereco["cidade"] . "/" . $endereco["estado"]) ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Endereço de destino</label>

                            <select name="destino_id" class="form-select" required>
                                <option value="">Selecione o destino</option>

                                <?php
                                $enderecos_destino = $conexao->query(
                                    "SELECT id, identificacao, cidade, estado FROM enderecos ORDER BY identificacao"
                                );
                                ?>

                                <?php while ($endereco = $enderecos_destino->fetch_assoc()): ?>
                                    <option value="<?= $endereco["id"] ?>">
                                        <?= htmlspecialchars($endereco["identificacao"] . " - " . $endereco["cidade"] . "/" . $endereco["estado"]) ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data de saída</label>
                            <input type="date" name="data_saida" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data de entrega</label>
                            <input type="date" name="data_entrega" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor do frete (R$)</label>
                            <input type="number" name="valor" class="form-control"
                                   min="0" step="0.01" placeholder="0,00" required>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Situação do frete</label>

                        <select name="situacao" class="form-select" required>
                            <option value="">Selecione a situação</option>
                            <option value="Pendente">Pendente</option>
                            <option value="Em transporte">Em transporte</option>
                            <option value="Entregue">Entregue</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>

                    <button type="submit" name="salvar" class="btn btn-success">
                        Salvar frete
                    </button>

                    <button type="reset" class="btn btn-secondary">
                        Cancelar
                    </button>

                </form>

            </div>

            <!-- LISTAGEM DE FRETES -->
            <div class="card shadow-sm p-4">

                <h4 class="mb-3">Fretes cadastrados</h4>

                <div class="table-responsive">

                    <table class="table table-striped table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Transportador</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Saída</th>
                                <th>Entrega</th>
                                <th>Valor</th>
                                <th>Situação</th>
                                <th>Ação</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($resultado->num_rows > 0): ?>

                                <?php while ($frete = $resultado->fetch_assoc()): ?>

                                    <tr>

                                        <td><?= $frete["id"] ?></td>

                                        <td><?= htmlspecialchars($frete["produto"]) ?></td>

                                        <td><?= htmlspecialchars($frete["transportador"]) ?></td>

                                        <td><?= htmlspecialchars($frete["origem"]) ?></td>

                                        <td><?= htmlspecialchars($frete["destino"]) ?></td>

                                        <td>
                                            <?= date("d/m/Y", strtotime($frete["data_saida"])) ?>
                                        </td>

                                        <td>
                                            <?= date("d/m/Y", strtotime($frete["data_entrega"])) ?>
                                        </td>

                                        <td>
                                            R$ <?= number_format($frete["valor"], 2, ",", ".") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($frete["situacao"]) ?>
                                        </td>

                                        <td>

                                            <form method="POST"
                                                  action="fretes.php"
                                                  onsubmit="return confirm('Deseja realmente excluir este frete?');">

                                                <input type="hidden"
                                                       name="excluir_id"
                                                       value="<?= $frete["id"] ?>">

                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Excluir
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        Nenhum frete cadastrado.
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
