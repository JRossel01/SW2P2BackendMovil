<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class PreEvaluationType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PreEvaluation',
        'description' => 'Pre-evaluación de cita médica',
    ];

    public function fields(): array
    {
        return [
            'id' => ['type' => Type::int()],
            'appointmentId' => ['type' => Type::int()],
            'symptoms' => ['type' => Type::string()],
            'potentialDiagnosis' => ['type' => Type::string()],
        ];
    }
}
