<?php

namespace App\Controller;

use App\Repository\ReservaRepository;
use App\Model\Reserva;

class ReservaController
{
    private ReservaRepository $repository;

    public function __construct()
    {
        $this->repository = new ReservaRepository();
    }

    public function index(): void
    {
        $filtros = [];
        $clavesPermitidas = ['nombre_huesped', 'nombre_alojamiento', 'estado', 'fecha_desde', 'fecha_hasta'];

        foreach ($clavesPermitidas as $clave) {
            if (!empty($_GET[$clave])) {
                $filtros[$clave] = $_GET[$clave];
            }
        }

        $reservas = $this->repository->findAll($filtros);

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($reservas);
    }

    public function show(int $id): void
    {
        $reserva = $this->repository->findById($id);

        if ($reserva === null) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Reserva no encontrada']);
            return;
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($reserva);
    }

    public function store(): void
    {

        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $errores = Reserva::validate($data);
        if (!empty($errores)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['errors' => $errores]);
            return;
        }

        $reserva = new Reserva($data);
        $id = $this->repository->create($reserva);

        http_response_code(201);
        header('Content-Type: application/json');
        echo json_encode(['id' => $id, 'message' => 'Reserva creada']);
    }
}
