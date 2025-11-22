<?php

namespace Service;

use Repository\UserRepository;
use Error\APIException;
use Utils\Jwt;

class LoginService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function autenticar(array $dados): array
    {
        //  Validação Básica
        if (empty($dados['email']) || empty($dados['senha'])) {
            throw new APIException("Email e senha são obrigatórios.", 400);
        }

        $email = $dados['email'];
        $senha = $dados['senha'];

        //  Busca o usuário no banco
        $user = $this->repository->findByEmail($email);

        // Verifica se usuário existe E se a senha bate
        if (!$user || !password_verify($senha, $user->senha_hash)) {
            throw new APIException("Email ou senha inválidos.", 401);
        }

        if ($user->ativo == 0) {
            throw new APIException("Usuário inativo.", 403);
        }


        // Gera o Token JWT
        $payload = [
            'sub' => $user->id,
            'nome' => $user->nome,
            'email' => $user->email,
            'role' => $user->cargo 
        ];
        
        $token = Jwt::generate($payload);

        return [
            "mensagem" => "Autenticado com sucesso",
            "token" => $token,
            "usuario" => [
                "id" => $user->id,
                "nome" => $user->nome,
                "email" => $user->email
            ]
        ];
    }
}