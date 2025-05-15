<?php
class DataManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getTableSchemas(): array {
        $tables = [];
        $result = $this->conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $table = $row[0];
            $tables[$table] = $this->getTableColumns($table);
        }
        return $tables;
    }

    public function getTableColumns(string $table): array {
        $columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM `$table`");
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        return $columns;
    }

    public function getData(string $table, int $limit = null): array {
        $sql = "SELECT * FROM `$table`";
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
        }
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPrimaryKey(string $table): ?string {
        $result = $this->conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        if ($row = $result->fetch_assoc()) {
            return $row['Column_name'];
        }
        return null;
    }

    public function getForeignKeys(string $table): array {
        $sql = "
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '$table' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ";
        $result = $this->conn->query($sql);
        $fks = [];
        while ($row = $result->fetch_assoc()) {
            $fks[$row['COLUMN_NAME']] = [
                'referenced_table' => $row['REFERENCED_TABLE_NAME'],
                'referenced_column' => $row['REFERENCED_COLUMN_NAME']
            ];
        }
        return $fks;
    }

    public function getForeignKeyLabels(string $table): array {
        $labels = [];
        $fks = $this->getForeignKeys($table);
        
        foreach ($fks as $coluna => $info) {
            $referencedTable = $info['referenced_table'];
            $referencedColumn = $info['referenced_column'];
            
            if ($coluna === 'IDInstituicaoCustodia' && $referencedTable === 'diminstituicaocustodia') {
                $descColumn = 'NomeInstituicao';
            } elseif ($coluna === 'IDConta' && $referencedTable === 'dimconta') {
                $descColumn = 'NomeConta';
            } else {
                $descColumn = $this->getFirstTextColumn($referencedTable, $referencedColumn);
            }
            
            $result = $this->conn->query("SELECT `$referencedColumn`, `$descColumn` FROM `$referencedTable`");
            
            $map = [];
            while ($row = $result->fetch_assoc()) {
                $map[$row[$referencedColumn]] = $row[$descColumn];
            }
            $labels[$coluna] = $map;
        }
        
        return $labels;
    }

    private function getFirstTextColumn(string $table, string $except = ''): string {
        $result = $this->conn->query("SHOW COLUMNS FROM `$table`");
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === $except) continue;
            if (stripos($row['Type'], 'varchar') !== false || 
                stripos($row['Type'], 'text') !== false ||
                stripos($row['Type'], 'char') !== false) {
                return $row['Field'];
            }
        }
        return $except;
    }

    public function isRequiredField(string $table, string $column): bool {
        if (strpos($column, 'ID') === 0 && $column === $this->getPrimaryKey($table)) {
            return false;
        }
        
        $result = $this->conn->query("SHOW COLUMNS FROM `$table` WHERE Field = '$column'");
        if ($row = $result->fetch_assoc()) {
            return $row['Null'] === 'NO' && empty($row['Default']);
        }
        return false;
    }

    public function getFkIdByDescription(string $table, string $idColumn, string $description): ?string {
        $descColumn = $this->getFirstTextColumn($table, $idColumn);
        
        $sql = "SELECT `$idColumn` FROM `$table` WHERE `$descColumn` = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $description);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row[$idColumn];
        }
        
        throw new Exception("Registro não encontrado em $table com descrição '$description'");
    }

    public function getDataById(string $table, $id): array {
        $pk = $this->getPrimaryKey($table);
        $sql = "SELECT * FROM `$table` WHERE `$pk` = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?? [];
    }

    public function validateCNPJ(string $cnpj): bool {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Validação do primeiro dígito verificador
        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;
        
        if ($cnpj[12] != $digit1) {
            return false;
        }
        
        // Validação do segundo dígito verificador
        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;
        
        return $cnpj[13] == $digit2;
    }

    public function saveData(string $table, array $data): bool {
        if (!$this->conn || $this->conn->connect_error) {
            throw new Exception("Conexão com o banco de dados não está ativa");
        }

        // Validações específicas por tabela
        if ($table === 'diminstituicaocustodia' && isset($data['CNPJ']) && !empty($data['CNPJ'])) {
            if (!$this->validateCNPJ($data['CNPJ'])) {
                throw new Exception("CNPJ inválido");
            }
        }

        // Remove campo ID se existir (deve ser auto-incremento)
        $pk = $this->getPrimaryKey($table);
        if ($pk && isset($data[$pk])) {
            unset($data[$pk]);
        }

        // Converter campos decimais
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
        }
        
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');
        
        $sql = "INSERT INTO `$table` (`" . implode("`,`", $columns) . "`) VALUES (" . implode(",", $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . $this->conn->error);
        }
        
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function updateData(string $table, array $data, $id): bool {
        $pk = $this->getPrimaryKey($table);
        if (!$pk) {
            throw new Exception("Tabela $table não possui chave primária");
        }

        // Validações específicas por tabela
        if ($table === 'diminstituicaocustodia' && isset($data['CNPJ']) && !empty($data['CNPJ'])) {
            if (!$this->validateCNPJ($data['CNPJ'])) {
                throw new Exception("CNPJ inválido");
            }
        }

        // Garantir que o ID não seja modificado
        if (isset($data[$pk])) {
            unset($data[$pk]);
        }

        // Verificar se há dados para atualizar
        if (empty($data)) {
            return true;
        }

        // Converter campos decimais
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
        }

        $set = [];
        $types = '';
        $values = [];
        
        foreach ($data as $column => $value) {
            $set[] = "`$column` = ?";
            $types .= 's';
            $values[] = $value;
        }
        
        $values[] = $id;
        $types .= 's';
        
        $sql = "UPDATE `$table` SET " . implode(", ", $set) . " WHERE `$pk` = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . $this->conn->error);
        }
        
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function deleteData(string $table, $id): bool {
        $pk = $this->getPrimaryKey($table);
        if (!$pk) {
            throw new Exception("Tabela $table não possui chave primária definida");
        }

        $dependencies = $this->checkDependencies($table, $id);
        if (!empty($dependencies)) {
            $mensagem = "Não é possível excluir porque este registro está vinculado a:<br>";
            foreach ($dependencies as $tabela => $quantidade) {
                $nomeAmigavel = $this->getFriendlyName($tabela);
                $mensagem .= "- $nomeAmigavel ($quantidade registros)<br>";
            }
            throw new Exception($mensagem);
        }

        $sql = "DELETE FROM `$table` WHERE `$pk` = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . $this->conn->error);
        }
        
        $stmt->bind_param('s', $id);
        return $stmt->execute();
    }

    private function checkDependencies(string $table, $id): array {
        $pk = $this->getPrimaryKey($table);
        $sql = "
            SELECT TABLE_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE() 
            AND REFERENCED_TABLE_NAME = ? 
            AND REFERENCED_COLUMN_NAME = ?
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $table, $pk);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dependents = [];
        while ($row = $result->fetch_assoc()) {
            $dependentTable = $row['TABLE_NAME'];
            $countSql = "SELECT COUNT(*) as count FROM `$dependentTable` WHERE `$pk` = ?";
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->bind_param('s', $id);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $countRow = $countResult->fetch_assoc();
            
            if ($countRow['count'] > 0) {
                $dependents[$dependentTable] = $countRow['count'];
            }
        }
        
        return $dependents;
    }

public function getForeignKeyData(string $table, string $fkColumn) {
    $fks = $this->getForeignKeys($table);
    if (!isset($fks[$fkColumn])) {
        return null;
    }

    $refTable = $fks[$fkColumn]['referenced_table'];
    $refColumn = $fks[$fkColumn]['referenced_column'];
    $descColumn = $this->getFirstTextColumn($refTable, $refColumn);

    $sql = "SELECT `$refColumn` as id, `$descColumn` as descricao FROM `$refTable` ORDER BY `$descColumn`";
    $result = $this->conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['id']] = $row['descricao'];
    }
    
    return $data;
}

 public function getFkDescription(string $table, string $fkColumn, $id) {
    $fks = $this->getForeignKeys($table);
    if (!isset($fks[$fkColumn])) {
        return $id;
    }

    $refTable = $fks[$fkColumn]['referenced_table'];
    $refColumn = $fks[$fkColumn]['referenced_column'];
    $descColumn = $this->getFirstTextColumn($refTable, $refColumn);

    $sql = "SELECT `$descColumn` FROM `$refTable` WHERE `$refColumn` = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row[$descColumn];
    }
    
    return $id;
}

    public function getFriendlyName(string $tabela): string {
        $mapa = [
            'dimcartaocredito' => '💳 Cartão de Crédito',
            'dimcategoria' => '📂 Categoria',
            'dimconta' => '🏦 Conta',
            'dimdivida' => '💸 Dívida',
            'diminstituicaocustodia' => '🏦 Instituição Custodiante',
            'diminvestimento' => '📈 Investimento',
            'dimpessoacredordevedor' => '👤 Credor/Devedor',
            'dimtempo' => '🗓️ Tempo',
            'dimtipotransacao' => '🔄 Tipo de Transação',
            'fatofaturacartao' => '🧾 Faturamento Cartão',
            'fatomovimentacaoinvestimento' => '💼 Movimentação Investimento',
            'fatoparcelas' => '📑 Parcelas em Geral',
            'fatotransacoes' => '💰 Transações'
        ];
        
        return $mapa[$tabela] ?? ucwords(str_replace(['_', '-'], ' ', $tabela));
    }
}