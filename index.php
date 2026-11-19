
<?php
require_once "conexao.php";

// Buscar totais
$total_produtos = $conexao->query("SELECT COUNT(*) AS total FROM produtos")->fetch_assoc()['total'];
$total_transportadores = $conexao->query("SELECT COUNT(*) AS total FROM transportadores")->fetch_assoc()['total'];
$total_fretes = $conexao->query("SELECT COUNT(*) AS total FROM fretes")->fetch_assoc()['total'];

// Buscar últimos fretes
$sql = "SELECT f.id, t.nome AS transportador, d.identificacao AS destino, f.data_saida
        FROM fretes f
        INNER JOIN transportadores t ON f.transportador_id = t.id
        INNER JOIN enderecos d ON f.destino_id = d.id
        ORDER BY f.id DESC
        LIMIT 5";

$ultimos_fretes = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador Logístico</title>
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

            <h1 style="color: #263238;">Gerenciador Logístico</h1>
            <p class="text-muted">Bem-vindo ao sistema de gerenciamento logístico.</p>

            <hr>

            <h4>Painel de controle</h4>
            <p>Acompanhe as informações e operações do sistema.</p>

            <div class="row mt-4">

                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm p-3">
                        <h5>Produtos cadastrados</h5>
                        <h2><?php echo $total_produtos; ?></h2>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm p-3">
                        <h5>Transportadores cadastrados</h5>
                        <h2><?php echo $total_transportadores; ?></h2>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm p-3">
                        <h5>Fretes realizados</h5>
                        <h2><?php echo $total_fretes; ?></h2>
                    </div>
                </div>

            </div>

            <hr class="mt-4">

            <h4>Últimos fretes</h4>

            <div class="table-responsive">
                <table class="table table-striped table-hover mt-3">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Transportador</th>
                            <th>Destino</th>
                            <th>Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($frete = $ultimos_fretes->fetch_assoc()) { ?>
                            <tr>
                                <td>F<?php echo str_pad($frete['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($frete['transportador']); ?></td>
                                <td><?php echo htmlspecialchars($frete['destino']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($frete['data_saida'])); ?></td>
                            </tr>
                        <?php } ?>

                        <?php if ($ultimos_fretes->num_rows == 0) { ?>
                            <tr>
                                <td colspan="4">Nenhum frete cadastrado.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

</body>
</html>
