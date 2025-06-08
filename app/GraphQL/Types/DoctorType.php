<?php

namespace App\GraphQL\Types;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class DoctorType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Doctor',
        'description' => 'Información del doctor',
    ];

    public function fields(): array
    {
        return [
            'idDoctor' => ['type' => Type::nonNull(Type::int())],
            'name' => ['type' => Type::string()],
            'username' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
            'specialty' => ['type' => Type::string()],
            'licenseNumber' => ['type' => Type::string()],
            'phone' => ['type' => Type::string()],
            'idUser' => ['type' => Type::int()],
        ];
    }
}