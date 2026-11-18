
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

            <p>Início</p>
            <p>Produtos</p>
            <p>Endereços</p>
            <p>Transportadores</p>
            <p>Fretes</p>
            <p>Relatórios</p>
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
            <h2>12</h2>
        </div>
     </div>
        <div class="col-md-4 mb-3">
        <div class="card shadow-sm p-3">
            <h5>Transportadores cadastrados</h5>
            <h2>4</h2>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm p-3">
            <h5>Fretes realizados</h5>
            <h2>8</h2>
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
            <tr>
                <td>F001</td>
                <td>Transportadora Alfa</td>
                <td>Curitiba</td>
                <td>17/11/2026</td>
            </tr>
            <tr>
                <td>F002</td>
                <td>Transportadora Beta</td>
                <td>São Paulo</td>
                <td>16/11/2026</td>
            </tr>
            <tr>
                <td>F003</td>
                <td>Transportadora Gama</td>
                <td>Florianópolis</td>
                <td>15/11/2026</td>
            </tr>
        </tbody>
    </table>
</div>

</div>
    </div>
</div>


</body>
</html>
