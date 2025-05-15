
<!DOCTYPE html>
<?php if (!headers_sent()) header('Content-Type: text/html; charset=utf-8'); ?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <img src="controle_financeiro.png" alt="Logo" height="40" class="me-2">
                    <a class="navbar-brand" href="index.php">Controle Financeiro</a>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Tabelas</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="row">
            <div class="col-md-3 sidebar">
                <div class="list-group">
                    <?php
                    if (isset($dataManager)) {
                        $schemas = $dataManager->getTableSchemas();
                        foreach ($schemas as $tableName => $columns) {
                           echo '<a href="/controle_financeiro/tables/'.strtolower($tableName).'.php" class="list-group-item list-group-item-action"><i class="bi bi-table"></i> '.$tableName.'</a>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">Sistema não inicializado corretamente</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-9 main-content">