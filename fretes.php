
<?php
require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// MENSAGENS
$mensagens = [
    "sucesso" => "Frete cadastrado com sucesso!",
    "editado" => "Frete atualizado com sucesso!",
    "excluido" => "Frete excluído com sucesso!",
    "erro" => "Ocorreu um erro ao processar a operação. Confira os dados informados."
];

// ATUALIZAR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar_id"])) {

    $transacao_iniciada = false;

    $id = (int) $_POST["atualizar_id"];
    $produtos_selecionados = $_POST["produtos"] ?? [];
    $quantidades = $_POST["quantidades"] ?? [];

    $transportador_id = (int) $_POST["transportador_id"];
    $origem_id = (int) $_POST["origem_id"];
    $destino_id = (int) $_POST["destino_id"];
    $data_saida = $_POST["data_saida"];
    $data_entrega = $_POST["data_entrega"];
    $valor = (float) str_replace(",", ".", $_POST["valor"]);
    $situacao = $_POST["situacao"];

    try {

        if (empty($produtos_selecionados)) {
            throw new Exception("Selecione pelo menos um produto.");
        }

        if ($id <= 0 || $transportador_id <= 0 || $origem_id <= 0 || $destino_id <= 0 || $valor < 0) {
            throw new Exception("Dados inválidos.");
        }

        $conexao->begin_transaction();
        $transacao_iniciada = true;

        // ATUALIZAR DADOS DO FRETE
        $sql = "UPDATE fretes
                SET transportador_id = ?,
                    origem_id = ?,
                    destino_id = ?,
                    data_saida = ?,
                    data_entrega = ?,
                    valor = ?,
                    situacao = ?
                WHERE id = ?";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "iiissdsi",
            $transportador_id,
            $origem_id,
            $destino_id,
            $data_saida,
            $data_entrega,
            $valor,
            $situacao,
            $id
        );

        $stmt->execute();

        // REMOVER OS PRODUTOS ANTIGOS DO FRETE
        $sql = "DELETE FROM frete_produtos WHERE frete_id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // INSERIR NOVAMENTE OS PRODUTOS SELECIONADOS
        $sql_item = "INSERT INTO frete_produtos
                     (frete_id, produto_id, quantidade)
                     VALUES (?, ?, ?)";

        $stmt_item = $conexao->prepare($sql_item);

        foreach ($produtos_selecionados as $produto_id) {

            $produto_id = (int) $produto_id;
            $quantidade = (int) ($quantidades[$produto_id] ?? 1);

            if ($produto_id <= 0 || $quantidade <= 0) {
                throw new Exception("Produto ou quantidade inválida.");
            }

            $stmt_item->bind_param(
                "iii",
                $id,
                $produto_id,
                $quantidade
            );

            $stmt_item->execute();
        }

        $conexao->commit();
        $transacao_iniciada = false;

        header("Location: fretes.php?editado=1");
        exit;

    } catch (Throwable $e) {

        if ($transacao_iniciada) {
            $conexao->rollback();
        }

        header("Location: fretes.php?erro=1");
        exit;
    }
}

// CADASTRAR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["salvar"])) {

    $transacao_iniciada = false;

    $produtos_selecionados = $_POST["produtos"] ?? [];
    $quantidades = $_POST["quantidades"] ?? [];

    $transportador_id = (int) $_POST["transportador_id"];
    $origem_id = (int) $_POST["origem_id"];
    $destino_id = (int) $_POST["destino_id"];
    $data_saida = $_POST["data_saida"];
    $data_entrega = $_POST["data_entrega"];
    $valor = (float) str_replace(",", ".", $_POST["valor"]);
    $situacao = $_POST["situacao"];

    try {

        if (empty($produtos_selecionados)) {
            throw new Exception("Selecione pelo menos um produto.");
        }

        if ($transportador_id <= 0 || $origem_id <= 0 || $destino_id <= 0 || $valor < 0) {
            throw new Exception("Dados inválidos.");
        }

        $conexao->begin_transaction();
        $transacao_iniciada = true;

        // CADASTRAR O FRETE
        $sql = "INSERT INTO fretes
                (transportador_id, origem_id, destino_id,
                 data_saida, data_entrega, valor, situacao)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "iiissds",
            $transportador_id,
            $origem_id,
            $destino_id,
            $data_saida,
            $data_entrega,
            $valor,
            $situacao
        );

        $stmt->execute();

        $frete_id = $conexao->insert_id;

        // CADASTRAR OS PRODUTOS DO FRETE
        $sql_item = "INSERT INTO frete_produtos
                     (frete_id, produto_id, quantidade)
                     VALUES (?, ?, ?)";

        $stmt_item = $conexao->prepare($sql_item);

        foreach ($produtos_selecionados as $produto_id) {

            $produto_id = (int) $produto_id;
            $quantidade = (int) ($quantidades[$produto_id] ?? 1);

            if ($produto_id <= 0 || $quantidade <= 0) {
                throw new Exception("Produto ou quantidade inválida.");
            }

            $stmt_item->bind_param(
                "iii",
                $frete_id,
                $produto_id,
                $quantidade
            );

            $stmt_item->execute();
        }

        $conexao->commit();
        $transacao_iniciada = false;

        header("Location: fretes.php?sucesso=1");
        exit;

    } catch (Throwable $e) {

        if ($transacao_iniciada) {
            $conexao->rollback();
        }

        header("Location: fretes.php?erro=1");
        exit;
    }
}

// EXCLUIR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir_id"])) {

    $transacao_iniciada = false;
    $id = (int) $_POST["excluir_id"];

    try {

        $conexao->begin_transaction();
        $transacao_iniciada = true;

        // EXCLUIR PRIMEIRO OS PRODUTOS VINCULADOS
        $stmt = $conexao->prepare(
            "DELETE FROM frete_produtos WHERE frete_id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        // EXCLUIR O FRETE
        $stmt = $conexao->prepare(
            "DELETE FROM fretes WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conexao->commit();
        $transacao_iniciada = false;

        header("Location: fretes.php?excluido=1");
        exit;

    } catch (Throwable $e) {

        if ($transacao_iniciada) {
            $conexao->rollback();
        }

        header("Location: fretes.php?erro=1");
        exit;
    }
}

// BUSCAR FRETE PARA EDIÇÃO
$frete_edicao = null;
$produtos_do_frete = [];

if (isset($_GET["editar_id"])) {

    $id = (int) $_GET["editar_id"];

    $sql = "SELECT * FROM fretes WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $frete_edicao = $stmt->get_result()->fetch_assoc();

    if ($frete_edicao) {

        $sql = "SELECT produto_id, quantidade
                FROM frete_produtos
                WHERE frete_id = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado_itens = $stmt->get_result();

        while ($item = $resultado_itens->fetch_assoc()) {
            $produtos_do_frete[$item["produto_id"]] = $item["quantidade"];
        }
    }
}

// BUSCAR DADOS PARA OS CAMPOS DE SELEÇÃO
$produtos_resultado = $conexao->query(
    "SELECT id, codigo, nome FROM produtos ORDER BY nome"
);

$lista_produtos = $produtos_resultado->fetch_all(MYSQLI_ASSOC);

$transportadores_resultado = $conexao->query(
    "SELECT id, nome FROM transportadores ORDER BY nome"
);

$lista_transportadores = $transportadores_resultado->fetch_all(MYSQLI_ASSOC);

$enderecos_resultado = $conexao->query(
    "SELECT id, identificacao, cidade, estado
     FROM enderecos ORDER BY identificacao"
);

$lista_enderecos = $enderecos_resultado->fetch_all(MYSQLI_ASSOC);

// LISTAR FRETES CADASTRADOS
$sql = "SELECT
            f.id,
            COALESCE(pf.produtos, 'Sem produtos') AS produtos,
            t.nome AS transportador,
            o.identificacao AS origem,
            d.identificacao AS destino,
            f.data_saida,
            f.data_entrega,
            f.valor,
            f.situacao
        FROM fretes f

        LEFT JOIN (
            SELECT
                fp.frete_id,
                GROUP_CONCAT(
                    CONCAT(p.nome, ' (Qtd: ', fp.quantidade, ')')
                    ORDER BY p.nome
                    SEPARATOR ', '
                ) AS produtos
            FROM frete_produtos fp
            INNER JOIN produtos p ON fp.produto_id = p.id
            GROUP BY fp.frete_id
        ) pf ON f.id = pf.frete_id

        LEFT JOIN transportadores t
            ON f.transportador_id = t.id

        LEFT JOIN enderecos o
            ON f.origem_id = o.id

        LEFT JOIN enderecos d
            ON f.destino_id = d.id

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
            <?php foreach ($mensagens as $chave => $mensagem): ?>
                <?php if (isset($_GET[$chave])): ?>

                    <div class="alert <?= $chave === "erro" ? "alert-danger" : "alert-success" ?>">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>

                <?php endif; ?>
            <?php endforeach; ?>

            <!-- FORMULÁRIO -->
            <div class="card shadow-sm p-4 mb-4">

                <h4 class="mb-3">
                    <?= $frete_edicao ? "Editar frete" : "Novo frete" ?>
                </h4>

                <form method="POST" action="fretes.php">

                    <?php if ($frete_edicao): ?>
                        <input
                            type="hidden"
                            name="atualizar_id"
                            value="<?= $frete_edicao["id"] ?>">
                    <?php endif; ?>

                    <!-- PRODUTOS -->
                    <div class="mb-3">

                        <label class="form-label">
                            Produtos transportados
                        </label>

                        <div class="border rounded p-3 bg-white">

                            <?php if (count($lista_produtos) > 0): ?>

                                <?php foreach ($lista_produtos as $produto): ?>

                                    <?php
                                    $produto_id = $produto["id"];
                                    $selecionado = isset($produtos_do_frete[$produto_id]);
                                    $quantidade_atual = $produtos_do_frete[$produto_id] ?? 1;
                                    ?>

                                    <div class="row align-items-center mb-2">

                                        <div class="col-md-8">

                                            <div class="form-check">

                                                <input
                                                    type="checkbox"
                                                    class="form-check-input produto-checkbox"
                                                    name="produtos[]"
                                                    value="<?= $produto_id ?>"
                                                    id="produto<?= $produto_id ?>"
                                                    <?= $selecionado ? "checked" : "" ?>
                                                >

                                                <label
                                                    class="form-check-label"
                                                    for="produto<?= $produto_id ?>"
                                                >
                                                    <?= htmlspecialchars($produto["codigo"] . " - " . $produto["nome"]) ?>
                                                </label>

                                            </div>

                                        </div>

                                        <div class="col-md-4">

                                            <input
                                                type="number"
                                                name="quantidades[<?= $produto_id ?>]"
                                                class="form-control form-control-sm quantidade-produto"
                                                min="1"
                                                value="<?= $quantidade_atual ?>"
                                                placeholder="Quantidade"
                                                <?= $selecionado ? "" : "disabled" ?>
                                                id="quantidade<?= $produto_id ?>"
                                            >

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p class="text-muted mb-0">
                                    Nenhum produto cadastrado.
                                </p>

                            <?php endif; ?>

                        </div>

                        <small class="text-muted">
                            Selecione um ou mais produtos e informe a quantidade de cada um.
                        </small>

                    </div>

                    <!-- TRANSPORTADOR -->
                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Transportador</label>

                            <select name="transportador_id" class="form-select" required>

                                <option value="">Selecione um transportador</option>

                                <?php foreach ($lista_transportadores as $transportador): ?>

                                    <option
                                        value="<?= $transportador["id"] ?>"
                                        <?= ($frete_edicao && $frete_edicao["transportador_id"] == $transportador["id"]) ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($transportador["nome"]) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <!-- ENDEREÇOS -->
                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Endereço de origem</label>

                            <select name="origem_id" class="form-select" required>

                                <option value="">Selecione a origem</option>

                                <?php foreach ($lista_enderecos as $endereco): ?>

                                    <option
                                        value="<?= $endereco["id"] ?>"
                                        <?= ($frete_edicao && $frete_edicao["origem_id"] == $endereco["id"]) ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($endereco["identificacao"] . " - " . $endereco["cidade"] . "/" . $endereco["estado"]) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Endereço de destino</label>

                            <select name="destino_id" class="form-select" required>

                                <option value="">Selecione o destino</option>

                                <?php foreach ($lista_enderecos as $endereco): ?>

                                    <option
                                        value="<?= $endereco["id"] ?>"
                                        <?= ($frete_edicao && $frete_edicao["destino_id"] == $endereco["id"]) ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($endereco["identificacao"] . " - " . $endereco["cidade"] . "/" . $endereco["estado"]) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <!-- DATAS E VALOR -->
                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">Data de saída</label>

                            <input
                                type="date"
                                name="data_saida"
                                class="form-control"
                                value="<?= htmlspecialchars($frete_edicao["data_saida"] ?? "") ?>"
                                required
                            >

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">Data de entrega</label>

                            <input
                                type="date"
                                name="data_entrega"
                                class="form-control"
                                value="<?= htmlspecialchars($frete_edicao["data_entrega"] ?? "") ?>"
                                required
                            >

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">Valor do frete (R$)</label>

                            <input
                                type="number"
                                name="valor"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                value="<?= htmlspecialchars($frete_edicao["valor"] ?? "") ?>"
                                required
                            >

                        </div>

                    </div>

                    <!-- SITUAÇÃO -->
                    <div class="mb-3">

                        <label class="form-label">Situação do frete</label>

                        <select name="situacao" class="form-select" required>

                            <option value="">Selecione a situação</option>

                            <?php
                            $situacoes = ["Pendente", "Em transporte", "Entregue", "Cancelado"];
                            ?>

                            <?php foreach ($situacoes as $situacao): ?>

                                <option
                                    value="<?= $situacao ?>"
                                    <?= ($frete_edicao && $frete_edicao["situacao"] === $situacao) ? "selected" : "" ?>
                                >
                                    <?= $situacao ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <?php if ($frete_edicao): ?>

                        <button type="submit" class="btn btn-primary">
                            Atualizar frete
                        </button>

                        <a href="fretes.php" class="btn btn-secondary">
                            Cancelar edição
                        </a>

                    <?php else: ?>

                        <button type="submit" name="salvar" class="btn btn-success">
                            Salvar frete
                        </button>

                        <button type="reset" class="btn btn-secondary">
                            Limpar
                        </button>

                    <?php endif; ?>

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
                                <th>Produtos</th>
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

                                        <td><?= htmlspecialchars($frete["produtos"]) ?></td>

                                        <td><?= htmlspecialchars($frete["transportador"] ?? "") ?></td>

                                        <td><?= htmlspecialchars($frete["origem"] ?? "") ?></td>

                                        <td><?= htmlspecialchars($frete["destino"] ?? "") ?></td>

                                        <td>
                                            <?= $frete["data_saida"] ? date("d/m/Y", strtotime($frete["data_saida"])) : "" ?>
                                        </td>

                                        <td>
                                            <?= $frete["data_entrega"] ? date("d/m/Y", strtotime($frete["data_entrega"])) : "" ?>
                                        </td>

                                        <td>
                                            R$ <?= number_format($frete["valor"], 2, ",", ".") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($frete["situacao"] ?? "") ?>
                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <a
                                                    href="fretes.php?editar_id=<?= $frete["id"] ?>"
                                                    class="btn btn-primary btn-sm">
                                                    Editar
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="fretes.php"
                                                    onsubmit="return confirm('Deseja realmente excluir este frete?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="excluir_id"
                                                        value="<?= $frete["id"] ?>"
                                                    >

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

<script>

// HABILITAR OU DESABILITAR A QUANTIDADE CONFORME A SELEÇÃO DO PRODUTO
document.querySelectorAll(".produto-checkbox").forEach(function(checkbox) {

    checkbox.addEventListener("change", function() {

        const quantidade = document.getElementById(
            "quantidade" + this.value
        );

        quantidade.disabled = !this.checked;

        if (!this.checked) {
            quantidade.value = 1;
        }

    });

});

</script>

</body>
</html>
