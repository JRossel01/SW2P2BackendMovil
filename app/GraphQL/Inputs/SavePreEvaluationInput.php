<?php

namespace App\GraphQL\Inputs;

use Rebing\GraphQL\Support\InputType;
use GraphQL\Type\Definition\Type;

class SavePreEvaluationInput extends InputType
{
    protected $attributes = [
        'name' => 'SavePreEvaluationInput',
    ];

    public function fields(): array
    {
        return [
            'appointmentId' => ['type' => Type::nonNull(Type::int())],
            'symptoms' => ['type' => Type::nonNull(Type::string())],
            'potentialDiagnosis' => ['type' => Type::nonNull(Type::string())],
        ];
    }
}
