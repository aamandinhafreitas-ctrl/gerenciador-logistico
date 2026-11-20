
<?php
require_once "conexao.php";

// FILTRO POR PERÍODO
$data_inicio = $_GET["data_inicio"] ?? "";
$data_fim = $_GET["data_fim"] ?? "";

$filtro = "";
$parametros = [];
$tipos = "";

if ($data_inicio !== "" && $data_fim !== "") {
    $filtro = " WHERE f.data_saida BETWEEN ? AND ?";
    $parametros = [$data_inicio, $data_fim];
    $tipos = "ss";
} elseif ($data_inicio !== "") {
    $filtro = " WHERE f.data_saida >= ?";
    $parametros = [$data_inicio];
    $tipos = "s";
} elseif ($data_fim !== "") {
    $filtro = " WHERE f.data_saida <= ?";
    $parametros = [$data_fim];
    $tipos = "s";
}

// CONTADORES GERAIS
$total_produtos = $conexao->query(
    "SELECT COUNT(*) AS total FROM produtos"
)->fetch_assoc()["total"];

$total_enderecos = $conexao->query(
    "SELECT COUNT(*) AS total FROM enderecos"
)->fetch_assoc()["total"];

$total_transportadores = $conexao->query(
    "SELECT COUNT(*) AS total FROM transportadores"
)->fetch_assoc()["total"];

$total_fretes = $conexao->query(
    "SELECT COUNT(*) AS total FROM fretes"
)->fetch_assoc()["total"];

// VALOR TOTAL DOS FRETES NO PERÍODO
$sql_valor = "SELECT COALESCE(SUM(f.valor), 0) AS total
              FROM fretes f" . $filtro;

$stmt_valor = $conexao->prepare($sql_valor);

if (!empty($parametros)) {
    $stmt_valor->bind_param($tipos, ...$parametros);
}

$stmt_valor->execute();
$valor_total = $stmt_valor->get_result()->fetch_assoc()["total"];
$stmt_valor->close();

// FRETES POR SITUAÇÃO NO PERÍODO
$sql_situacoes = "SELECT f.situacao, COUNT(*) AS total
                  FROM fretes f
                  $filtro
                  GROUP BY f.situacao";

$stmt_situacoes = $conexao->prepare($sql_situacoes);

if (!empty($parametros)) {
    $stmt_situacoes->bind_param($tipos, ...$parametros);
}

$stmt_situacoes->execute();
$situacoes = $stmt_situacoes->get_result();

// LISTAGEM DOS FRETES E SEUS PRODUTOS
$sql = "SELECT
            f.id,
            COALESCE(
                GROUP_CONCAT(
                    CONCAT(p.nome, ' (Qtd: ', fp.quantidade, ')')
                    ORDER BY p.nome
                    SEPARATOR ', '
                ),
                'Sem produtos'
            ) AS produtos,
            t.nome AS transportador,
            o.identificacao AS origem,
            d.identificacao AS destino,
            f.data_saida,
            f.data_entrega,
            f.valor,
            f.situacao
        FROM fretes f
        LEFT JOIN frete_produtos fp ON f.id = fp.frete_id
        LEFT JOIN produtos p ON fp.produto_id = p.id
        LEFT JOIN transportadores t ON f.transportador_id = t.id
        LEFT JOIN enderecos o ON f.origem_id = o.id
        LEFT JOIN enderecos d ON f.destino_id = d.id
        $filtro
        GROUP BY
            f.id,
            t.nome,
            o.identificacao,
            d.identificacao,
            f.data_saida,
            f.data_entrega,
            f.valor,
            f.situacao
        ORDER BY f.id DESC";

$stmt_fretes = $conexao->prepare($sql);

if (!empty($parametros)) {
    $stmt_fretes->bind_param($tipos, ...$parametros);
}

$stmt_fretes->execute();
$fretes = $stmt_fretes->get_result();

// URL PARA EXPORTAÇÃO COM O MESMO FILTRO
$url_exportacao = "exportar_fretes.php";

if ($data_inicio !== "" || $data_fim !== "") {
    $url_exportacao .= "?" . http_build_query([
        "data_inicio" => $data_inicio,
        "data_fim" => $data_fim
    ]);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Relatórios - Sistema de Logística</title>

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

            <h1 style="color: #263238;">Relatórios</h1>

            <p class="text-muted">
                Acompanhe os indicadores e movimentações do sistema.
            </p>

            <hr>

            <!-- INDICADORES -->
            <div class="row">

                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm p-3">
                        <h6 class="text-muted">Produtos</h6>
                        <h2><?= $total_produtos ?></h2>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm p-3">
                        <h6 class="text-muted">Endereços</h6>
                        <h2><?= $total_enderecos ?></h2>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm p-3">
                        <h6 class="text-muted">Transportadores</h6>
                        <h2><?= $total_transportadores ?></h2>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm p-3">
                        <h6 class="text-muted">Fretes</h6>
                        <h2><?= $total_fretes ?></h2>
                    </div>
                </div>

            </div>

            <!-- VALOR TOTAL -->
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="text-muted">Valor total dos fretes no período</h5>

                <h2 class="text-success">
                    R$ <?= number_format($valor_total, 2, ",", ".") ?>
                </h2>
            </div>

            <!-- FILTRO POR PERÍODO -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">Filtrar fretes por período</h4>

                <form method="GET" action="relatorios.php">

                    <div class="row align-items-end">

                        <div class="col-md-4 mb-3">
                            <label for="data_inicio" class="form-label">
                                Data inicial
                            </label>

                            <input
                                type="date"
                                name="data_inicio"
                                id="data_inicio"
                                class="form-control"
                                value="<?= htmlspecialchars($data_inicio) ?>"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="data_fim" class="form-label">
                                Data final
                            </label>

                            <input
                                type="date"
                                name="data_fim"
                                id="data_fim"
                                class="form-control"
                                value="<?= htmlspecialchars($data_fim) ?>"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary">
                                Filtrar
                            </button>

                            <a href="relatorios.php" class="btn btn-secondary">
                                Limpar
                            </a>
                        </div>

                    </div>

                </form>

                <small class="text-muted">
                    O filtro considera a data de saída dos fretes.
                </small>

            </div>

            <!-- SITUAÇÃO DOS FRETES -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">Fretes por situação</h4>

                <div class="table-responsive">

                    <table class="table table-striped align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>Situação</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($situacoes->num_rows > 0): ?>

                                <?php while ($situacao = $situacoes->fetch_assoc()): ?>

                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($situacao["situacao"] ?? "") ?>
                                        </td>

                                        <td>
                                            <?= $situacao["total"] ?>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        Nenhum frete encontrado no período.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- RELAÇÃO DE FRETES -->
            <div class="card shadow-sm p-4">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <h4>Relação de fretes</h4>

                    <div>
                        <a href="<?= htmlspecialchars($url_exportacao) ?>" class="btn btn-primary">
                            Exportar CSV
                        </a>

                        <button onclick="window.print()" class="btn btn-success">
                            Imprimir relatório
                        </button>
                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-striped table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Produtos e quantidades</th>
                                <th>Transportador</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Saída</th>
                                <th>Entrega</th>
                                <th>Valor</th>
                                <th>Situação</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($fretes->num_rows > 0): ?>

                                <?php while ($frete = $fretes->fetch_assoc()): ?>

                                    <tr>

                                        <td><?= $frete["id"] ?></td>

                                        <td><?= htmlspecialchars($frete["produtos"]) ?></td>

                                        <td><?= htmlspecialchars($frete["transportador"] ?? "") ?></td>

                                        <td><?= htmlspecialchars($frete["origem"] ?? "") ?></td>

                                        <td><?= htmlspecialchars($frete["destino"] ?? "") ?></td>

                                        <td>
                                            <?= !empty($frete["data_saida"])
                                                ? date("d/m/Y", strtotime($frete["data_saida"]))
                                                : "" ?>
                                        </td>

                                        <td>
                                            <?= !empty($frete["data_entrega"])
                                                ? date("d/m/Y", strtotime($frete["data_entrega"]))
                                                : "" ?>
                                        </td>

                                        <td>
                                            R$ <?= number_format($frete["valor"], 2, ",", ".") ?>
                                        </td>

                                        <td><?= htmlspecialchars($frete["situacao"] ?? "") ?></td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        Nenhum frete encontrado no período.
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
