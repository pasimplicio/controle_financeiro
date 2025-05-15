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
    if (empty($tabela)) {
        throw new Exception("Tabela não especificada na URL");
    }

    $dados = $_POST;
    $colunas = $manager->getTableColumns($tabela);
    
    // Filtrar apenas colunas existentes
    $dados = array_intersect_key($dados, array_flip($colunas));

    // Validar campos obrigatórios
    foreach ($colunas as $col) {
        if ($manager->isRequiredField($tabela, $col) && empty($dados[$col])) {
            throw new Exception("O campo {$col} é obrigatório");
        }
    }

    // Salvar dados
    $result = $manager->saveData($tabela, $dados);

    if (!$result) {
        throw new Exception("Erro ao salvar registro");
    }

    $response = [
        'status' => 'success',
        'message' => "Registro criado com sucesso!",
        'id' => $conn->insert_id
    ];

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    http_response_code(400);
}

ob_end_clean();
echo json_encode($response);
exit;