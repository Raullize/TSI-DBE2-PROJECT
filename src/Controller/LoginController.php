<?php

namespace Controller;

use Service\LoginService;
use Http\Request;
use Http\Response;
use Error\APIException;

class LoginController
{
    private LoginService $service;

    public function __construct()
    {
        $this->service = new LoginService();
    }

    public function processRequest(Request $request)
    {
        if ($request->getMethod() !== 'POST') {
            throw new APIException("Método não permitido. Use POST para login.", 405);
        }

        $body = $request->getBody();
        
        $resultado = $this->service->autenticar($body);

        Response::send($resultado, 200);
    }
}