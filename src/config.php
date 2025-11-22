<?php

date_default_timezone_set('America/Sao_Paulo');

// Headers para API (CORS e JSON)
if (isset($_SERVER['REQUEST_METHOD'])) {
    
    // Headers para API (CORS e JSON)
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    // Tratamento para requisições OPTIONS (Pre-flight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}
// AUTOLOAD
spl_autoload_register(function ($class) {
    

    $classPath = str_replace('\\', '/', $class);

    $file = __DIR__ . '/' . $classPath . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Tratamento de exceções globais
set_exception_handler(function ($e) {

    if ($e instanceof Error\ApiException) {
        $code = $e->getCode();
        $message = $e->getMessage();
    } else {
        $code = 500;
        $message = $e->getMessage();
    }

    // Força o código HTTP
    if (isset($_SERVER['REQUEST_METHOD'])) {
        http_response_code($code);
    }
    
    // Retorna o JSON
    echo json_encode([
        "error" => $message
    ], JSON_UNESCAPED_UNICODE);
    
    exit;
});