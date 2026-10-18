<?php

namespace App\Model;

use DateTime;

class Reserva
{
    public ?int $id = null;
    public string $nombre_huesped = '';
    public string $email_huesped = '';
    public string $nombre_alojamiento = '';
    public string $fecha_entrada = '';
    public string $fecha_salida = '';
    public string $estado = 'pendiente';
    public float $importe = 0.0;
    public ?string $notas = null;
    public ?string $fecha_creacion = null;

    public function __construct(array $data = [])
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }

        if (isset($data['nombre_huesped'])) {
            $this->nombre_huesped = (string) $data['nombre_huesped'];
        }

        if (isset($data['email_huesped'])) {
            $this->email_huesped = (string) $data['email_huesped'];
        }

        if (isset($data['nombre_alojamiento'])) {
            $this->nombre_alojamiento = (string) $data['nombre_alojamiento'];
        }

        if (isset($data['fecha_entrada'])) {
            $this->fecha_entrada = (string) $data['fecha_entrada'];
        }

        if (isset($data['fecha_salida'])) {
            $this->fecha_salida = (string) $data['fecha_salida'];
        }

        if (isset($data['estado']) && $data['estado'] !== '') {
            $this->estado = (string) $data['estado'];
        }

        if (isset($data['importe'])) {
            $this->importe = (float) $data['importe'];
        }

        if (isset($data['notas']) && $data['notas'] !== '') {
            $this->notas = (string) $data['notas'];
        }

        if (isset($data['fecha_creacion'])) {
            $this->fecha_creacion = (string) $data['fecha_creacion'];
        }
    }

    public static function validate(array $data): array
    {
        $errors = [];

        //validación del nombre del huesped

        $nombre = trim($data['nombre_huesped'] ?? '');

        if ($nombre === '') {
            $errors['nombre_huesped'] = 'El nombre del huésped es obligatorio';
        } elseif (mb_strlen($nombre) > 100) {
            $errors['nombre_huesped'] = 'El nombre del huésped no puede superar los 100 caracteres';
        }

        //validación del email del huesped

        $email = trim($data['email_huesped'] ?? '');

        if ($email === '') {
            $errors['email_huesped'] = 'El email es obligatorio';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email_huesped'] = 'El email no tiene un formato válido';
        } elseif (mb_strlen($email) > 254) {
            $errors['email_huesped'] = 'El email no puede superar los 254 caracteres';
        }

        //validación del nombre del alojamiento

        $alojamiento = trim($data['nombre_alojamiento'] ?? '');

        if ($alojamiento === '') {
            $errors['nombre_alojamiento'] = 'El nombre del alojamiento es obligatorio';
        } elseif (mb_strlen($alojamiento) > 150) {
            $errors['nombre_alojamiento'] = 'El nombre del alojamiento no puede superar los 150 caracteres';
        }

        //validacion del importe

        $importe = $data['importe'] ?? null;

        if ($importe === null || $importe === '') {
            $errors['importe'] = 'El importe es obligatorio';
        } elseif (!is_numeric($importe)) {
            $errors['importe'] = 'El importe con formato inválido';
        } elseif ((float) $importe < 0) {
            $errors['importe'] = 'El importe no puede ser negativo';
        }

        //validación del estado

        if (isset($data['estado']) && $data['estado'] !== '') {
            $estadosValidos = ['pendiente', 'confirmada', 'cancelada'];
            if (!in_array($data['estado'], $estadosValidos, true)) {
                $errors['estado'] = 'El estado no es válido';
            }
        }

        //validación de fechas

        //obligatorias 
        $fechaEntrada = trim($data['fecha_entrada'] ?? '');
        $fechaSalida  = trim($data['fecha_salida'] ?? '');

        if ($fechaEntrada === '') {
            $errors['fecha_entrada'] = 'La fecha de entrada es obligatoria';
        }
        if ($fechaSalida === '') {
            $errors['fecha_salida'] = 'La fecha de salida es obligatoria';
        }

        //formato correcto 
        $entradaValida = false;
        if ($fechaEntrada !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $fechaEntrada);
            if (!$d || $d->format('Y-m-d') !== $fechaEntrada) {
                $errors['fecha_entrada'] = 'La fecha de entrada tiene un formato inválido. Usa YYYY-MM-DD';
            } else {
                $entradaValida = true;
            }
        }

        $salidaValida = false;
        if ($fechaSalida !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $fechaSalida);
            if (!$d || $d->format('Y-m-d') !== $fechaSalida) {
                $errors['fecha_salida'] = 'La fecha de salida tiene un formato inválido. Usa YYYY-MM-DD';
            } else {
                $salidaValida = true;
            }
        }

        //salida > entrada
        if ($entradaValida && $salidaValida) {
            $entrada = new DateTime($fechaEntrada);
            $salida  = new DateTime($fechaSalida);
            if ($salida <= $entrada) {
                $errors['fecha_salida'] = 'La fecha de salida debe ser posterior a la fecha de entrada';
            }
        }

        //validacion de notas

        if (isset($data['notas']) && $data['notas'] !== '') {
            if (!is_string($data['notas'])) {
                $errors['notas'] = 'Las notas deben ser texto';
            } elseif (mb_strlen($data['notas']) > 2000) {
                $errors['notas'] = 'Las notas no pueden superar los 2000 caracteres';
            }
        }


        return $errors;
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'nombre_huesped'     => $this->nombre_huesped,
            'email_huesped'      => $this->email_huesped,
            'nombre_alojamiento' => $this->nombre_alojamiento,
            'fecha_entrada'      => $this->fecha_entrada,
            'fecha_salida'       => $this->fecha_salida,
            'estado'             => $this->estado,
            'importe'            => $this->importe,
            'notas'              => $this->notas,
            'fecha_creacion'     => $this->fecha_creacion,
        ];
    }
}
