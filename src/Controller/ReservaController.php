<?php

namespace App\Controller;

use App\Repository\ReservaRepository;

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
}
