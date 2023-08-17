<?php

class EstoqueUpdater {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function atualizarEstoque(array $estoqueData) {
        $sql = "INSERT INTO estoque (produto, cor, tamanho, deposito, data_disponibilidade, quantidade)
                VALUES (:produto, :cor, :tamanho, :deposito, :data_disponibilidade, :quantidade)
                ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade)";

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);

            foreach ($estoqueData as $item) {
                $stmt->bindValue(':produto', $item['produto']);
                $stmt->bindValue(':cor', $item['cor']);
                $stmt->bindValue(':tamanho', $item['tamanho']);
                $stmt->bindValue(':deposito', $item['deposito']);
                $stmt->bindValue(':data_disponibilidade', $item['data_disponibilidade']);
                $stmt->bindValue(':quantidade', $item['quantidade']);
                $stmt->execute();
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao atualizar o estoque: " . $e->getMessage());
        }
    }
}

// Configurações do banco de dados
$dbHost = "localhost";
$dbName = "geovendas";
$dbUser = "root";
$dbPass = "root123";

try {
    // Conectando ao banco de dados
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Exemplo de JSON de entrada
    $jsonData = '[
        {
            "produto": "10.01.0419",
            "cor": "00",
            "tamanho": "P",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 15
            },
            {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "P",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 2
            },
            {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "M",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 4
            },
            {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "G",
            "deposito": "1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 6
            },
            {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "P",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-06-01",
            "quantidade": 8
            }
    ]';

    $estoqueData = json_decode($jsonData, true);

    $estoqueUpdater = new EstoqueUpdater($pdo);
    $estoqueUpdater->atualizarEstoque($estoqueData);

    echo "Estoque atualizado com sucesso!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

?>
