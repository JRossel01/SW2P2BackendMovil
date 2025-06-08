<?php

namespace App\GraphQL\Inputs;

use Rebing\GraphQL\Support\InputType;
use GraphQL\Type\Definition\Type;

class SaveAppointmentInput extends InputType
{
    protected $attributes = [
        'name' => 'SaveAppointmentInput',
        'description' => 'Input para registrar una cita médica',
    ];

    public function fields(): array
    {
        return [
            'doctorId' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID del doctor'
            ],
            'patientId' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID del paciente'
            ],
            'date' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Fecha de la cita (AAAA-MM-DD)'
            ],
            'time' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Hora de la cita (HH:MM)'
            ],
            'reason' => [
                'type' => Type::string(),
                'description' => 'Motivo de la cita'
            ],
        ];
    }
}
