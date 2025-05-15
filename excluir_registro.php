<?php
require_once 'classes/DataManager.php';

header('Content-Type: application/json');

try {
    $conn = new mysqli("localhost", "root", "", "controle_financeiro");
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
    $manager = new DataManager($conn);

    $tabela = $conn->real_escape_string($_GET['tabela'] ?? '');
    $id = $conn->real_escape_string($_GET['id'] ?? '');
    
    if (empty($tabela) || empty($id)) {
        throw new Exception("Parâmetros inválidos");
    }

    // Verificar se o registro existe
    $currentData = $manager->getDataById($tabela, $id);
    if (empty($currentData)) {
        throw new Exception("Registro não encontrado");
    }

    if ($manager->deleteData($tabela, $id)) {
        $response = [
            'status' => 'success',
            'message' => 'Registro excluído com sucesso!'
        ];
    } else {
        throw new Exception("Erro ao excluir registro");
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    http_response_code(400);
}

echo json_encode($response);
exit;