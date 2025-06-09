<?php

namespace App\GraphQL\Types;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class AssistantResponseType extends GraphQLType
{
    protected $attributes = [
        'name' => 'AssistantResponse',
        'description' => 'Respuesta del asistente IA con posible cita registrada',
    ];

    public function fields(): array
    {
        return [
            'message' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Respuesta textual del asistente',
            ],
            'appointmentId' => [
                'type' => Type::int(),
                'description' => 'ID de la cita registrada (si aplica)',
            ],
        ];
    }
}
