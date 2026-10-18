<?php

namespace App\Repository;

use App\Database;
use App\Model\Reserva;
use PDO;

class ReservaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findAll(array $filtros = []): array
    {

        $sql = "SELECT * FROM reserva";
        $where = [];
        $params = [];

        //filtrar por nombre de huesped
        if (!empty($filtros['nombre_huesped'])) {
            $where[]  = "nombre_huesped LIKE ?";
            $params[] = '%' . $filtros['nombre_huesped'] . '%';
        }

        //filtrar por nombre de alojamiento
        if (!empty($filtros['nombre_alojamiento'])) {
            $where[]  = "nombre_alojamiento LIKE ?";
            $params[] = '%' . $filtros['nombre_alojamiento'] . '%';
        }

        //filtrar por estado
        if (!empty($filtros['estado'])) {
            $where[]  = "estado = ?";
            $params[] = $filtros['estado'];
        }

        //filtrar por fecha de entrada desde
        if (!empty($filtros['fecha_desde'])) {
            $where[]  = "fecha_entrada >= ?";
            $params[] = $filtros['fecha_desde'];
        }

        //filtrar por fecha de salida hasta
        if (!empty($filtros['fecha_hasta'])) {
            $where[]  = "fecha_salida <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        //WHERER si hay condiciones
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Orden
        $sql .= " ORDER BY fecha_entrada DESC";

        // Ejecutar
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM reserva WHERE id = ? LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $resultado =  $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado === false ? null : $resultado;
    }
}
