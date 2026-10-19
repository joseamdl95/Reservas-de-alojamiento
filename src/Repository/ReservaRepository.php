<?php

namespace App\Repository;

use App\Database;
use App\Model\Reserva;
use PDO;
use Throwable;

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

    public function create(Reserva $reserva): int
    {
        $this->pdo->beginTransaction();

        try {
            $sql = "INSERT INTO reserva (nombre_huesped, email_huesped, nombre_alojamiento,fecha_entrada, fecha_salida, estado, importe, notas) VALUES (?,?,?,?,?,?,?,?)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $reserva->nombre_huesped,
                $reserva->email_huesped,
                $reserva->nombre_alojamiento,
                $reserva->fecha_entrada,
                $reserva->fecha_salida,
                $reserva->estado,
                $reserva->importe,
                $reserva->notas,
            ]);

            $id = (int) $this->pdo->lastInsertId();

            $this->addEvento($id, 'creada', 'Reserva creada');

            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateEstado(int $id, string $estado): string
    {
        $actual = $this->findById($id);

        if ($actual === null) {
            return 'not_found';
        }

        if ($actual['estado'] === $estado) {
            return 'unchanged';
        }

        $this->pdo->beginTransaction();

        try {
            $sql = "UPDATE reserva SET estado = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$estado, $id]);

            $this->addEvento($id, $estado, "Estado cambiado a {$estado}");

            $this->pdo->commit();
            return 'updated';
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function addEvento(int $reservaId, string $tipo, string $descripcion): void
    {
        $sql = "INSERT INTO reserva_evento (reserva_id, tipo, descripcion) VALUES (?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$reservaId, $tipo, $descripcion]);
    }

    public function findEventosByReserva(int $id): array
    {
        $sql = "SELECT * FROM reserva_evento WHERE reserva_id = ? ORDER BY fecha_creacion ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
