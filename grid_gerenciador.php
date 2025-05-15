<?php
require_once 'config/database.php';
require_once 'classes/DataManager.php';

$conn = $GLOBALS['DB_CONNECTION'];
$dataManager = new DataManager($conn);
$tabela = $_GET['tabela'] ?? '';
$schemas = $dataManager->getTableSchemas();
$colunas = $schemas[$tabela] ?? [];
$dados = $dataManager->getData($tabela, 100);

// Identificar FK simulando por nome de coluna
function is_foreign_key($coluna, $schemas) {
    foreach ($schemas as $tabela_ref => $cols) {
        if (in_array($coluna, $cols)) {
            return $tabela_ref;
        }
    }
    return false;
}

function get_fk_options($conn, $tabela, $coluna_id, $coluna_nome = null) {
    $coluna_nome = $coluna_nome ?? $coluna_id;
    $options = [];
    $sql = "SELECT `$coluna_id`, `$coluna_nome` FROM `$tabela` LIMIT 100";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $options[$row[$coluna_id]] = $row[$coluna_nome];
    }
    return $options;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar <?= htmlspecialchars($tabela) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background-color: #f2f4f8;
    }
    .container {
        background: #fff;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    table th {
        background-color: #0d6efd;
        color: white;
        text-align: center;
        vertical-align: middle;
    }
    table td {
        vertical-align: middle;
    }
    .form-select, .form-control {
        font-size: 0.9rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
    }
  </style>
</head>
<body class="p-4">
<div class="container">
  <h2 class="mb-4 text-primary">Gerenciar: <?= htmlspecialchars($tabela) ?></h2>
  <form method="post" action="salvar_registro.php?tabela=<?= urlencode($tabela) ?>">
    <table class="table table-bordered table-hover align-middle text-center">
      <thead>
        <tr>
          <?php foreach ($colunas as $col): ?>
            <th><?= $col ?></th>
          <?php endforeach; ?>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $linha): ?>
          <tr>
            <?php foreach ($colunas as $col): ?>
              <td><?= htmlspecialchars($linha[$col]) ?></td>
            <?php endforeach; ?>
            <td>
              <a href="editar_registro.php?tabela=<?= $tabela ?>&id=<?= $linha[$colunas[0]] ?>" class="btn btn-warning btn-sm">Editar</a>
              <a href="excluir_registro.php?tabela=<?= $tabela ?>&id=<?= $linha[$colunas[0]] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <?php foreach ($colunas as $col): ?>
            <td>
              <?php
              $fk_table = is_foreign_key($col, $schemas);
              if ($fk_table) {
                  $options = get_fk_options($conn, $fk_table, $col, $schemas[$fk_table][1] ?? $col);
                  echo "<select name='{$col}' class='form-select'>";
                  foreach ($options as $id => $label) {
                      echo "<option value='$id'>$label</option>";
                  }
                  echo "</select>";
              } else {
                  echo "<input type='text' name='{$col}' class='form-control'>";
              }
              ?>
            </td>
          <?php endforeach; ?>
          <td><button type="submit" class="btn btn-success btn-sm">Incluir</button></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
</body>
</html>
