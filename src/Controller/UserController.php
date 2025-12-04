<?php

namespace Controller;

use Service\UserService;
use Http\Request;
use Http\Response;
use Error\APIException;
use Utils\jwt;

class UserController
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function processRequest(Request $request)
    {
        $method = $request->getMethod();
        $id = $request->getId(); // Pode ser o ID
        $body = $request->getBody();

        switch ($method) {
            case 'POST':
                // Rota de cadastro (Sign-up)
                $novoUser = $this->service->cadastrar($body);
                Response::send($novoUser, 201);
                break;

            case 'GET':

                $usuarioLogado = $this->autenticarUsuario();
                if ($id) {

                    if ($usuarioLogado['role'] !== 'admin' && $id != $usuarioLogado['id']) {
                        throw new APIException("Acesso negado. Você só pode visualizar seu próprio perfil.", 403);
                    }
                    $user = $this->service->buscarPorId($id);
                    Response::send($user);
                } else {
                    if ($usuarioLogado['role'] !== 'admin') {
                        throw new APIException("Acesso negado. Apenas administradores podem listar usuários.", 403);
                    }

                    $format = $request->getQuery()['format'] ?? null;
                    if ($format === 'csv') {
                        $lista = $this->service->listarTodos();
                        $this->exportToCsv($lista);
                    } else {
                        $lista = $this->service->listarTodos();
                        Response::send($lista);
                    }
                }
                break;
            
            default:
                throw new APIException("Método não suportado para Usuários.", 405);
        }
    }

    private function exportToCsv(array $data): void
    {
        if (empty($data)) {
            Response::send([], 204); // No content
            return;
        }

        $basePath = dirname(__DIR__, 2);
        $exportDir = $basePath . '/exports';

        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $filename = $exportDir . '/users_' . date('Y-m-d_H-i-s') . '.csv';
        $output = fopen($filename, 'w');

        // Cabeçalho do CSV
        fputcsv($output, array_keys($data[0]));

        // Dados
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);

        // Padroniza as barras para a resposta da API
        $responsePath = str_replace('\\', '/', $filename);
        Response::send(['message' => 'Arquivo CSV de usuários gerado com sucesso em: ' . $responsePath]);
    }

    private function autenticarUsuario(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            throw new APIException("Token de autenticação não fornecido.", 401);
        }

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new APIException("Formato do token inválido.", 401);
        }

        try {
            $payload = Jwt::validate($matches[1]);
            return [
                'id' => $payload['sub'],
                'role' => $payload['role'] ?? 'user'
            ];
        } catch (\Exception $e) {
            throw new APIException("Token inválido ou expirado: " . $e->getMessage(), 401);
        }
    }
}