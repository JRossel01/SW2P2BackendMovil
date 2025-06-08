<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class LoginResponseType extends GraphQLType
{
    protected $attributes = [
        'name' => 'LoginResponse',
        'description' => 'Respuesta del login del backend Spring Boot'
    ];

    public function fields(): array
    {
        return [
            'jwt' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'role' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'doctorId' => [
                'type' => Type::int(),
            ],
            'patientId' => [
                'type' => Type::int(),
            ],
        ];
    }
}