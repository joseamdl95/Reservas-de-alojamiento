<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

//Cargar clases (a mano, mientras no usemos Composer)
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Model/Reserva.php';
require __DIR__ . '/../src/Repository/ReservaRepository.php';
require __DIR__ . '/../src/Controller/ReservaController.php';

use App\Controller\ReservaController;

//Capturar cualquier excepción no controlada → 500
try {

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $metodo = $_SERVER['REQUEST_METHOD'];

    $controller = new ReservaController();

    //listar reservas
    if ($metodo === 'GET' && $uri === '/api/reservas') {
        $controller->index();
        return;
    }

    //buscar reserva por id
    if ($metodo === 'GET' && preg_match('#^/api/reservas/(\d+)$#', $uri, $matches)) {
        $id = (int) $matches[1];
        $controller->show($id);
        return;
    }

    //Crear reserva
    if ($metodo === 'POST' && $uri === '/api/reservas') {
        $controller->store();
        return;
    }

    //Ruta no encontrada
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ruta no encontrada']);
} catch (Throwable $e) {
    error_log($e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error interno del servidor']);
}
