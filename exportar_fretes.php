
<?php
require_once "conexao.php";

// RECEBER DATAS DO FILTRO
$data_inicio = $_GET["data_inicio"] ?? "";
$data_fim = $_GET["data_fim"] ?? "";

// MONTAR FILTRO
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

// CONSULTAR FRETES E PRODUTOS
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

$stmt = $conexao->prepare($sql);

if (!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$resultado = $stmt->get_result();

// CONFIGURAR DOWNLOAD CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=relatorio_fretes.csv");

// CRIAR ARQUIVO
$arquivo = fopen("php://output", "w");

// BOM PARA EXCEL
fwrite($arquivo, "\xEF\xBB\xBF");

// CABEÇALHO
fputcsv($arquivo, [
    "ID",
    "Produtos e quantidades",
    "Transportador",
    "Origem",
    "Destino",
    "Data de saída",
    "Data de entrega",
    "Valor",
    "Situação"
], ";");

// EXPORTAR DADOS
while ($frete = $resultado->fetch_assoc()) {

    fputcsv($arquivo, [
        $frete["id"],
        $frete["produtos"],
        $frete["transportador"],
        $frete["origem"],
        $frete["destino"],
        $frete["data_saida"],
        $frete["data_entrega"],
        number_format($frete["valor"], 2, ",", "."),
        $frete["situacao"]
    ], ";");
}

fclose($arquivo);
$stmt->close();
exit;
