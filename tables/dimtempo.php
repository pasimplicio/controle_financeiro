<?php
// C:\xampp\htdocs\controle_financeiro\tables\dimcategoria.php
declare(strict_types=1);
error_reporting(E_ALL);

// =============================================
// CARREGA CONFIGURAÇÃO DE CAMINHOS
// =============================================
require_once __DIR__ . '/../paths.php';

// =============================================
// FUNÇÃO DE CARREGAMENTO SEGURO
// =============================================
function carregarArquivo(string $caminhoAbsoluto): void {
    if (!file_exists($caminhoAbsoluto)) {
        throw new RuntimeException("Arquivo não encontrado: " . basename($caminhoAbsoluto));
    }
    require_once $caminhoAbsoluto;
}

// =============================================
// CARREGA DEPENDÊNCIAS
// =============================================
try {
    carregarArquivo(CONFIG_PATH . '/database.php');
    carregarArquivo(CLASSES_PATH . '/DataManager.php');
    
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException("Conexão com banco de dados não inicializada");
    }
} catch (Throwable $e) {
    die("<div class='alert alert-danger'>
        <h2>Erro Crítico</h2>
        <p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>
        <a href='/controle_financeiro/' class='btn btn-secondary'>Voltar</a>
    </div>");
}

// =============================================
// CONFIGURAÇÃO DA TABELA
// =============================================
$tableName = 'dimtempo';
$dataManager = new DataManager($conn);

try {
    $data = $dataManager->getData($tableName);
    $columns = $dataManager->getTableSchemas()[$tableName];
} catch (Throwable $e) {
    die("<div class='alert alert-danger container mt-4'>
        <h2>Erro ao Carregar Dados</h2>
        <p>Tabela: " . htmlspecialchars($tableName) . "</p>
        <p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <a href='/controle_financeiro/tables/' class='btn btn-secondary'>Voltar</a>
    </div>");
}

// =============================================
// CARREGAMENTO DO HEADER
// =============================================
try {
    carregarArquivo('../../includes/header.php');
} catch (Throwable $e) {
    die("<div class='alert alert-danger'>Erro ao carregar cabeçalho: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<!-- ============================================= -->
<!-- CONTEÚDO PRINCIPAL -->
<!-- ============================================= -->
<div class="container-fluid mt-3">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="card-title">
                <i class="bi bi-table"></i> <?= htmlspecialchars((string)$tableName) ?>
            </h2>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <th><?= htmlspecialchars((string)$column) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="<?= count($columns) ?>" class="text-center">
                                    Nenhum registro encontrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <td><?= isset($row[$column]) ? htmlspecialchars((string)$row[$column]) : 'NULL' ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <span class="text-muted">
                    Total de registros: <?= count($data) ?>
                </span>
                <a href="/controle_financeiro/" class="btn btn-primary">
                    <i class="bi bi-house-door"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// =============================================
// CARREGAMENTO DO FOOTER
// =============================================
try {
    carregarArquivo('../../includes/footer.php');
} catch (Throwable $e) {
    die("<div class='alert alert-danger'>Erro ao carregar rodapé: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>