<?php

namespace Service;

use Repository\TaskRepository;
use Model\Task;             
use Error\APIException;

class TaskService
{
    private TaskRepository $repository;

    public function __construct()
    {
        $this->repository = new TaskRepository();
    }

    public function listar($filtros)
    {
        return $this->repository->findAll($filtros);
    }

    public function buscarPorId($id)
    {
        $task = $this->repository->findById($id);
        if (!$task) {
            throw new APIException("Relatório/Task não encontrado", 404);
        }
        return $task;
    }

    public function criar(array $dados)
    {
        //Campos obrigatórios
        if (empty($dados['titulo']) || empty($dados['cliente']) || empty($dados['data_realizacao'])) {
            throw new APIException("Campos obrigatórios: titulo, cliente e data_realizacao", 400);
        }

        // Validação de Valor
        $valor = $dados['valor'] ?? 0;
        if ($valor < 0) {
            throw new APIException("O valor do serviço não pode ser negativo", 400);
        }

        // Cria o objeto Task (Model)
        // posteriormente add token jwt
        $usuarioId = $dados['usuario_id'] ?? 1; 

        $task = new Task(
            $usuarioId,
            $dados['titulo'],
            $dados['cliente'],
            $dados['data_realizacao'],
            (float) $valor,
            $dados['descricao'] ?? null
        );

        return $this->repository->create($task);
    }

    public function atualizar($id, array $dados)
    {
        // Verifica se existe antes de atualizar
        $existente = $this->buscarPorId($id);

        // Validação de Valor na atualização
        if (isset($dados['valor']) && $dados['valor'] < 0) {
            throw new APIException("O valor do serviço não pode ser negativo", 400);
        }

        // Chama o repository para atualizar
        $sucesso = $this->repository->update($id, $dados);

        if (!$sucesso) {
            throw new APIException("Erro ao atualizar task", 500);
        }

        return $this->buscarPorId($id); // Retorna o item atualizado
    }

    public function deletar($id)
    {
        // Verifica se existe
        $this->buscarPorId($id);

        // Hard Delete
        $this->repository->delete($id);
        
        return true;
    }
}