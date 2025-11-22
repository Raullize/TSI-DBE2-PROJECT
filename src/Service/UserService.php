<?php

namespace Service;

use Repository\UserRepository;
use Model\User;
use Error\APIException;

class UserService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function cadastrar(array $dados)
    {
        // Validação básica
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            throw new APIException("Nome, email e senha são obrigatórios.", 400);
        }

        // Verifica se e-mail já existe
        if ($this->repository->findByEmail($dados['email'])) {
            throw new APIException("Este e-mail já está cadastrado.", 409);
        }

        //criptografia da senha
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);

        $user = new User(
            $dados['nome'],
            $dados['email'],
            $dados['senha'],
            $dados['ativo'] ?? 1,
            null,    
            $senhaHash,        
            $dados['cargo'] ?? 'user'
        );

        $novoUsuario = $this->repository->create($user);

        //remove as senhas do client-side por segurança
        $novoUsuario->senha = null;
        $novoUsuario->senha_hash = null;

        return $novoUsuario;
    }

    public function buscarPorId($id)
    {
        $user = $this->repository->findById($id);
        if (!$user) {
            throw new APIException("Usuário não encontrado.", 404);
        }
        return $user;
    }
    public function listarTodos()
    {
        return $this->repository->findAll();
    }
    
}