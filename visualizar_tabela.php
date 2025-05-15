<?php
require_once 'config/database.php';
require_once 'classes/DataManager.php';

$tabela = $_GET['tabela'] ?? '';
$dataManager = new DataManager($GLOBALS['DB_CONNECTION']);
$dados = $dataManager->getData($tabela);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar <?= htmlspecialchars($tabela) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <a href="index.php" class="btn btn-secondary mb-3">← Voltar</a>
    <h2>Dados da Tabela: <?= htmlspecialchars($tabela) ?></h2>
    
    <?php if (count($dados) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <?php foreach (array_keys($dados[0]) as $coluna): ?>
                            <th><?= htmlspecialchars($coluna) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dados as $linha): ?>
                        <tr>
                            <?php foreach ($linha as $valor): ?>
                                <td><?= htmlspecialchars($valor) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Nenhum dado encontrado na tabela.</div>
    <?php endif; ?>
</body>
</html>
