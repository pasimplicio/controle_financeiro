<?php
require_once 'classes/DataManager.php';

// Conexão com banco de dados
try {
    $conn = new mysqli("localhost", "root", "", "controle_financeiro");
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
    $manager = new DataManager($conn);
    $tabelas = $manager->getTableSchemas();
    
    if (empty($tabelas)) {
        throw new Exception("Nenhuma tabela encontrada no banco de dados.");
    }
    
    $tabela = $_GET['tabela'] ?? array_key_first($tabelas);
    $colunas = $tabelas[$tabela] ?? [];
    $pk = $manager->getPrimaryKey($tabela);
    $fks = $manager->getForeignKeyLabels($tabela);
    $dados = $manager->getData($tabela);

} catch (Exception $e) {
    die("<div class='alert alert-danger m-3'>Erro no sistema: " . $e->getMessage() . "</div>");
}

// Função para formatar datas
function formatarData($valor) {
    if (!$valor || $valor === '0000-00-00') return '';
    try {
        return date('d/m/Y', strtotime($valor));
    } catch (Exception $e) {
        return $valor;
    }
}

// Processar exportação de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tabelas_exportar'])) {
    $selecionadas = $_POST['tabelas_exportar'];
    
    if (count($selecionadas) === 1) {
        $tabela = $selecionadas[0];
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tabela . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        $dadosTbl = $manager->getData($tabela);
        if (!empty($dadosTbl)) {
            fputcsv($out, array_keys($dadosTbl[0]));
            foreach ($dadosTbl as $linha) fputcsv($out, $linha);
        }
        fclose($out);
        exit;
    } else {
        $zip = new ZipArchive();
        $tmpZip = tempnam(sys_get_temp_dir(), 'csvzip');
        $zip->open($tmpZip, ZipArchive::OVERWRITE);
        
        foreach ($selecionadas as $tabela) {
            $dadosTbl = $manager->getData($tabela);
            if (!empty($dadosTbl)) {
                $csv = fopen('php://temp', 'w+');
                fputcsv($csv, array_keys($dadosTbl[0]));
                foreach ($dadosTbl as $linha) fputcsv($csv, $linha);
                rewind($csv);
                $csvContent = stream_get_contents($csv);
                fclose($csv);
                $zip->addFromString($tabela . '.csv', $csvContent);
            }
        }
        
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="exportacao_tabelas.zip"');
        readfile($tmpZip);
        unlink($tmpZip);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: #0d6efd; min-height: 100vh; padding: 20px 0; }
        .main-content { padding: 20px; }
        .nav-link.active { font-weight: bold; background-color: rgba(255,255,255,0.1); }
        .form-section { background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .table-container { overflow-x: auto; margin-top: 20px; }
        .required-field::after { content: " *"; color: red; }
        .btn-action { min-width: 100px; margin-right: 5px; margin-bottom: 5px; }
        .menu-title { color: #ffc107; font-weight: bold; }
        .alert-floating { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .decimal-input { text-align: right; font-family: monospace; }
        .decimal-input:disabled { background-color: #f8f9fa; opacity: 1; }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        tr:hover { background-color: #f1f1f1; cursor: pointer; }
        .btn-home {
            margin-bottom: 20px;
            width: 100%;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Menu Lateral -->
        <div class="col-md-2 sidebar text-white p-3">
            <div class="text-center mb-4">
                <a href="index.php" class="btn btn-warning btn-sm btn-home">
                    <i class="bi bi-house-door"></i> Home
                </a>
            </div>

            <h5 class="menu-title">Cadastro</h5>
            <nav class="nav flex-column">
                <?php foreach (array_keys($tabelas) as $tab): ?>
                    <a href="?tabela=<?= htmlspecialchars($tab) ?>" 
                       class="nav-link text-white <?= $tab === $tabela ? 'active' : '' ?>">
                        <?= htmlspecialchars($manager->getFriendlyName($tab)) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <h5 class="menu-title mt-4">Exportar</h5>
            <form method="post" id="exportForm">
                <?php foreach (array_keys($tabelas) as $tab): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="tabelas_exportar[]" 
                               value="<?= htmlspecialchars($tab) ?>" 
                               id="check_<?= htmlspecialchars($tab) ?>">
                        <label class="form-check-label text-white" for="check_<?= htmlspecialchars($tab) ?>">
                            <?= htmlspecialchars($manager->getFriendlyName($tab)) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-warning btn-sm mt-2">
                    <i class="bi bi-download"></i> Exportar
                </button>
            </form>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-md-10 p-4 main-content">
            <h4 class="mb-4">
                <i class="bi bi-table"></i> <?= htmlspecialchars($manager->getFriendlyName($tabela)) ?>
            </h4>

            <div class="form-section">
                <h5 class="mb-3" id="formTitle">Novo Registro</h5>
                <form method="post" id="formDados" class="row g-3">
                   <?php foreach ($colunas as $col): ?>
    <?php if ($col === $pk) continue; ?>
    <div class="col-md-4">
        <label class="form-label <?= $manager->isRequiredField($tabela, $col) ? 'required-field' : '' ?>" 
               for="input_<?= htmlspecialchars($col) ?>">
            <?= htmlspecialchars($col) ?>
        </label>
        
        <?php 
        $isDecimalField = preg_match('/valor|preço|taxa|juros|limite|total|saldo/i', $col);
        $fkData = $manager->getForeignKeyData($tabela, $col);
        
        if ($fkData): ?>
            <select name="<?= htmlspecialchars($col) ?>" 
                    id="input_<?= htmlspecialchars($col) ?>" 
                    class="form-select" 
                    <?= $manager->isRequiredField($tabela, $col) ? 'required' : '' ?>
                    data-fk="true">
                <option value="">Selecione...</option>
                <?php foreach ($fkData as $id => $desc): ?>
                    <option value="<?= htmlspecialchars($id) ?>">
                        <?= htmlspecialchars($desc) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($isDecimalField): ?>
                                <input type="text" 
                                       name="<?= htmlspecialchars($col) ?>" 
                                       id="input_<?= htmlspecialchars($col) ?>" 
                                       class="form-control decimal-input" 
                                       <?= $manager->isRequiredField($tabela, $col) ? 'required' : '' ?>
                                       onfocus="prepararParaEdicao(this)"
                                       onblur="formatarDecimal(this)">
                            <?php elseif (str_contains(strtolower($col), 'data')): ?>
                                <input type="date" 
                                       name="<?= htmlspecialchars($col) ?>" 
                                       id="input_<?= htmlspecialchars($col) ?>" 
                                       class="form-control" 
                                       <?= $manager->isRequiredField($tabela, $col) ? 'required' : '' ?>>
                            <?php else: ?>
                                <input type="text" 
                                       name="<?= htmlspecialchars($col) ?>" 
                                       id="input_<?= htmlspecialchars($col) ?>" 
                                       class="form-control" 
                                       <?= $manager->isRequiredField($tabela, $col) ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="col-12 mt-3">
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-success btn-action" onclick="salvarRegistro(this)" id="btnSalvar">
                                <i class="bi bi-save"></i> Salvar
                            </button>
                            <button type="button" class="btn btn-warning btn-action" onclick="prepararAlteracao()" id="btnAlterar" disabled>
                                <i class="bi bi-pencil"></i> Alterar
                            </button>
                            <button type="button" class="btn btn-danger btn-action" onclick="confirmarExclusao()" id="btnExcluir" disabled>
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                            <button type="button" class="btn btn-primary btn-action" onclick="prepararNovo()" id="btnNovo">
                                <i class="bi bi-plus-circle"></i> Novo
                            </button>
                            <button type="button" class="btn btn-secondary btn-action" onclick="limparCampos()">
                                <i class="bi bi-eraser"></i> Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela de Registros -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($colunas as $col): ?>
                                <th><?= htmlspecialchars($col) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
 <?php foreach ($dados as $linha): ?>
    <tr onclick="selecionar(this)" 
        data-id="<?= htmlspecialchars($linha[$pk] ?? '') ?>">
        <?php foreach ($colunas as $col): ?>
            <?php
            $val = $linha[$col] ?? '';
            $cellVal = $val;
            
            if (array_key_exists($col, $fks)) {
                $val = $manager->getFkDescription($tabela, $col, $val);
            }
            elseif (str_contains(strtolower($col), 'data')) {
                $val = formatarData($val);
            }
            elseif (preg_match('/valor|preço|taxa|juros|limite|total|saldo/i', $col)) {
                $val = number_format((float)$val, 2, ',', '.');
            }
            ?>
            <td data-col="<?= htmlspecialchars($col) ?>" 
                data-id="<?= htmlspecialchars($cellVal) ?>">
                <?= htmlspecialchars((string)$val) ?>
            </td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este registro?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="excluirRegistro()">
                    <i class="bi bi-trash"></i> Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let modoEdicao = false;
let registroSelecionado = null;

// Função para preparar novo registro
function prepararNovo() {
    limparCampos();
    toggleFormFields(true);
    document.getElementById('formTitle').textContent = 'Novo Registro';
    modoEdicao = false;
    registroSelecionado = null;
    document.getElementById('btnSalvar').disabled = false;
    document.getElementById('btnAlterar').disabled = true;
    document.getElementById('btnExcluir').disabled = true;
    document.getElementById('btnNovo').disabled = true;
    showAlert('info', 'Pronto para cadastrar novo registro.');
}

// Função para preparar alteração
function prepararAlteracao() {
    if (!registroSelecionado) {
        showAlert('warning', 'Nenhum registro selecionado para alteração');
        return;
    }
    
    modoEdicao = true;
    toggleFormFields(true);
    document.getElementById('btnSalvar').disabled = false;
    document.getElementById('btnAlterar').disabled = true;
    document.getElementById('btnExcluir').disabled = true;
    document.getElementById('btnNovo').disabled = true;
    document.getElementById('formTitle').textContent = 'Editando Registro';
    showAlert('info', 'Modo edição ativado. Faça suas alterações e clique em Salvar.');
}

// Função para confirmar exclusão
function confirmarExclusao() {
    if (!registroSelecionado) return;
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

// Função para excluir registro
function excluirRegistro() {
    const form = document.getElementById('formDados');
    const pk = '<?= $pk ?>';
    const pkValor = form.querySelector(`input[name="${pk}"]`)?.value;
    const tabela = '<?= $tabela ?>';

    if (!pkValor) {
        showAlert('danger', 'Nenhum registro selecionado para exclusão');
        return;
    }

    const btnExcluir = document.getElementById('btnExcluir');
    btnExcluir.innerHTML = '<i class="bi bi-trash"></i> Excluindo <span class="loading"></span>';
    btnExcluir.disabled = true;

    fetch(`excluir_registro.php?tabela=${encodeURIComponent(tabela)}&id=${encodeURIComponent(pkValor)}`, {
        method: 'GET'
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro na rede');
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showAlert('danger', error.message);
    })
    .finally(() => {
        btnExcluir.innerHTML = '<i class="bi bi-trash"></i> Excluir';
        btnExcluir.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    });
}

// Função para limpar campos
function limparCampos() {
    const form = document.getElementById('formDados');
    form.reset();
    const hiddenInput = form.querySelector('input[type="hidden"][name="<?= $pk ?>"]');
    if (hiddenInput) {
        hiddenInput.remove();
    }
    registroSelecionado = null;
    document.getElementById('btnAlterar').disabled = true;
    document.getElementById('btnExcluir').disabled = true;
    document.getElementById('btnSalvar').disabled = false;
    document.getElementById('btnNovo').disabled = false;
    document.getElementById('formTitle').textContent = 'Novo Registro';
    toggleFormFields(false);
}

// Função para habilitar/desabilitar campos
function toggleFormFields(enable) {
    const form = document.getElementById('formDados');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type !== 'hidden') {
            input.disabled = !enable;
            
            if (input.classList.contains('decimal-input')) {
                if (enable) {
                    input.addEventListener('focus', decimalFocusHandler);
                    input.addEventListener('blur', decimalBlurHandler);
                }
                if (!enable && input.value) {
                    formatarDecimal(input);
                }
            }
        }
    });
}

// Funções para tratamento de decimais
function formatarDecimal(input) {
    let valor = input.value.replace(/[^\d,.-]/g, '');
    let valorNumerico = parseFloat(valor.replace(',', '.')) || 0;
    input.value = valorNumerico.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function prepararParaEdicao(input) {
    let valor = input.value.replace(/[^\d,-]/g, '');
    input.value = valor.replace(',', '.');
}

function prepararDecimalParaEnvio(valor) {
    if (typeof valor === 'string') {
        return valor.replace(/\./g, '').replace(',', '.');
    }
    return valor;
}

// Handlers para eventos de campos decimais
function decimalFocusHandler(e) {
    prepararParaEdicao(e.target);
}

function decimalBlurHandler(e) {
    formatarDecimal(e.target);
}

// Função para selecionar linha da tabela
function selecionar(row) {
    document.querySelectorAll('tbody tr').forEach(tr => {
        tr.classList.remove('table-primary');
    });
    
    row.classList.add('table-primary');
    
    const id = row.getAttribute('data-id');
    const cells = row.querySelectorAll('td');
    const form = document.getElementById('formDados');

    if (id && '<?= $pk ?>') {
        let hiddenInput = form.querySelector('input[name="<?= $pk ?>"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = '<?= $pk ?>';
            form.appendChild(hiddenInput);
        }
        hiddenInput.value = id;
    }

    document.querySelectorAll('#formDados input, #formDados select').forEach((input) => {
        const colName = input.name;
        const cell = [...cells].find(c => c.getAttribute('data-col') === colName);
        if (!cell) return;

        let valor = cell.getAttribute('data-id') || cell.innerText.trim();

     if (input.tagName === 'SELECT') {
        const options = input.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === valor) {
                input.selectedIndex = i;
                break;
                }
            }
        } else if (input.type === 'date' && valor.includes('/')) {
            const [d, m, y] = valor.split('/');
            input.value = `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
        } else if (input.classList.contains('decimal-input')) {
            if (valor.includes('.')) {
                valor = valor.replace('.', ',');
            }
            input.value = valor;
            formatarDecimal(input);
        } else {
            input.value = valor;
        }
    });

    toggleFormFields(false);
    registroSelecionado = true;
    document.getElementById('btnAlterar').disabled = false;
    document.getElementById('btnExcluir').disabled = false;
    document.getElementById('btnSalvar').disabled = true;
    document.getElementById('btnNovo').disabled = false;
    document.getElementById('formTitle').textContent = 'Visualizando Registro';
    document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
}

// Modifique a função salvarRegistro para garantir o ID correto
function salvarRegistro(btn) {
    const form = document.getElementById('formDados');
    const formData = new FormData(form);
    const tabela = '<?= $tabela ?>';
    const pk = '<?= $pk ?>';
    const pkValor = form.querySelector(`input[name="${pk}"]`)?.value;
    const modoEdicao = pkValor !== undefined && pkValor !== '';

    // Converter campos decimais antes de enviar
    document.querySelectorAll('.decimal-input').forEach(input => {
        if (input.name) {
            const valor = input.value.replace(/\./g, '').replace(',', '.');
            formData.set(input.name, valor);
        }
    });

    btn.innerHTML = '<i class="bi bi-save"></i> Salvando...';
    btn.disabled = true;

    const url = modoEdicao 
        ? `alterar_registro.php?tabela=${encodeURIComponent(tabela)}&id=${encodeURIComponent(pkValor)}`
        : `salvar_registro.php?tabela=${encodeURIComponent(tabela)}`;

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro na rede');
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.href = `?tabela=${encodeURIComponent(tabela)}` + 
                    (modoEdicao ? '' : `&highlight=${encodeURIComponent(data.id)}`);
            }, 1500);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showAlert('danger', error.message);
    })
    .finally(() => {
        btn.innerHTML = '<i class="bi bi-save"></i> Salvar';
        btn.disabled = false;
    });
}

function formatarCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 2) {
        value = value.substring(0, 2) + '.' + value.substring(2);
    }
    if (value.length > 6) {
        value = value.substring(0, 6) + '.' + value.substring(6);
    }
    if (value.length > 10) {
        value = value.substring(0, 10) + '/' + value.substring(10);
    }
    if (value.length > 15) {
        value = value.substring(0, 15) + '-' + value.substring(15, 17);
    }
    
    input.value = value;
}

// Função para mostrar alertas
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const existingAlert = document.querySelector('.alert-floating');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
}

// Inicialização - desabilita campos ao carregar
document.addEventListener('DOMContentLoaded', function() {
    toggleFormFields(false);
    
    // Habilitar campos ao clicar no formulário (apenas se não houver registro selecionado)
    document.querySelector('.form-section').addEventListener('click', function() {
        if (!registroSelecionado) {
            toggleFormFields(true);
        }
    });
});
</script>
</body>
</html>