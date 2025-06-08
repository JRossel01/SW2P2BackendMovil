<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class LogoutResponseType extends GraphQLType
{
    protected $attributes = [
        'name' => 'LogoutResponse',
        'description' => 'Respuesta de logout del backend Spring Boot'
    ];

    public function fields(): array
    {
        return [
            'message' => [
                'type' => Type::string(),
                'description' => 'Mensaje de confirmación de logout'
            ],
        ];
    }
}