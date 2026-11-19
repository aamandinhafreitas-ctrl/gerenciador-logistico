
<?php
require_once "conexao.php";

// CONSULTAR FRETES
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

// CONFIGURAR DOWNLOAD CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=relatorio_fretes.csv");

// CRIAR ARQUIVO DE SAÍDA
$arquivo = fopen("php://output", "w");

// BOM para facilitar leitura de caracteres no Excel
fwrite($arquivo, "\xEF\xBB\xBF");

// CABEÇALHO
fputcsv($arquivo, [
    "ID",
    "Produto",
    "Transportador",
    "Origem",
    "Destino",
    "Data de saída",
    "Data de entrega",
    "Valor",
    "Situação"
], ";");

// DADOS
while ($frete = $resultado->fetch_assoc()) {

    fputcsv($arquivo, [
        $frete["id"],
        $frete["produto"],
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
exit;
