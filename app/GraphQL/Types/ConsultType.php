<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ConsultType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Consult',
        'description' => 'Una consulta médica del paciente'
    ];

    public function fields(): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::int())],
            'date' => ['type' => Type::string()],
            'diagnosis' => ['type' => Type::string()],
            'treatment' => ['type' => Type::string()],
            'observations' => ['type' => Type::string()],
            'currentWeight' => ['type' => Type::float()],
            'currentHeight' => ['type' => Type::float()],
            'medicalRecordId' => ['type' => Type::int()],
            'appointmentId' => ['type' => Type::int()],
            'attentionTime' => ['type' => Type::string()],
        ];
    }
}
