<?php

namespace Model;

class Task
{
    // Atributos que espelham a tabela 'relatorios'
    public ?int $id;
    public int $usuario_id;
    public string $titulo;
    public string $cliente;
    public ?string $descricao;
    public string $data_realizacao;
    public float $valor;

    // Construtor para a criação do objeto
    public function __construct(
        int $usuario_id,
        string $titulo,
        string $cliente,
        string $data_realizacao,
        float $valor = 0,
        ?string $descricao = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->titulo = $titulo;
        $this->cliente = $cliente;
        $this->data_realizacao = $data_realizacao;
        $this->valor = $valor;
        $this->descricao = $descricao;
    }

    // Método para transformar o objeto em Array
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'titulo' => $this->titulo,
            'cliente' => $this->cliente,
            'descricao' => $this->descricao,
            'data_realizacao' => $this->data_realizacao,
            'valor' => $this->valor
        ];
    }
}