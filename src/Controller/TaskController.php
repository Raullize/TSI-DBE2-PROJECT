<?php

namespace Controller;

use Service\TaskService;
use Http\Request;
use Http\Response;
use Error\APIException;

class TaskController
{
    private TaskService $service;

    public function __construct()
    {
        $this->service = new TaskService();
    }

    // O método principal que o index.php chama
    public function processRequest(Request $request)
    {
        $method = $request->getMethod();
        $id = $request->getId();
        $body = $request->getBody();
        $query = $request->getQuery();

        // Router interno do Controller
        switch ($method) {
            case 'GET':
                if ($id) {
                    $dado = $this->service->buscarPorId($id);
                    Response::send($dado);
                } else {
                    // Passa os filtros (?cliente=X&data=Y) para o Service
                    $lista = $this->service->listar($query);
                    Response::send($lista);
                }
                break;

            case 'POST':
                $novo = $this->service->criar($body);
                Response::send($novo, 201);
                break;

            case 'PUT':
                if (!$id) throw new APIException("ID obrigatório para atualização", 400);
                $atualizado = $this->service->atualizar($id, $body);
                Response::send($atualizado);
                break;

            case 'DELETE':
                if (!$id) throw new APIException("ID obrigatório para exclusão", 400);
                $this->service->deletar($id);
                Response::send(null, 204);
                break;

            default:
                throw new APIException("Método não permitido", 405);
        }
    }
}