<?php

namespace Model;

class User
{
    public ?int $id;
    public string $nome;
    public string $email;
    public ?string $senha;
    public ?string $senha_hash;
    public int $ativo;
    public string $cargo;

    public function __construct(
        string $nome,
        string $email,
        ?string $senha = null,
        int $ativo = 1,
        ?int $id = null,
        ?string $senha_hash = null,
        string $cargo = 'user'
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->ativo = $ativo;
        $this->senha_hash = $senha_hash;
        $this->cargo = $cargo;
    }
}