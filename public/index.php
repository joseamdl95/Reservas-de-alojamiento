<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use App\Controller\ReservaController;
use App\Repository\ReservaRepository;
use App\TwigFactory;

//Capturar cualquier excepción no controlada → 500
try {

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $metodo = $_SERVER['REQUEST_METHOD'];

    $controller = new ReservaController();

    // ─────────────────────────────────────────────
    // VISTAS (HTML con Twig)
    // ─────────────────────────────────────────────

    // Vista: listado de reservas
    if ($metodo === 'GET' && $uri === '/') {
        $repo = new ReservaRepository();
        $reservas = $repo->findAll();

        $twig = TwigFactory::getTwig();
        echo $twig->render('lista_reservas.html.twig', ['reservas' => $reservas]);
        return;
    }

    // Vista: detalle de reserva
    if ($metodo === 'GET' && preg_match('#^/reserva/(\d+)$#', $uri, $matches)) {
        $id = (int) $matches[1];
        $repo = new ReservaRepository();
        $reserva = $repo->findById($id);

        if ($reserva === null) {
            http_response_code(404);
            echo "Reserva no encontrada";
            return;
        }

        $eventos = $repo->findEventosByReserva($id);

        $twig = TwigFactory::getTwig();
        echo $twig->render('detalle_reserva.html.twig', [
            'reserva' => $reserva,
            'eventos' => $eventos,
        ]);
        return;
    }

    // ─────────────────────────────────────────────
    // API (JSON)
    // ─────────────────────────────────────────────

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

    //Cambiar estado de reserva
    if ($metodo === 'PATCH' && preg_match('#^/api/reservas/(\d+)/estado$#', $uri, $matches)) {
        $id = (int) $matches[1];
        $controller->updateEstado($id);
        return;
    }

    //Eventos de una reserva
    if ($metodo === 'GET' && preg_match('#^/api/reservas/(\d+)/eventos$#', $uri, $matches)) {
        $id = (int) $matches[1];
        $controller->eventos($id);
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
