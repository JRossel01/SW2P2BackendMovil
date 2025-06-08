<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class AppointmentType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Appointment',
        'description' => 'Una cita médica'
    ];

    public function fields(): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::int())],
            'date' => ['type' => Type::nonNull(Type::string())],
            'time' => ['type' => Type::nonNull(Type::string())],
            'status' => ['type' => Type::nonNull(Type::string())],
            'reason' => ['type' => Type::nonNull(Type::string())],
            'patientId' => ['type' => Type::nonNull(Type::int())],
            'doctorId' => ['type' => Type::nonNull(Type::int())],
        ];
    }
}