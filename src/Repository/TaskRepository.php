<?php

namespace Repository;

use Database\Database;
use Model\Task;
use PDO;

class TaskRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(Task $task): Task
    {
        $sql = "INSERT INTO relatorios (usuario_id, titulo, cliente, descricao, data_realizacao, valor) 
                VALUES (:usuario_id, :titulo, :cliente, :descricao, :data_realizacao, :valor)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $task->usuario_id);
        $stmt->bindValue(':titulo', $task->titulo);
        $stmt->bindValue(':cliente', $task->cliente);
        $stmt->bindValue(':descricao', $task->descricao);
        $stmt->bindValue(':data_realizacao', $task->data_realizacao);
        $stmt->bindValue(':valor', $task->valor);
        
        $stmt->execute();

        // Pega o ID que o banco gerou e devolve o objeto preenchido
        $task->id = (int) $this->pdo->lastInsertId();
        
        return $task;
    }

    public function findAll(array $filtros = []): array
    {
        $sql = "SELECT * FROM relatorios WHERE 1=1";
        
        // Filtro por Cliente (?cliente=nome)
        if (!empty($filtros['cliente'])) {
            $sql .= " AND cliente LIKE :cliente";
        }

        // Filtro por Data (?data=2023-01-01)
        if (!empty($filtros['data'])) {
            $sql .= " AND data_realizacao = :data";
        }

        $stmt = $this->pdo->prepare($sql);

        if (!empty($filtros['cliente'])) {
            $stmt->bindValue(':cliente', '%' . $filtros['cliente'] . '%');
        }
        if (!empty($filtros['data'])) {
            $stmt->bindValue(':data', $filtros['data']);
        }

        $stmt->execute();
        
        // Retorna um array de arrays associativos
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM relatorios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $dados ?: null; // Retorna null se não encontrar
    }

    public function update(int $id, array $dados): bool
    {
        // Monta o SQL dinamicamente (só atualiza os campos enviados)
        $campos = [];
        foreach ($dados as $key => $value) {
            // Ignora campos que não existem no banco ou não devem ser atualizados via API simples
            if (in_array($key, ['titulo', 'cliente', 'descricao', 'data_realizacao', 'valor'])) {
                $campos[] = "$key = :$key";
            }
        }

        if (empty($campos)) {
            return false; // Nada para atualizar
        }

        // Adiciona atualização automática da data
        $campos[] = "data_atualizacao = CURRENT_TIMESTAMP";

        $sql = "UPDATE relatorios SET " . implode(', ', $campos) . " WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        foreach ($dados as $key => $value) {
            if (in_array($key, ['titulo', 'cliente', 'descricao', 'data_realizacao', 'valor'])) {
                $stmt->bindValue(":$key", $value);
            }
        }

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM relatorios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }
}