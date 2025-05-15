<?php
require_once 'classes/DataManager.php';

ob_start();
header('Content-Type: application/json');

try {
    $conn = new mysqli("localhost", "root", "", "controle_financeiro");
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
    $manager = new DataManager($conn);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não permitido. Use POST.");
    }

    $tabela = $_GET['tabela'] ?? '';
    $id = $_GET['id'] ?? '';
    
    if (empty($tabela) || empty($id)) {
        throw new Exception("Parâmetros inválidos");
    }

    $currentData = $manager->getDataById($tabela, $id);
    if (empty($currentData)) {
        throw new Exception("Registro não encontrado");
    }

    $dados = $_POST;
    $colunas = $manager->getTableColumns($tabela);
    $dados = array_intersect_key($dados, array_flip($colunas));

    // Garantir que o ID não seja modificado
    $pk = $manager->getPrimaryKey($tabela);
    if ($pk && isset($dados[$pk])) {
        unset($dados[$pk]);
    }

    $hasChanges = false;
    foreach ($dados as $key => $value) {
        if (isset($currentData[$key]) && $currentData[$key] != $value) {
            $hasChanges = true;
            break;
        }
    }
    
    if (!$hasChanges) {
        $response = [
            'status' => 'success',
            'message' => 'Nenhuma alteração foi detectada',
            'changes' => false
        ];
    } else {
        if ($manager->updateData($tabela, $dados, $id)) {
            $response = [
                'status' => 'success',
                'message' => 'Registro atualizado com sucesso!',
                'changes' => true,
                'id' => $id
            ];
        } else {
            throw new Exception("Erro ao atualizar registro");
        }
    }

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'changes' => false
    ];
    http_response_code(400);
}

ob_end_clean();
echo json_encode($response);
exit;