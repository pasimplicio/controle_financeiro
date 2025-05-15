<?php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/DataManager.php';

try {
    $conn = $GLOBALS['DB_CONNECTION'];
    $dataManager = new DataManager($conn);
    $schemas = $dataManager->getTableSchemas();
    $status = "Conexão e leitura do banco OK.";
} catch (Exception $e) {
    $status = "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste de Conexão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Teste de Conexão com o Banco</h1>
    <?php if (str_starts_with($status, "Erro")): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($status) ?></div>
    <?php else: ?>
        <div class="alert alert-success"><?= htmlspecialchars($status) ?></div>
        <p>Tabelas encontradas:</p>
        <ul class="list-group">
            <?php foreach ($schemas as $tabela => $colunas): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $tabela ?>
                    <span class="badge bg-secondary"><?= count($colunas) ?> colunas</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
